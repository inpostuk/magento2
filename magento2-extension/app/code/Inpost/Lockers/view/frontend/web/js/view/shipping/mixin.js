/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

define([
    'mage/translate',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/model/messageList',
    'Inpost_Lockers/js/view/locker',
    'ko',
    'jquery',
    'Magento_Checkout/js/action/select-shipping-address'
], function (
    translate,
    quote,
    messageList,
    locker,
    ko,
    $,
    selectShippingAddress
) {
    'use strict';

    var mixin = {
        lockerErrors: ko.observable(),

        /**
         * Extend parent method to validate extension attributes data
         *
         * @returns {*}
         */
        validateShippingInformation: function () {
            var shippingAddress = quote.shippingAddress(),
                lockerAddress = this.getChild('inpost-shipping-method').selectedLockerAddress();

            if (this.getChild('inpost-shipping-method').showPhoneField()) {
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
                    }
                });
            }
            this.lockerErrors('');
            var hasLockerMachine = shippingAddress.hasOwnProperty('extensionAttributes') &&
                typeof shippingAddress.extensionAttributes === 'object' &&
                shippingAddress.extensionAttributes.hasOwnProperty('lockerMachine') &&
                !!shippingAddress.extensionAttributes.lockerMachine;

            if (!this._super()) {
                return false;
            }
            if (quote.shippingMethod().carrier_code === locker().methodCode) {
                var selectedLocker = JSON.parse(window.localStorage.getItem('selected_locker'));
                if (!hasLockerMachine || !selectedLocker) {
                    this.lockerErrors('Please choose InPost Locker.');
                    return false;
                }
                this.getChild('inpost-shipping-method').selectedLockerAddress(lockerAddress);
                quote.shippingAddress().extensionAttributes.lockerMachine = selectedLocker.id;
            } else {
                if (shippingAddress.hasOwnProperty('extensionAttributes') &&
                    typeof shippingAddress.extensionAttributes === 'object' &&
                    shippingAddress.extensionAttributes.hasOwnProperty('lockerMachine')) {
                    shippingAddress.extensionAttributes.lockerMachine = null;
                }
                if (window.localStorage.getItem('selected_locker')) {
                    window.localStorage.removeItem('selected_locker');
                }
            }
            return true;
        },


        selectShippingMethod: function (shippingMethod) {
            if (shippingMethod.carrier_code == 'inpost') {
                $('.choose-locker').trigger('click');
            }
            return this._super();
        },

        showError: function() {
            $('#advice-validate-uk-phone-locker-phone').show(0).delay(3600).hide(0);
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});