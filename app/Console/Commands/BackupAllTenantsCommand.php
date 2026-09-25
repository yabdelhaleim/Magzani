<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Backup all tenant databases independently.
 *
 * The Spatie backup:run command backs up only the DB connection configured
 * in config/database.php (the central DB). For Magzani, each tenant has its
 * own database, so we need to iterate through tenants and dump each one
 * onto the configured backup disk.
 *
 * Usage:
 *   php artisan tenants:backup [disk]
 *
 * Output structure (on disk):
 *   backups/tenants/<tenant_id>/<database>-<timestamp>.sql.gz
 */
class BackupAllTenantsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'tenants:backup {disk=local : Disk name (s3, local, etc.)} {--keep=0 : Number of backups to keep per tenant (0 = unlimited)}';

    /**
     * @var string
     */
    protected $description = 'Back up every active tenant database into the configured backup disk.';

    public function handle(): int
    {
        $disk = $this->argument('disk');
        $keep = (int) $this->option('keep');

        $this->info("Starting backup of all tenants to disk [{$disk}]" . ($keep > 0 ? " (keep last {$keep})" : ''));

        $tenants = Tenant::all();
        $success = 0;
        $failed = 0;

        foreach ($tenants as $tenant) {
            try {
                $this->backupTenant($tenant, $disk);
                $this->purgeOld($tenant, $disk, $keep);
                $success++;
                $this->line("  ✓ Tenant {$tenant->id}");
            } catch (\Throwable $e) {
                $failed++;
                Log::error("Tenant backup failed for tenant {$tenant->id}", [
                    'error' => $e->getMessage(),
                ]);
                $this->error("  ✗ Tenant {$tenant->id}: " . $e->getMessage());

                // Don't re-throw — we want to keep backing up the rest.
                continue;
            }
        }

        $this->info("Done. OK: {$success}, Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Run mysqldump for a single tenant database and store the resulting
     * gzipped SQL on the configured disk.
     */
    protected function backupTenant(Tenant $tenant, string $disk): void
    {
        // Stancl Tenancy stores the database name on the tenant under
        // `tenancy_db_name` inside the `data` JSON column.
        $dbName = data_get($tenant->data, 'tenancy_db_name');
        if (! $dbName) {
            throw new \RuntimeException("Tenant {$tenant->id} has no associated database name.");
        }

        $database = config('database.connections.tenant.database', $dbName);
        $host = config('database.connections.tenant.host', '127.0.0.1');
        $port = (string) config('database.connections.tenant.port', 3306);
        $username = config('database.connections.tenant.username', 'root');
        $password = config('database.connections.tenant.password', '');

        $timestamp = now()->format('Y-m-d-H-i-s');
        $filename = "{$dbName}-{$timestamp}.sql.gz";
        $path = "backups/tenants/{$tenant->id}/{$filename}";
        $localTmp = storage_path("app/backup-temp/{$filename}");

        @mkdir(dirname($localTmp), 0775, true);

        // Build mysqldump command. Use --single-transaction for InnoDB only.
        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers --events %s 2>&1 | gzip > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($localTmp)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || ! file_exists($localTmp) || filesize($localTmp) === 0) {
            throw new \RuntimeException('mysqldump failed (returnCode=' . $returnCode . '): ' . implode("\n", $output));
        }

        // Upload to the configured disk.
        $stream = fopen($localTmp, 'rb');
        Storage::disk($disk)->put($path, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        // Cleanup local temp file.
        @unlink($localTmp);

        Log::info("Tenant backup created", [
            'tenant_id' => $tenant->id,
            'database'  => $dbName,
            'disk'      => $disk,
            'path'      => $path,
            'size'      => Storage::disk($disk)->size($path),
        ]);
    }

    /**
     * If --keep is set, delete the oldest backups beyond that count.
     */
    protected function purgeOld(Tenant $tenant, string $disk, int $keep): void
    {
        if ($keep <= 0) {
            return;
        }

        $path = "backups/tenants/{$tenant->id}";
        $files = Storage::disk($disk)->files($path);

        if (count($files) <= $keep) {
            return;
        }

        // Sort files by name (timestamp encoded in filename).
        usort($files, fn ($a, $b) => strcmp($a, $b));
        $delete = array_slice($files, 0, count($files) - $keep);
        foreach ($delete as $file) {
            Storage::disk($disk)->delete($file);
        }
    }
}
