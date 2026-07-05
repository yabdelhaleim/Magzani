<?php

namespace App\Services\Accounting;

use App\Enums\JournalEntrySource;
use App\Models\JournalEntry;
use App\Models\RecurringJournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RecurringJournalEntryService
{
    public function __construct(
        private JournalEntryService $journalService
    ) {}

    public function create(array $data): RecurringJournalEntry
    {
        return DB::transaction(function () use ($data) {
            $template = RecurringJournalEntry::create([
                'template_name' => $data['template_name'],
                'description'   => $data['description'],
                'frequency'     => $data['frequency'],
                'next_run_date' => $data['next_run_date'],
                'end_date'      => $data['end_date'] ?? null,
                'is_active'     => $data['is_active'] ?? true,
                'auto_post'     => $data['auto_post'] ?? true,
                'created_by'    => Auth::id(),
            ]);

            $this->syncLines($template, $data['lines'] ?? []);

            return $template->load('lines.account');
        });
    }

    public function update(RecurringJournalEntry $template, array $data): RecurringJournalEntry
    {
        return DB::transaction(function () use ($template, $data) {
            $template->update([
                'template_name' => $data['template_name'],
                'description'   => $data['description'],
                'frequency'     => $data['frequency'],
                'next_run_date' => $data['next_run_date'],
                'end_date'      => $data['end_date'] ?? null,
                'is_active'     => $data['is_active'] ?? true,
                'auto_post'     => $data['auto_post'] ?? true,
            ]);

            $this->syncLines($template, $data['lines'] ?? []);

            return $template->fresh()->load('lines.account');
        });
    }

    /**
     * معالجة كل القوالب المستحقة حتى تاريخ معين
     */
    public function processDue(?Carbon $asOf = null): array
    {
        $asOf = ($asOf ?? now())->startOfDay();
        $processed = [];

        $templates = RecurringJournalEntry::with('lines.account')
            ->where('is_active', true)
            ->where('next_run_date', '<=', $asOf->toDateString())
            ->where(function ($q) use ($asOf) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $asOf->toDateString());
            })
            ->get();

        foreach ($templates as $template) {
            $entry = $this->processTemplate($template, $asOf);
            if ($entry) {
                $processed[] = ['template' => $template->template_name, 'entry_id' => $entry->id];
            }
        }

        return $processed;
    }

    public function processTemplate(RecurringJournalEntry $template, ?Carbon $runDate = null): ?JournalEntry
    {
        $runDate = ($runDate ?? Carbon::parse($template->next_run_date))->startOfDay();

        if (!$template->is_active) {
            return null;
        }

        if ($template->end_date && $runDate->gt($template->end_date)) {
            return null;
        }

        $lines = $template->lines->map(fn ($line) => [
            'account_id'  => $line->account_id,
            'debit'       => (float) $line->debit,
            'credit'      => (float) $line->credit,
            'description' => $line->description ?? $template->description,
        ])->toArray();

        if (empty($lines)) {
            throw new RuntimeException("القالب [{$template->template_name}] لا يحتوي على أسطر.");
        }

        $eventKey = "recurring:{$template->id}:{$runDate->toDateString()}";

        $entryData = [
            'entry_date'       => $runDate->toDateString(),
            'description'      => "{$template->description} — {$runDate->toDateString()}",
            'source_type'      => JournalEntrySource::MANUAL->value,
            'source_id'        => $template->id,
            'source_event_key' => $eventKey,
            'reference'        => "REC-{$template->id}",
            'lines'            => $lines,
        ];

        $entry = $template->auto_post
            ? $this->journalService->createAndPost($entryData)
            : $this->journalService->createDraft($entryData);

        $template->update([
            'last_run_date' => $runDate->toDateString(),
            'next_run_date' => $this->advanceDate($runDate, $template->frequency)->toDateString(),
        ]);

        return $entry;
    }

    public function advanceDate(Carbon $date, string $frequency): Carbon
    {
        return match ($frequency) {
            'daily'     => $date->copy()->addDay(),
            'weekly'    => $date->copy()->addWeek(),
            'monthly'   => $date->copy()->addMonth(),
            'quarterly' => $date->copy()->addMonths(3),
            'yearly'    => $date->copy()->addYear(),
            default     => $date->copy()->addMonth(),
        };
    }

    private function syncLines(RecurringJournalEntry $template, array $lines): void
    {
        $template->lines()->delete();

        foreach ($lines as $i => $line) {
            if (empty($line['account_id'])) {
                continue;
            }

            $template->lines()->create([
                'line_number' => $i + 1,
                'account_id'  => $line['account_id'],
                'debit'       => (float) ($line['debit'] ?? 0),
                'credit'      => (float) ($line['credit'] ?? 0),
                'description' => $line['description'] ?? null,
            ]);
        }
    }
}
