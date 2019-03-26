/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/core/renderer/types',
    'Magento_Checkout/js/model/quote',
    'jquery/ui',
    'mage/translate',
    'mage/mage',
    'mage/validation'
], function ($, alert, coreTypes, quote) {
    'use strict';

    return function (widget) {

        $.widget('mage.orderReview', widget, {
            _submitUpdateOrder: function (url, resultId) {
                var isChecked, formData, callBackResponseHandler, shippingMethod;

                if (document.getElementById('shipping-method').value == 'inpost_inpost' && !window.localStorage.getItem('selected_locker')) {
                    $('#openInpostPopup').trigger('click');
                    return;
                } else {
                    $('#checkDescriptionVisibility').trigger('click');
                }

                if (this.element.find(this.options.waitLoadingContainer).is(':visible')) {
                    return false;
                }
                isChecked = $(this.options.billingAsShippingSelector).is(':checked');
                formData = null;
                callBackResponseHandler = null;
                shippingMethod = $.trim($(this.options.shippingSelector).val());
                this._shippingTobilling();

                if (url && resultId && shippingMethod) {
                    this._updateOrderSubmit(true);
                    this._toggleButton(this.options.updateOrderSelector, true);

                    // form data and callBack updated based on the shippping Form element
                    if (this.isShippingSubmitForm) {
                        formData = $(this.options.shippingSubmitFormSelector).serialize() + '&isAjax=true';

                        /**
                         * @param {Object} response
                         */
                        callBackResponseHandler = function (response) {
                            $(resultId).html(response);
                            this._updateOrderSubmit(false);
                            this._ajaxComplete();
                        };
                    } else {
                        formData = this.element.serialize() + '&isAjax=true';

                        /**
                         * @param {Object} response
                         */
                        callBackResponseHandler = function (response) {
                            $(resultId).html(response);
                            this._ajaxShippingUpdate(shippingMethod);
                        };
                    }

                    if (document.getElementById('shipping-method').value == 'inpost_inpost') {
                        formData += '&paypal_express=true&locker_id=' + JSON.parse(window.localStorage.getItem('selected_locker')).id;
                    }

                    if (isChecked) {
                        $(this.options.shippingSelect).prop('disabled', true);
                    }
                    $.ajax({
                        url: url,
                        type: 'post',
                        context: this,
                        beforeSend: this._ajaxBeforeSend,
                        data: formData,
                        success: callBackResponseHandler
                    });
                }
            },

            _submitOrder: function() {
                var self = this;
                var phone = document.getElementById('inpost-phone').value;
                if (!phone) {
                    this.showError();
                    return false;
                }
                var patt = new RegExp(/^((((\+|00)?447\s?\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3})|(((\+|00)447\s?\d{2}|\(?07\d{2}\)?)\s?\d{3}\s?\d{4})|(((\+|00)447\s?\d{1}|\(?07\d{1}\)?)\s?\d{4}\s?\d{4}))(\s?\#(\d{4}|\d{3}))?$/);
                var res = patt.test(phone);
                if (!res) {
                    this.showError();
                    return false;
                }
                quote.shippingAddress().telephone = phone;
                $.ajax({
                    url: window.inpostPhoneUpdateUrl,
                    type: 'post',
                    data: {
                        'phone': phone,
                        'quote_id': quote.getQuoteId()
                    },
                    success: self._super(),
                });
            },

            showError: function() {
                $([document.documentElement, document.body]).animate({
                    scrollTop: $("#shipping-method-form").offset().top
                }, 500);
                $('#advice-validate-uk-phone-locker-phone').show(0).delay(3600).hide(0);
            }
        });

        return $.mage.orderReview;
    };

});
