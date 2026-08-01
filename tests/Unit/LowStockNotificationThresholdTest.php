<?php

namespace Tests\Unit;

use App\Listeners\Stock\UpdateStockCache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LowStockNotificationThresholdTest extends TestCase
{
    #[DataProvider('thresholdCases')]
    public function test_notification_is_created_only_when_stock_crosses_the_minimum(
        float $oldQuantity,
        float $newQuantity,
        float $minimumStock,
        bool $expected
    ): void {
        $this->assertSame(
            $expected,
            UpdateStockCache::crossedMinimum($oldQuantity, $newQuantity, $minimumStock)
        );
    }

    public static function thresholdCases(): array
    {
        return [
            'crosses below minimum' => [12, 9, 10, true],
            'lands exactly on minimum' => [12, 10, 10, true],
            'remains below minimum' => [9, 7, 10, false],
            'restocks while below minimum' => [7, 9, 10, false],
            'remains above minimum' => [15, 12, 10, false],
            'zero minimum disables alert' => [12, 0, 0, false],
        ];
    }
}
