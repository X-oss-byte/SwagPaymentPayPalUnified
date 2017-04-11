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

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources;

class Sale extends RelatedResource
{
    /**
     * @var string
     */
    private $paymentMode;

    /**
     * @var string
     */
    private $protectionEligibility;

    /**
     * @var string
     */
    private $protectionEligibilityType;

    /**
     * @var TransactionFee
     */
    private $transactionFee;

    /**
     * @var string
     */
    private $receiptId;

    /**
     * @return string
     */
    public function getPaymentMode()
    {
        return $this->paymentMode;
    }

    /**
     * @param string $paymentMode
     */
    public function setPaymentMode($paymentMode)
    {
        $this->paymentMode = $paymentMode;
    }

    /**
     * @return string
     */
    public function getProtectionEligibility()
    {
        return $this->protectionEligibility;
    }

    /**
     * @param string $protectionEligibility
     */
    public function setProtectionEligibility($protectionEligibility)
    {
        $this->protectionEligibility = $protectionEligibility;
    }

    /**
     * @return string
     */
    public function getProtectionEligibilityType()
    {
        return $this->protectionEligibilityType;
    }

    /**
     * @param string $protectionEligibilityType
     */
    public function setProtectionEligibilityType($protectionEligibilityType)
    {
        $this->protectionEligibilityType = $protectionEligibilityType;
    }

    /**
     * @return TransactionFee
     */
    public function getTransactionFee()
    {
        return $this->transactionFee;
    }

    /**
     * @param TransactionFee $transactionFee
     */
    public function setTransactionFee($transactionFee)
    {
        $this->transactionFee = $transactionFee;
    }

    /**
     * @return string
     */
    public function getReceiptId()
    {
        return $this->receiptId;
    }

    /**
     * @param string $receiptId
     */
    public function setReceiptId($receiptId)
    {
        $this->receiptId = $receiptId;
    }

    /**
     * @param array $data
     *
     * @return Sale
     */
    public static function fromArray(array $data)
    {
        $result = new self();
        $result->prepare($result, $data, ResourceType::SALE);

        $result->setPaymentMode($data['payment_mode']);
        $result->setProtectionEligibility($data['protection_eligibility']);
        $result->setProtectionEligibilityType($data['protection_eligibility_type']);
        $result->setTransactionFee(TransactionFee::fromArray($data['transaction_fee']));
        $result->setReceiptId($data['receipt_id']);

        return $result;
    }
}