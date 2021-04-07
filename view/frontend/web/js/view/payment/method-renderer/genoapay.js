/*browser:true*/
/*global define*/
define(
    [   'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Latitude_Payment/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Catalog/js/price-utils',
        'Magento_Customer/js/customer-data',
        'mage/translate'
    ],
    function ($,Component, setPaymentMethodAction, additionalValidators, quote, totals,messageList,fullScreenLoader,priceUtils,customerData) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Latitude_Payment/payment/genoapay'
            },
            initialize: function () {
                this._super();
                var _self = this;
                _self.PaymentFaileMsg();

                return this;
            },
            /** Returns send check to info */
            getMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            getLogoUrl: function() {
                return window.checkoutConfig.latitudepayments.genoapay;
            },
            getInstallmentText: function() {
                var grandTotal  = 0,
                    installmentText = '',
                    curInstallment  = window.checkoutConfig.latitudepayments.installmentno,
                    currency        = window.checkoutConfig.latitudepayments.currency_symbol,
                    grandTotal      = totals.getSegment('grand_total').value,
                    html            = window.checkoutConfig.latitudepayments.gpay_installment_block;
                if(grandTotal && html){
                    var amountPerInstallment = grandTotal / curInstallment;
                    installmentText = html.replace(/%s/g,currency + Math.floor(amountPerInstallment * 100) / 100);
                }
                return installmentText;
            },
            /** Redirect to Genoapay */
            continueToGenoapay: function () {
                fullScreenLoader.startLoader();
                if (additionalValidators.validate()) {
                    this.selectPaymentMethod();
                    setPaymentMethodAction(this.messageContainer).done(
                        function () {
                            customerData.invalidate(['cart']);
                            $.get(window.checkoutConfig.payment.latitude.redirectUrl[quote.paymentMethod().method]+'?isAjax=true')
                                .done(function (response) {
                                    if (response['success']) {
                                        if (response['redirect_url']) {
                                            $.mage.redirect(response['redirect_url']);
                                        }
                                    } else {
                                        var msg = $.mage.__('There was an error with your payment, please try again or select other payment method');
                                        if(response['error']){
                                            msg = response['message'];
                                        }
                                        fullScreenLoader.stopLoader();
                                        messageList.addErrorMessage({ message: msg});
                                    }
                                }).fail(function (response) {
                                $.mage.redirect(
                                    window.checkoutConfig.payment.latitude.redirectUrl[quote.paymentMethod().method]+'?method=genoapay'
                                );
                                fullScreenLoader.stopLoader();
                            });
                        }
                    );
                    return false;
                }
            },
            PaymentFaileMsg: function () {
                var cancelUrl = document.URL.split('?')[1];
                if(cancelUrl){
                    var CancelRedirect = cancelUrl.split("/")[0];
                }
                if(CancelRedirect){
                    var msg = $.mage.__('There was an error with your payment, please try again or select other payment method');
                    messageList.addErrorMessage({ message: msg });
                }
            }

        });
    }
);
