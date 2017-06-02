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

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Documents;

use Doctrine\DBAL\Connection;
use Enlight_Class;
use Shopware_Models_Document_Order;
use SwagPaymentPayPalUnified\Subscriber\Documents\Installments;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\PayPalUnifiedPaymentIdTrait;

class InstallmentsSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;
    use PayPalUnifiedPaymentIdTrait;

    public function test_construct()
    {
        $subscriber = new Installments(
            Shopware()->Container()->get('paypal_unified.installments.order_credit_info_service'),
            Shopware()->Container()->get('dbal_connection')
        );
        $this->assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents()
    {
        $events = Installments::getSubscribedEvents();

        $this->assertCount(1, $events);
        $this->assertEquals('onBeforeRenderDocument', $events['Shopware_Components_Document::assignValues::after']);
    }

    public function test_onBeforeRenderDocument_returns_when_no_document_was_given()
    {
        $subscriber = new Installments(
            Shopware()->Container()->get('paypal_unified.installments.order_credit_info_service'),
            Shopware()->Container()->get('dbal_connection')
        );

        $hookArgs = new HookArgsWithoutSubject();

        $this->assertNull($subscriber->onBeforeRenderDocument($hookArgs));
    }

    public function test_onBeforeRenderDocument_returns_when_wrong_payment_id_was_given()
    {
        $subscriber = new Installments(
            Shopware()->Container()->get('paypal_unified.installments.order_credit_info_service'),
            Shopware()->Container()->get('dbal_connection')
        );

        $hookArgs = new HookArgsWithWrongPaymentId();

        $this->assertNull($subscriber->onBeforeRenderDocument($hookArgs));
    }

    public function test_onBeforeRenderDocument_handleDocument()
    {
        $subscriber = new Installments(
            Shopware()->Container()->get('paypal_unified.installments.order_credit_info_service'),
            Shopware()->Container()->get('dbal_connection')
        );

        $this->updateOrderPaymentId(15, $this->getInstallmentsPaymentId());
        $this->insertTestData();

        $hookArgs = new HookArgsWithCorrectPaymentId();

        $subscriber->onBeforeRenderDocument($hookArgs);
        $view = $hookArgs->getView();

        $this->assertNotNull($view->getVariable('paypalInstallmentsCredit')->value);
    }

    private function insertTestData()
    {
        /** @var Connection $db */
        $db = Shopware()->Container()->get('dbal_connection');

        $sql = "INSERT INTO `swag_payment_paypal_unified_financing_information` (`payment_id`, `fee_amount`, `total_cost`, `term`, `monthly_payment`)
                VALUES ('TEST_PAYMENT_ID', 10.01, 1400.04, 12, 67.68);";
        $db->executeUpdate($sql);

        $sql = "UPDATE s_order SET temporaryID='TEST_PAYMENT_ID' WHERE id=15";
        $db->executeUpdate($sql);
    }

    /**
     * @param int $orderId
     * @param int $paymentId
     */
    private function updateOrderPaymentId($orderId, $paymentId)
    {
        $db = Shopware()->Container()->get('dbal_connection');

        $sql = 'UPDATE s_order SET paymentID=:paymentId WHERE id=:orderId';
        $db->executeUpdate($sql, [
            ':paymentId' => $paymentId,
            ':orderId' => $orderId,
        ]);
    }
}

class HookArgsWithoutSubject extends \Enlight_Hook_HookArgs
{
    public function getSubject()
    {
        return null;
    }
}

class HookArgsWithWrongPaymentId extends \Enlight_Hook_HookArgs
{
    /**
     * @return Enlight_Class
     */
    public function getSubject()
    {
        $subject = Enlight_Class::Instance(\Shopware_Components_Document::class);

        $subject->_order = new Shopware_Models_Document_Order(15);

        return $subject;
    }
}

class HookArgsWithCorrectPaymentId extends \Enlight_Hook_HookArgs
{
    /**
     * @var \Smarty_Data
     */
    private $_view;

    /**
     * @var \Enlight_Template_Manager
     */
    private $_template;

    public function getSubject()
    {
        $subject = Enlight_Class::Instance(\Shopware_Components_Document::class);

        $subject->_order = new Shopware_Models_Document_Order(15);
        $subject->_view = new \Smarty_Data();
        $subject->_template = new \Enlight_Template_Manager();

        $this->_view = $subject->_view;
        $this->_template = $subject->_template;

        return $subject;
    }

    /**
     * @return \Smarty_Data
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * @return \Enlight_Template_Manager
     */
    public function getTemplate()
    {
        return $this->_template;
    }
}