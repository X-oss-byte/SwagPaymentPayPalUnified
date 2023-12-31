<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone;

use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class PhoneNumber extends PayPalApiStruct
{
    /**
     * @var string
     */
    protected $nationalNumber;

    /**
     * @var string
     */
    protected $countryCode;

    /**
     * @return string
     */
    public function getNationalNumber()
    {
        return $this->nationalNumber;
    }

    /**
     * @param string $nationalNumber
     *
     * @return void
     */
    public function setNationalNumber($nationalNumber)
    {
        $this->nationalNumber = $nationalNumber;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }
}
