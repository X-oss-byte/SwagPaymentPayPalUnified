<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Models\Settings;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\WebhookResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;

class Shopware_Controllers_Backend_PaypalUnifiedSettings extends Shopware_Controllers_Backend_Application
{
    /**
     * {@inheritdoc}
     */
    protected $model = Settings::class;

    /**
     * {@inheritdoc}
     */
    protected $alias = 'settings';

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        $this->settingsService = $this->container->get('paypal_unified.settings_service');
        parent::preDispatch();
    }

    public function detailAction()
    {
        $shopId = (int) $this->Request()->getParam('shopId');

        $settingsModel = $this->settingsService->getSettings($shopId);
        $settings = $settingsModel === null ? ['shopId' => $shopId] : $settingsModel->toArray();

        $this->view->assign('settings', $settings);
    }

    /**
     * This action handles the register webhook request.
     * It configures the RestClient to the provided credentials and announces
     * a wildcard webhook to the PayPal API.
     */
    public function registerWebhookAction()
    {
        $this->configureClient();

        //Generate URL
        /** @var Enlight_Controller_Router $router */
        $router = $this->container->get('front')->Router();
        $url = $router->assemble([
            'module' => 'frontend',
            'controller' => 'PaypalUnifiedWebhook',
            'action' => 'execute',
            'forceSecure' => 1,
        ]);
        $url = str_replace('http://', 'https://', $url);

        /** @var ClientService $clientService */
        $clientService = $this->container->get('paypal_unified.client_service');

        $webhookResource = new WebhookResource($clientService);
        $webhookResource->create($url, ['*']);
        $this->View()->assign('url', $url);
    }

    public function validateAPIAction()
    {
        try {
            $this->configureClient();
            $this->View()->assign('success', true);
        } catch (RequestException $ex) {
            $this->View()->assign('success', false);
            $this->View()->assign('message', json_decode($ex->getBody(), true)['error_description']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save($data)
    {
        $this->configureClient();

        $webProfileService = $this->container->get('paypal_unified.web_profile_service');
        $data['webProfileId'] = $webProfileService->getWebProfile($data);
        $data['webProfileIdEc'] = $webProfileService->getWebProfile($data, true);

        return parent::save($data);
    }

    private function configureClient()
    {
        $shopId = (int) $this->Request()->getParam('shopId');
        $restId = $this->Request()->getParam('clientId');
        $sandbox = (bool) $this->Request()->getParam('sandbox', false);
        $restSecret = $this->Request()->getParam('clientSecret');

        /** @var ClientService $clientService */
        $clientService = $this->container->get('paypal_unified.client_service');
        $clientService->configure([
            'clientId' => $restId,
            'clientSecret' => $restSecret,
            'sandbox' => $sandbox,
            'shopId' => $shopId,
        ]);
    }
}
