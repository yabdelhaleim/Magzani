<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackService
{
    protected ?string $webhookUrl;
    protected bool $enabled;

    public function __construct()
    {
        $this->webhookUrl = config('services.slack.webhook_url');
        $this->enabled = !empty($this->webhookUrl);
    }

    /**
     * تحقق من تفعيل Slack
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * إرسال رسالة بسيطة
     */
    public function send(string $message, string $emoji = '📢'): bool
    {
        if (!$this->enabled) {
            Log::debug('Slack notifications disabled');
            return false;
        }

        try {
            $response = Http::timeout(5)->post($this->webhookUrl, [
                'text' => "{$emoji} {$message}"
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Slack notification failed', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
            return false;
        }
    }

    /**
     * إرسال رسالة متقدمة مع Blocks
     */
    public function sendRich(string $title, array $fields, array $options = []): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $blocks = [
            [
                'type' => 'header',
                'text' => [
                    'type' => 'plain_text',
                    'text' => $title,
                    'emoji' => true
                ]
            ],
            [
                'type' => 'section',
                'fields' => $this->formatFields($fields)
            ]
        ];

        // إضافة الألوان والأزرار
        if (isset($options['color'])) {
            $blocks[0]['color'] = $options['color'];
        }

        if (isset($options['button'])) {
            $blocks[] = [
                'type' => 'actions',
                'elements' => [
                    [
                        'type' => 'button',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => $options['button']['text'],
                            'emoji' => true
                        ],
                        'url' => $options['button']['url'],
                        'style' => $options['button']['style'] ?? 'primary'
                    ]
                ]
            ];
        }

        try {
            $response = Http::timeout(5)->post($this->webhookUrl, [
                'text' => $title,
                'blocks' => $blocks
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Slack rich notification failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * إشعار مخزون منخفض
     */
    public function notifyLowStock($product, $warehouse, int $currentQty, int $minStock, string $severity): bool
    {
        $shortage = $minStock - $currentQty;
        $emoji = $this->getSeverityEmoji($severity);

        return $this->sendRich(
            "{$emoji} تنبيه مخزون حرج",
            [
                'المنتج' => "{$product->name} ({$product->code})",
                'المخزن' => $warehouse->name,
                'الكمية الحالية' => $currentQty,
                'الحد الأدنى' => $minStock,
                'النقص' => "{$shortage} وحدة",
                'الحالة' => "{$emoji} " . $this->getSeverityText($severity),
            ],
            [
                'color' => $this->getSeverityColor($severity),
                'button' => [
                    'text' => '🔍 عرض المنتج',
                    'url' => route('products.show', $product->id),
                    'style' => $severity === 'critical' ? 'danger' : 'primary'
                ]
            ]
        );
    }

    /**
     * إشعار فاتورة جديدة
     */
    public function notifyNewInvoice(string $type, $invoice): bool
    {
        $emoji = $type === 'sales' ? '💰' : '🛒';
        $typeText = $type === 'sales' ? 'مبيعات' : 'مشتريات';

        return $this->sendRich(
            "{$emoji} فاتورة {$typeText} جديدة",
            [
                'رقم الفاتورة' => $invoice->invoice_number,
                'العميل/المورد' => $invoice->customer->name ?? $invoice->supplier->name,
                'الإجمالي' => number_format($invoice->total, 2) . ' جنيه',
                'المدفوع' => number_format($invoice->paid, 2) . ' جنيه',
                'المتبقي' => number_format($invoice->remaining, 2) . ' جنيه',
                'التاريخ' => $invoice->invoice_date->format('Y-m-d'),
            ],
            [
                'button' => [
                    'text' => '📄 عرض الفاتورة',
                    'url' => route($type . '.show', $invoice->id),
                ]
            ]
        );
    }

    /**
     * إشعار دفعة جديدة
     */
    public function notifyPayment($payment): bool
    {
        $type = $payment->payable_type === 'App\Models\Customer' ? 'من عميل' : 'لمورد';

        return $this->sendRich(
            "💵 دفعة جديدة {$type}",
            [
                'المبلغ' => number_format($payment->amount, 2) . ' جنيه',
                'طريقة الدفع' => $this->getPaymentMethodText($payment->payment_method),
                'التاريخ' => $payment->payment_date->format('Y-m-d'),
            ]
        );
    }

    // Helper Methods

    private function formatFields(array $fields): array
    {
        $formatted = [];
        
        foreach ($fields as $key => $value) {
            $formatted[] = [
                'type' => 'mrkdwn',
                'text' => "*{$key}:*\n{$value}"
            ];
        }

        return $formatted;
    }

    private function getSeverityEmoji(string $severity): string
    {
        return match($severity) {
            'critical' => '🔴',
            'high' => '🟠',
            'medium' => '🟡',
            default => '🔵',
        };
    }

    private function getSeverityText(string $severity): string
    {
        return match($severity) {
            'critical' => 'حرجة',
            'high' => 'عالية',
            'medium' => 'متوسطة',
            default => 'منخفضة',
        };
    }

    private function getSeverityColor(string $severity): string
    {
        return match($severity) {
            'critical' => '#FF0000',
            'high' => '#FF8C00',
            'medium' => '#FFD700',
            default => '#0000FF',
        };
    }

    private function getPaymentMethodText(string $method): string
    {
        return match($method) {
            'cash' => '💵 نقدي',
            'bank' => '🏦 تحويل بنكي',
            'check' => '📝 شيك',
            default => $method,
        };
    }
}

