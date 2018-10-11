/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/core/renderer/types',
    'jquery/ui',
    'mage/translate',
    'mage/mage',
    'mage/validation'
], function ($, alert, coreTypes) {
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
        });

        return $.mage.orderReview;
    };

});
