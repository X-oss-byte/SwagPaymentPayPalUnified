<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber\ControllerRegistration;

use Enlight\Event\SubscriberInterface;

class Frontend implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDirectory;

    /**
     * @param string $pluginDirectory
     */
    public function __construct($pluginDirectory)
    {
        $this->pluginDirectory = $pluginDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnified' => 'onGetUnifiedControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedV2' => 'onGetUnifiedControllerPathV2',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedWebhook' => 'onGetWebhookControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedV2PayUponInvoice' => 'onGetUnifiedV2PayUponInvoiceControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedApm' => 'onGetPaypalUnifiedApmControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedAdvancedCreditDebitCard' => 'onGetPaypalUnifiedAdvancedCreditDebitCardControllerPath',
        ];
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnified event.
     * Returns the path to the frontend controller.
     *
     * @return string
     */
    public function onGetUnifiedControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Frontend/PaypalUnified.php';
    }

    /**
     * @return string
     */
    public function onGetUnifiedControllerPathV2()
    {
        return $this->pluginDirectory . '/Controllers/Frontend/PaypalUnifiedV2.php';
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedWebhook event.
     * Returns the path to the webhook controller.
     *
     * @return string
     */
    public function onGetWebhookControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Frontend/PaypalUnifiedWebhook.php';
    }

    /**
     * @return string
     */
    public function onGetUnifiedV2PayUponInvoiceControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Frontend/PaypalUnifiedV2PayUponInvoice.php';
    }

    /**
     * @return string
     */
    public function onGetPaypalUnifiedApmControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Frontend/PaypalUnifiedApm.php';
    }

    /**
     * @return string
     */
    public function onGetPaypalUnifiedAdvancedCreditDebitCardControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Frontend/PaypalUnifiedV2AdvancedCreditDebitCard.php';
    }
}
