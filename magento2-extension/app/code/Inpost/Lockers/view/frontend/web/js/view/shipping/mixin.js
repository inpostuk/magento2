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
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});