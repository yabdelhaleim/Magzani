<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class SalesInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $customers = DB::table('customers')->pluck('id');
        $products = DB::table('products')->pluck('id');
        $warehouses = DB::table('warehouses')->pluck('id');
        
        $invoiceStatuses = ['confirmed', 'pending', 'confirmed', 'confirmed'];
        $paymentStatuses = ['paid', 'partial', 'unpaid', 'paid'];
        
        $invoices = [
            ['invoice_number' => 'SI-2026-0001', 'days_ago' => 5, 'items' => [
                ['product_idx' => 0, 'qty' => 2, 'price' => 28000, 'tax' => 14],
                ['product_idx' => 6, 'qty' => 5, 'price' => 600, 'tax' => 14],
                ['product_idx' => 8, 'qty' => 3, 'price' => 4200, 'tax' => 14],
            ]],
            ['invoice_number' => 'SI-2026-0002', 'days_ago' => 3, 'items' => [
                ['product_idx' => 2, 'qty' => 1, 'price' => 23000, 'tax' => 14],
                ['product_idx' => 3, 'qty' => 2, 'price' => 14500, 'tax' => 14],
            ]],
            ['invoice_number' => 'SI-2026-0003', 'days_ago' => 10, 'items' => [
                ['product_idx' => 4, 'qty' => 1, 'price' => 25000, 'tax' => 14],
                ['product_idx' => 5, 'qty' => 2, 'price' => 17500, 'tax' => 14],
            ]],
            ['invoice_number' => 'SI-2026-0004', 'days_ago' => 1, 'items' => [
                ['product_idx' => 14, 'qty' => 50, 'price' => 25, 'tax' => 14],
                ['product_idx' => 15, 'qty' => 100, 'price' => 10, 'tax' => 14],
                ['product_idx' => 16, 'qty' => 30, 'price' => 35, 'tax' => 14],
            ]],
            ['invoice_number' => 'SI-2026-0005', 'days_ago' => 7, 'items' => [
                ['product_idx' => 10, 'qty' => 10, 'price' => 450, 'tax' => 14],
                ['product_idx' => 11, 'qty' => 5, 'price' => 600, 'tax' => 14],
            ]],
            ['invoice_number' => 'SI-2026-0006', 'days_ago' => 2, 'items' => [
                ['product_idx' => 22, 'qty' => 20, 'price' => 50, 'tax' => 14],
                ['product_idx' => 23, 'qty' => 100, 'price' => 5, 'tax' => 14],
                ['product_idx' => 24, 'qty' => 50, 'price' => 10, 'tax' => 14],
            ]],
            ['invoice_number' => 'SI-2026-0007', 'days_ago' => 15, 'items' => [
                ['product_idx' => 28, 'qty' => 20, 'price' => 80, 'tax' => 14],
                ['product_idx' => 29, 'qty' => 15, 'price' => 100, 'tax' => 14],
            ]],
            ['invoice_number' => 'SI-2026-0008', 'days_ago' => 0, 'items' => [
                ['product_idx' => 1, 'qty' => 3, 'price' => 21000, 'tax' => 14],
                ['product_idx' => 7, 'qty' => 10, 'price' => 250, 'tax' => 14],
            ]],
        ];

        foreach ($invoices as $index => $inv) {
            $customerId = $customers[$index % $customers->count()];
            $warehouseId = $warehouses[$index % $warehouses->count()];
            $invoiceDate = now()->subDays($inv['days_ago']);
            
            $subtotal = 0;
            $taxAmount = 0;
            
            $invoice = SalesInvoice::create([
                'invoice_number' => $inv['invoice_number'],
                'customer_id' => $customerId,
                'warehouse_id' => $warehouseId,
                'invoice_date' => $invoiceDate,
                'due_date' => $invoiceDate->addDays(30),
                'subtotal' => 0,
                'discount_type' => 'percent',
                'discount_value' => 0,
                'discount_amount' => 0,
                'tax_rate' => 14,
                'tax_amount' => 0,
                'shipping_cost' => 0,
                'other_charges' => 0,
                'total' => 0,
                'paid' => 0,
                'status' => $invoiceStatuses[$index % count($invoiceStatuses)],
                'payment_status' => $paymentStatuses[$index % count($paymentStatuses)],
                'notes' => 'فاتورة تجريبية #' . ($index + 1),
                'created_by' => 1,
            ]);

            foreach ($inv['items'] as $item) {
                $productId = $products[$item['product_idx'] % $products->count()];
                $qty = $item['qty'];
                $price = $item['price'];
                $taxRate = $item['tax'];
                
                $itemSubtotal = $qty * $price;
                $itemTax = $itemSubtotal * ($taxRate / 100);
                $itemTotal = $itemSubtotal + $itemTax;
                
                $subtotal += $itemSubtotal;
                $taxAmount += $itemTax;
                
                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'quantity_in_base_unit' => $qty,
                    'price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $itemTax,
                    'subtotal' => $itemSubtotal,
                    'total' => $itemTotal,
                ]);
            }

            $total = $subtotal + $taxAmount;
            $paid = 0;
            
            $paymentStatus = $invoice->payment_status;
            if ($paymentStatus === 'paid') {
                $paid = $total;
            } elseif ($paymentStatus === 'partial') {
                $paid = $total * 0.5;
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'paid' => $paid,
            ]);

            if ($paid > 0) {
                Payment::create([
                    'payable_type' => SalesInvoice::class,
                    'payable_id' => $invoice->id,
                    'amount' => $paid,
                    'payment_date' => $invoiceDate,
                    'payment_method' => 'cash',
                    'reference_number' => 'PAY-' . $inv['invoice_number'],
                    'notes' => 'دفع تجريبي',
                    'created_by' => 1,
                ]);
            }

            DB::table('customers')
                ->where('id', $customerId)
                ->increment('balance', $total - $paid);
        }
    }
}
