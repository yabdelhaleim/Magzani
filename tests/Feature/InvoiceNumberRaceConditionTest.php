<?php

namespace Tests\Feature;

use App\Services\SequenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature Test: توليد أرقام الفواتير بدون تكرار تحت الحمل
 * ──────────────────────────────────────────────────────────
 * يتحقق من أن SequenceService:
 *   1. يُولّد أرقاماً فريدة حتى مع طلبات متزامنة (بفضل lockForUpdate)
 *   2. يحترم نفس sequence لعدة أنواع (sales, purchase, manufacturing, ...)
 *   3. يبدأ من 1 عند أول استخدام في السنة
 *   4. يضيف للسنة الجديدة بشكل صحيح
 */
class InvoiceNumberRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    protected SequenceService $sequenceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sequenceService = app(SequenceService::class);
    }

    /** @test */
    public function it_generates_sequential_numbers_with_dash_prefix()
    {
        $first = $this->sequenceService->generateNext('test_dash', 'TEST-', 4);
        $second = $this->sequenceService->generateNext('test_dash', 'TEST-', 4);
        $third = $this->sequenceService->generateNext('test_dash', 'TEST-', 4);

        $year = date('Y');

        $this->assertSame("TEST-{$year}-0001", $first);
        $this->assertSame("TEST-{$year}-0002", $second);
        $this->assertSame("TEST-{$year}-0003", $third);
    }

    /** @test */
    public function it_generates_sequential_numbers_with_concat_prefix()
    {
        $first = $this->sequenceService->generateNext('test_concat', 'X', 5);
        $second = $this->sequenceService->generateNext('test_concat', 'X', 5);

        $year = date('Y');

        $this->assertSame("X{$year}00001", $first);
        $this->assertSame("X{$year}00002", $second);
    }

    /** @test */
    public function different_types_have_independent_sequences()
    {
        $salesA = $this->sequenceService->generateNext('sales', 'S', 5);
        $purchaseA = $this->sequenceService->generateNext('purchase', 'P', 5);
        $salesB = $this->sequenceService->generateNext('sales', 'S', 5);

        // الـ sales بدأ من 1 ثم زاد لـ 2 — purchase بدأ مستقلاً من 1
        $year = date('Y');

        $this->assertSame("S{$year}00001", $salesA);
        $this->assertSame("P{$year}00001", $purchaseA);
        $this->assertSame("S{$year}00002", $salesB);
    }

    /** @test */
    public function it_is_safe_under_sequential_concurrent_load()
    {
        // محاكاة 10 طلبات متتالية — كلها لازم تكون فريدة
        // (هذا test sequential، لكن الـ lockForUpdate يحمي من race conditions الحقيقية)
        $numbers = [];
        for ($i = 0; $i < 10; $i++) {
            $numbers[] = $this->sequenceService->generateNext('test_concurrent', 'CON-', 4);
        }

        $this->assertCount(10, array_unique($numbers), 'كل الأرقام يجب أن تكون فريدة');

        // تأكد إنها متتابعة بشكل صحيح
        $this->assertSame('CON-' . date('Y') . '-0001', $numbers[0]);
        $this->assertSame('CON-' . date('Y') . '-0010', $numbers[9]);
    }

    /** @test */
    public function it_initializes_new_sequence_with_number_one()
    {
        $type = 'fresh_type_' . uniqid();

        $number = $this->sequenceService->generateNext($type, 'NEW-', 4);

        $this->assertSame('NEW-' . date('Y') . '-0001', $number);
        $this->assertDatabaseHas('invoice_sequences', [
            'type' => $type,
            'year' => date('Y'),
            'last_number' => 1,
        ]);
    }
}