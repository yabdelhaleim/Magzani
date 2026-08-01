<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\BusinessLogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * اختبارات وحدة لـ BusinessLogicException
 * ───────────────────────────────────────────
 * تتحقق من:
 *   - وراثة RuntimeException (لذلك تعمل catch كـ RuntimeException)
 *   - تخزين السياق (context) بشكل صحيح
 *   - إرجاع السياق عبر getContext()
 *   - الـ message يظل متاحاً عبر getMessage() الموروث
 */
class BusinessLogicExceptionTest extends TestCase
{
    /** @test */
    public function it_extends_runtime_exception_for_backward_compatibility()
    {
        $e = new BusinessLogicException('تجاوز الحد');

        $this->assertInstanceOf(RuntimeException::class, $e);
    }

    /** @test */
    public function it_stores_context_array()
    {
        $context = ['customer_id' => 42, 'credit_limit' => 1000, 'new_balance' => 1500];
        $e = new BusinessLogicException('تجاوز الحد', $context);

        $this->assertSame($context, $e->getContext());
    }

    /** @test */
    public function it_returns_empty_context_by_default()
    {
        $e = new BusinessLogicException('simple error');

        $this->assertSame([], $e->getContext());
    }

    /** @test */
    public function it_preserves_the_message()
    {
        $message = 'الفاتورة ملغاة بالفعل';
        $e = new BusinessLogicException($message, ['invoice_id' => 7]);

        $this->assertSame($message, $e->getMessage());
    }

    /** @test */
    public function it_can_be_caught_as_runtime_exception()
    {
        try {
            throw new BusinessLogicException('credit limit', ['x' => 1]);
        } catch (RuntimeException $e) {
            $this->assertSame('credit limit', $e->getMessage());

            // الـ context لا يُفقد رغم الوراثة
            if (method_exists($e, 'getContext')) {
                $this->assertSame(['x' => 1], $e->getContext());
            }
            return;
        }

        $this->fail('BusinessLogicException should be catchable as RuntimeException');
    }
}