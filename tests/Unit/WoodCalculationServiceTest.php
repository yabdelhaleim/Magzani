<?php

namespace Tests\Unit;

use App\Services\WoodCalculationService;
use PHPUnit\Framework\TestCase;

/**
 * 🛡️ Regression Safety Net — WoodCalculationService
 *
 * هذه الاختبارات هي شبكة الأمان قبل المرحلة ب (تعميم موديول التصنيع).
 * يجب أن تنجح كلها (PASS) على الكود الحالي بدون أي تعديل.
 *
 * التغطية:
 *  - calculateVolumeCm3: صيغة L×W×T×Q (متوازي المستطيلات)
 *  - cm3ToM3 / m3ToCm3: التحويل بين cm³ و m³
 *  - cm3ToM2: اشتقاق المساحة من الحجم والسمك
 *  - pricePerCm3: اشتقاق سعر cm³ من سعر m³
 *  - حالات حافة (thickness=0, قيم كبيرة, قيم عشرية)
 */
class WoodCalculationServiceTest extends TestCase
{
    private WoodCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WoodCalculationService();
    }

    /** @test */
    public function it_calculates_volume_cm3_using_length_times_width_times_thickness_times_quantity(): void
    {
        // 120cm × 10cm × 2cm × 500 = 1,200,000 cm³
        $volume = $this->service->calculateVolumeCm3(120.0, 10.0, 2.0, 500);

        $this->assertEquals(1200000.0, $volume);
    }

    /** @test */
    public function it_calculates_volume_cm3_for_real_tenantjoo_batch(): void
    {
        // مطابقة للبيانات الفعلية في tenantjoo (wood_stocks id=3)
        // L=120, W=10, T=2, qty=500 -> vol_cm3=1,200,000
        $volume = $this->service->calculateVolumeCm3(120.0, 10.0, 2.0, 500);

        $this->assertEquals(1200000.0, $volume);
    }

    /** @test */
    public function it_converts_cm3_to_m3_and_back(): void
    {
        $cm3 = 1200000.0;
        $m3 = $this->service->cm3ToM3($cm3);

        $this->assertEquals(1.2, $m3);
        $this->assertEquals($cm3, $this->service->m3ToCm3($m3));
    }

    /** @test */
    public function it_converts_cm3_to_m2_using_thickness(): void
    {
        // m² = cm³ / thickness_cm / 10000
        // 1,200,000 / 2 / 10,000 = 60 m²
        $m2 = $this->service->cm3ToM2(1200000.0, 2.0);

        $this->assertEquals(60.0, $m2);
    }

    /** @test */
    public function it_returns_zero_m2_when_thickness_is_zero(): void
    {
        // حالة حافة: حماية من القسمة على صفر
        $m2 = $this->service->cm3ToM2(1200000.0, 0.0);

        $this->assertEquals(0, $m2);
    }

    /** @test */
    public function it_returns_zero_m2_when_thickness_is_negative(): void
    {
        // حالة حافة: حماية من القيم السالبة
        $m2 = $this->service->cm3ToM2(1200000.0, -1.0);

        $this->assertEquals(0, $m2);
    }

    /** @test */
    public function it_calculates_price_per_cm3_from_price_per_m3(): void
    {
        // إذا سعر m³ = 1000 ج.م -> سعر cm³ = 0.001
        $pricePerCm3 = $this->service->pricePerCm3(1000.0);

        $this->assertEquals(0.001, $pricePerCm3);
    }

    /** @test */
    public function it_handles_fractional_dimensions_correctly(): void
    {
        // حالة حقيقية: لوح بسمك 2.5 سم (كسر عشري شائع)
        // 120 × 10 × 2.5 × 100 = 300,000 cm³
        $volume = $this->service->calculateVolumeCm3(120.0, 10.0, 2.5, 100);

        $this->assertEquals(300000.0, $volume);
    }

    /** @test */
    public function it_handles_single_piece_quantity(): void
    {
        // قطعة واحدة فقط (quantity = 1)
        // 200 × 15 × 3 × 1 = 9,000 cm³
        $volume = $this->service->calculateVolumeCm3(200.0, 15.0, 3.0, 1);

        $this->assertEquals(9000.0, $volume);
    }
}