// {block name="backend/paypal_unified/apiV2Types"}
Ext.define('Shopware.apps.PaypalUnified.ApiV2Types', {

    apiV2Types: [
        'PayPalClassicV2',
        'PayPalPayLater',
        'PayPalExpressV2',
        'PayPalPayUponInvoiceV2',
        'PayPalSmartPaymentButtonsV2',
        'PayPalAdvancedCreditDebitCardV2',
        'PayPalSepa',
        'bancontact',
        'blik',
        'eps',
        'giropay',
        'ideal',
        'multibanco',
        'mybank',
        'p24',
        'sofort',
        'trustly'
    ],

    /**
     * @return { Array }
     */
    getV2Types: function() {
        return this.apiV2Types;
    },
});
// {/block}
