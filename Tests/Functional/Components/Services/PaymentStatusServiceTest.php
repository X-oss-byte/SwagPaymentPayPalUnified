<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use Generator;
use PHPUnit\Framework\TestCase;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\Services\LoggerService;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class PaymentStatusServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use ContainerTrait;
    use SettingsHelperTrait;
    use ShopRegistrationTrait;

    const ANY_ID = 9999;
    const SHOPWARE_ORDER_ID = 6655;
    const EXPECTS_EXCEPTION = true;
    const EXPECTS_NO_EXCEPTION = false;

    /**
     * @dataProvider UpdatePaymentStatusV2TestDataProvider
     *
     * @param int         $shopwareOrderId
     * @param int         $paymentStateId
     * @param bool        $expectsException
     * @param string|null $exceptionClassString
     *
     * @return void
     */
    public function testUpdatePaymentStatusV2($shopwareOrderId, $paymentStateId, $expectsException, $exceptionClassString = null)
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/order_payment_state_test.sql');
        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->executeUpdate($sql, ['orderId' => self::SHOPWARE_ORDER_ID]);

        $paymentStatusService = $this->createPaymentStatusService();

        if ($expectsException) {
            static::assertTrue(\is_string($exceptionClassString), 'Parameter "exceptionClassString" is requred if this test expects a exception');

            $this->expectException($exceptionClassString);
        }

        $paymentStatusService->updatePaymentStatusV2($shopwareOrderId, $paymentStateId);

        if ($expectsException) {
            return;
        }

        $modelManager = $this->getContainer()->get('models');
        $modelManager->clear(Order::class);
        $order = $modelManager->getRepository(Order::class)->find($shopwareOrderId);
        static::assertInstanceOf(Order::class, $order);

        static::assertSame($paymentStateId, $order->getPaymentStatus()->getId());
        static::assertNotNull($order->getClearedDate());
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function UpdatePaymentStatusV2TestDataProvider()
    {
        yield 'Payment status should be updated' => [
            self::SHOPWARE_ORDER_ID,
            Status::PAYMENT_STATE_COMPLETELY_PAID,
            self::EXPECTS_NO_EXCEPTION,
        ];
    }

    /**
     * @dataProvider determinePaymentStausForCapturingTestDataProvider
     *
     * @param bool  $finalize
     * @param float $amountToCapture
     * @param float $maxCaptureAmount
     * @param int   $expectedResult
     *
     * @return void
     */
    public function testDeterminePaymentStausForCapturing($finalize, $amountToCapture, $maxCaptureAmount, $expectedResult)
    {
        $service = $this->createPaymentStatusService();

        $result = $service->determinePaymentStausForCapturing($finalize, $amountToCapture, $maxCaptureAmount);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function determinePaymentStausForCapturingTestDataProvider()
    {
        yield 'Is finalized' => [
            true,
            0.00,
            0.00,
            Status::PAYMENT_STATE_COMPLETELY_PAID,
        ];

        yield 'Not finalized amountToCapture is less than maxCaptureAmount' => [
            false,
            5.00,
            6.00,
            Status::PAYMENT_STATE_PARTIALLY_PAID,
        ];

        yield 'Not finalized amountToCapture equals maxCaptureAmount' => [
            false,
            5.00,
            5.00,
            Status::PAYMENT_STATE_COMPLETELY_PAID,
        ];

        yield 'Not finalized amountToCapture is larger than maxCaptureAmount' => [
            false,
            6.00,
            5.00,
            Status::PAYMENT_STATE_COMPLETELY_PAID,
        ];
    }

    /**
     * @return PaymentStatusService
     */
    private function createPaymentStatusService()
    {
        return new PaymentStatusService(
            $this->getContainer()->get('models'),
            $this->createMock(LoggerService::class),
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('paypal_unified.dependency_provider')
        );
    }
}
