<?php

namespace App\Console\Commands;

use App\Models\RawMaterialTemplate;
use App\Services\RawMaterialTemplateInventoryService;
use Illuminate\Console\Command;

class SyncRawMaterialTemplatesInventoryCommand extends Command
{
    protected $signature = 'inventory:sync-raw-material-templates';

    protected $description = 'مزامنة قوالب الخامات مع المنتجات وربط المخازن (للخامات المضافة قبل التحديث)';

    public function handle(RawMaterialTemplateInventoryService $service): int
    {
        $q = RawMaterialTemplate::query()->whereNotNull('warehouse_id');
        $count = $q->count();
        if ($count === 0) {
            $this->info('لا توجد خامات مرتبطة بمخزن.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($q->cursor() as $template) {
            try {
                $service->sync($template, null);
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("فشل للخامة #{$template->id} ({$template->name}): {$e->getMessage()}");

                return self::FAILURE;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("تمت مزامنة {$count} خامة.");

        return self::SUCCESS;
    }
}
