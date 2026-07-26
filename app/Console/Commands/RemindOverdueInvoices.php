<?php

namespace App\Console\Commands;

use App\Models\SalesInvoice;
use App\Notifications\Accounting\OverdueInvoiceNotification;
use App\Services\NotificationDeliveryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RemindOverdueInvoices extends Command
{
    protected $signature = 'accounting:remind-overdue {--days=0 : Minimum days overdue}';

    protected $description = 'Send reminders for overdue unpaid sales invoices';

    public function handle(): int
    {
        $minDays = (int) $this->option('days');
        $today = now()->toDateString();

        $invoices = SalesInvoice::with('customer')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->where('status', 'confirmed')
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->whereRaw('DATEDIFF(?, due_date) >= ?', [$today, $minDays])
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('No overdue invoices found.');

            return self::SUCCESS;
        }

        $notifications = app(NotificationDeliveryService::class);
        $count = 0;
        $deliveries = 0;

        foreach ($invoices as $invoice) {
            $daysOverdue = now()->diffInDays($invoice->due_date);

            Log::info("[OverdueReminder] Invoice #{$invoice->invoice_number} overdue by {$daysOverdue} days");

            $deliveries += $notifications->sendToAdmins(
                new OverdueInvoiceNotification($invoice, $daysOverdue)
            );

            $count++;
        }

        if ($deliveries === 0) {
            $this->warn('No active admin users found to notify.');
        }

        $this->info("Sent reminders for {$count} overdue invoice(s).");

        return self::SUCCESS;
    }
}
