<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware_Controllers_Frontend_Checkout;
use SwagPaymentPayPalUnified\Components\ButtonLocaleService;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class PayLater implements SubscriberInterface
{
    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var ButtonLocaleService
     */
    private $buttonLocaleService;

    public function __construct(SettingsServiceInterface $settingsService, ContextServiceInterface $contextService, ButtonLocaleService $buttonLocaleService)
    {
        $this->settingsService = $settingsService;
        $this->contextService = $contextService;
        $this->buttonLocaleService = $buttonLocaleService;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'addPayLaterButtonButton',
        ];
    }

    /**
     * @return void
     */
    public function addPayLaterButtonButton(Enlight_Event_EventArgs $args)
    {
        /** @var Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->get('subject');

        $action = $subject->Request()->getActionName();
        $isPaypalUnifiedPayLater = (bool) $subject->Request()->getParam('paypalUnifiedPayLater', false);
        if ($action !== 'confirm' || $isPaypalUnifiedPayLater) {
            return;
        }

        /** @var GeneralSettingsModel|null $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings instanceof GeneralSettingsModel || !$generalSettings->getActive()) {
            return;
        }

        $paymentMethod = $subject->View()->getAssign('sPayment');
        if ($paymentMethod['name'] !== PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME) {
            return;
        }

        $subject->View()->assign([
            'showPaypalUnifiedPayLaterButton' => true,
            'paypalUnifiedPayLater' => true,
            'paypalUnifiedPayLaterClientId' => $generalSettings->getSandbox() ? $generalSettings->getSandboxClientId() : $generalSettings->getClientId(),
            'paypalUnifiedPayLaterCurrency' => $this->contextService->getShopContext()->getCurrency()->getCurrency(),
            'paypalUnifiedPayLaterIntent' => $generalSettings->getIntent(),
            'paypalUnifiedPayLaterButtonStyleColor' => $generalSettings->getButtonStyleColor(),
            'paypalUnifiedPayLaterStyleShape' => $generalSettings->getButtonStyleShape(),
            'paypalUnifiedPayLaterStyleSize' => $generalSettings->getButtonStyleSize(),
            'paypalUnifiedPayLaterButtonLocale' => $this->buttonLocaleService->getButtonLocale($generalSettings->getButtonLocale()),
        ]);
    }
}
