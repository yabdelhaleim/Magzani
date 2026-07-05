<?php

namespace App\Console\Commands;

use App\Services\Accounting\RecurringJournalEntryService;
use Illuminate\Console\Command;

class ProcessRecurringJournalEntries extends Command
{
    protected $signature = 'accounting:process-recurring {--date= : Process as of this date (Y-m-d)}';

    protected $description = 'Generate journal entries from due recurring templates';

    public function handle(RecurringJournalEntryService $service): int
    {
        $asOf = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))
            : now();

        $processed = $service->processDue($asOf);

        if (empty($processed)) {
            $this->info('No recurring journal entries due.');
            return self::SUCCESS;
        }

        foreach ($processed as $item) {
            $this->line("✓ {$item['template']} → entry #{$item['entry_id']}");
        }

        $this->info(count($processed) . ' recurring entry(ies) processed.');
        return self::SUCCESS;
    }
}
