<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Components_Session_Namespace;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\NumberRangeIncrementerDecorator;
use SwagPaymentPayPalUnified\Components\OrderNumberService;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\Components\Services\Validation\SimpleBasketValidator;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\AssertLocationTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend\_fixtures\SimplePayPalOrderCreator;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\ConnectionMock;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

require __DIR__ . '/../../../../Controllers/Frontend/PaypalUnifiedV2ExpressCheckout.php';

class PaypalUnifiedV2ExpressCheckoutExpressCheckoutFinishActionTest extends PaypalPaymentControllerTestCase
{
    use ShopRegistrationTrait;
    use ContainerTrait;
    use SettingsHelperTrait;
    use DatabaseTestCaseTrait;
    use AssertLocationTrait;

    /**
     * @return void
     */
    public function testExpressCheckoutFinishAction()
    {
        $this->insertGeneralSettingsFromArray(['active' => 1]);

        $orderNumber = '66666666666';

        $session = $this->getContainer()->get('session');
        $session->offsetSet(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY, $orderNumber);
        $session->offsetSet('sUserId', 1);
        $session->offsetSet('sOrderVariables', [
            'sBasket' => require __DIR__ . '/_fixtures/getBasket_result.php',
            'sUserData' => require __DIR__ . '/_fixtures/getUser_result.php',
        ]);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('token', '123456789');

        $response = new Enlight_Controller_Response_ResponseTestCase();

        $settingsServiceMock = $this->createMock(SettingsService::class);

        $payPalOrder = $this->createPayPalOrder();

        $orderResourceMock = $this->createMock(OrderResource::class);
        $orderResourceMock->method('get')->willReturn($payPalOrder);
        $orderResourceMock->method('capture')->willReturn($payPalOrder);

        $simpleBasketValidatorMock = $this->createMock(SimpleBasketValidator::class);
        $simpleBasketValidatorMock->method('validate')->willReturn(true);

        $orderNumberServiceMock = $this->createMock(OrderNumberService::class);
        $orderNumberServiceMock->method('getOrderNumber')->willReturn($orderNumber);

        $orderDataServiceMock = $this->createMock(OrderDataService::class);
        $orderDataServiceMock->expects(static::once())->method('applyPaymentTypeAttribute');

        $paymentStatusServiceMock = $this->createMock(PaymentStatusService::class);

        $sessionMock = $this->createMock(Enlight_Components_Session_Namespace::class);
        $sessionMock->method('offsetExists')->willReturn(false);

        $dependencyProviderMock = $this->createMock(DependencyProvider::class);
        $dependencyProviderMock->method('getSession')->willReturn($sessionMock);

        $paypalUnifiedV2Controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2ExpressCheckout::class,
            [
                self::SERVICE_SETTINGS_SERVICE => $settingsServiceMock,
                self::SERVICE_ORDER_RESOURCE => $orderResourceMock,
                self::SERVICE_SIMPLE_BASKET_VALIDATOR => $simpleBasketValidatorMock,
                self::SERVICE_ORDER_NUMBER_SERVICE => $orderNumberServiceMock,
                self::SERVICE_ORDER_DATA_SERVICE => $orderDataServiceMock,
                self::SERVICE_PAYMENT_STATUS_SERVICE => $paymentStatusServiceMock,
                self::SERVICE_DBAL_CONNECTION => (new ConnectionMock())->createConnectionMock('1', 'fetch'),
                self::SERVICE_DEPENDENCY_PROVIDER => $dependencyProviderMock,
                self::SERVICE_ORDER_FACTORY => $this->getContainer()->get('paypal_unified.order_factory'),
                self::SERVICE_ORDER_PARAMETER_FACADE => $this->getContainer()->get('paypal_unified.paypal_order_parameter_facade'),
            ],
            $request,
            $response
        );

        $paypalUnifiedV2Controller->expressCheckoutFinishAction();

        static::assertLocationEndsWith($response, '/checkout/finish/sUniqueID/123456789');
        static::assertSame(302, $response->getHttpResponseCode());
    }

    /**
     * @return Order
     */
    private function createPayPalOrder()
    {
        return (new SimplePayPalOrderCreator())->createSimplePayPalOrder();
    }
}
