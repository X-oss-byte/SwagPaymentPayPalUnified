<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Resource;

use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\RequestUriV2;

class OrderResource
{
    /**
     * @var ClientService
     */
    private $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * @param string $orderId
     *
     * @return Order
     */
    public function get($orderId)
    {
        $response = $this->clientService->sendRequest(
            RequestType::GET,
            \sprintf('%s/%s', RequestUriV2::ORDERS_RESOURCE, $orderId)
        );

        return (new Order())->assign($response);
    }

    /**
     * @param string $partnerAttributionId
     * @param bool   $minimalResponse
     *
     * @return Order
     */
    public function create(
        Order $order,
        $partnerAttributionId,
        $minimalResponse = true
    ) {
        $this->clientService->setPartnerAttributionId($partnerAttributionId);
        if ($minimalResponse === false) {
            $this->clientService->setHeader('Prefer', 'return=representation');
        }

        $response = $this->clientService->sendRequest(
            RequestType::POST,
            RequestUriV2::ORDERS_RESOURCE,
            $order->toArray()
        );

        return (new Order())->assign($response);
    }

    /**
     * @param Patch[] $patches
     * @param string  $orderId
     * @param string  $partnerAttributionId
     *
     * @return void
     */
    public function update(array $patches, $orderId, $partnerAttributionId)
    {
        $this->clientService->setPartnerAttributionId($partnerAttributionId);
        $this->clientService->sendRequest(
            RequestType::PATCH,
            \sprintf('%s/%s', RequestUriV2::ORDERS_RESOURCE, $orderId),
            $patches
        );
    }

    /**
     * @param string $orderId
     * @param string $partnerAttributionId
     * @param bool   $minimalResponse
     *
     * @return Order
     */
    public function capture(
        $orderId,
        $partnerAttributionId,
        $minimalResponse = false
    ) {
        $this->clientService->setPartnerAttributionId($partnerAttributionId);
        if ($minimalResponse === false) {
            $this->clientService->setHeader('Prefer', 'return=representation');
        }

        $response = $this->clientService->sendRequest(
            RequestType::POST,
            \sprintf('%s/%s/capture', RequestUriV2::ORDERS_RESOURCE, $orderId),
            null
        );

        return (new Order())->assign($response);
    }

    /**
     * @param string $orderId
     * @param string $partnerAttributionId
     * @param bool   $minimalResponse
     *
     * @return Order
     */
    public function authorize(
        $orderId,
        $partnerAttributionId,
        $minimalResponse = false
    ) {
        $this->clientService->setPartnerAttributionId($partnerAttributionId);
        if ($minimalResponse === false) {
            $this->clientService->setHeader('Prefer', 'return=representation');
        }

        $response = $this->clientService->sendRequest(
            RequestType::POST,
            \sprintf('%s/%s/authorize', RequestUriV2::ORDERS_RESOURCE, $orderId),
            null
        );

        return (new Order())->assign($response);
    }
}
