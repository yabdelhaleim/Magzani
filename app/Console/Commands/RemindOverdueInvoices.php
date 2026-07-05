<?php

namespace App\Console\Commands;

use App\Models\SalesInvoice;
use App\Models\User;
use App\Notifications\Accounting\OverdueInvoiceNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class RemindOverdueInvoices extends Command
{
    protected $signature = 'accounting:remind-overdue {--days=0 : Minimum days overdue}';

    protected $description = 'Send reminders for overdue unpaid sales invoices';

    public function handle(): int
    {
        $minDays = (int) $this->option('days');
        $today   = now()->toDateString();

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

        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('No admin users found to notify.');
        }

        $count = 0;
        foreach ($invoices as $invoice) {
            $daysOverdue = now()->diffInDays($invoice->due_date);

            Log::info("[OverdueReminder] Invoice #{$invoice->invoice_number} overdue by {$daysOverdue} days");

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new OverdueInvoiceNotification($invoice, $daysOverdue));
            }

            $count++;
        }

        $this->info("Sent reminders for {$count} overdue invoice(s).");
        return self::SUCCESS;
    }
}
