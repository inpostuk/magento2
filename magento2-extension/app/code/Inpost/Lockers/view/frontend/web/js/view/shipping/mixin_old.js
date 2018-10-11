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
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
], function (
    translate,
    quote,
    messageList,
    locker,
    ko,
    $,
    createBillingAddress,
    selectBillingAddress,
    createShippingAddress,
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
            var address = quote.shippingAddress(),
                hasLockerMachine;
            this.lockerErrors('');

            if (!address.hasOwnProperty('extensionAttributes')) {
                Object.defineProperty(address, 'extensionAttributes', {
                    value: {},
                    writable: true,
                    enumerable: true,
                    configurable: true
                });
            }

            hasLockerMachine = address.hasOwnProperty('extensionAttributes') &&
                typeof address.extensionAttributes === 'object' &&
                address.extensionAttributes.hasOwnProperty('lockerMachine') &&
                !!address.extensionAttributes.lockerMachine;

            if (quote.shippingMethod().carrier_code === locker().methodCode && !hasLockerMachine) {
                this.lockerErrors('Please choose InPost Locker.');
                return false;
            }

            var selectedLocker = JSON.parse(window.localStorage.getItem('selected_locker'));
            window.selectedCarrier = quote.shippingMethod().carrier_code;

            if (selectedLocker && quote.shippingMethod().carrier_code == 'inpost') {
                var lockerAddress = this.getChild('inpost-shipping-method').selectedLockerAddress();
                if (!this._super()) {
                    this.getChild('inpost-shipping-method').selectedLockerAddress(lockerAddress);
                    return false;
                }

                if (customerData.hasOwnProperty('id')) {
                    var addressId = quote.shippingAddress().customerAddressId;
                    if (checkoutConfig.customerData.addresses != undefined) {
                        for (var i = 0; i < checkoutConfig.customerData.addresses.length; i++) {
                            if (addressId != undefined) {
                                if (checkoutConfig.customerData.addresses[i].id == addressId) {
                                    addressData = checkoutConfig.customerData.addresses[i];
                                    addressData.region = '';
                                    flag = true;
                                    break;
                                }
                            } else {
                                if (checkoutConfig.customerData.addresses[i].default_billing) {
                                    addressData = checkoutConfig.customerData.addresses[i];
                                    addressData.region = '';
                                    flag = true;
                                    break;
                                }
                            }
                        }
                    }
                    var newBillingAddress = createBillingAddress(addressData);
                    selectBillingAddress(newBillingAddress);
                } else {
                    var newBillingAddress = createBillingAddress(quote.shippingAddress());
                    selectBillingAddress(newBillingAddress);
                }

                address = $.extend(true, {}, quote.shippingAddress());
                address.city = selectedLocker.city;
                address.postcode = selectedLocker.post_code;
                address.company = 'InPost Locker - ' + selectedLocker.building_no;
                /*address.region = selectedLocker.province;*/
                address.street[0] = selectedLocker.street;
                address.street[1] = '';

                //var newShippingAddress = createShippingAddress(address);
                address.canUseForBilling = function () {
                    return false;
                };
                selectShippingAddress(address);

                window.localStorage.setItem('shipping_setted', true);
                address.extensionAttributes.lockerMachine = selectedLocker.id;

                this.getChild('inpost-shipping-method').selectedLockerAddress(lockerAddress);

                return true;
            } else {
                this._super();
                var flag = false;
                var addressData = this.source.get('shippingAddress');
                if (addressData.postcode == null || addressData.postcode == '') {
                    var addressId = quote.shippingAddress().customerAddressId;
                    if (checkoutConfig.customerData.addresses != undefined && addressId) {
                        for (var i = 0; i < checkoutConfig.customerData.addresses.length; i++) {
                            if (checkoutConfig.customerData.addresses[i].id == addressId) {
                                addressData = checkoutConfig.customerData.addresses[i];
                                addressData.region = '';
                                flag = true;
                                break;
                            }
                        }
                    }
                }
                var newShippingAddress = createShippingAddress(addressData);
                if (flag) {
                    newShippingAddress.customerAddressId = checkoutConfig.customerData.addresses[i].id;
                }
                newShippingAddress.canUseForBilling = function () {
                    return true;
                };
                selectShippingAddress(newShippingAddress);
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