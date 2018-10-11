/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/resource-url-manager',
    'mage/storage',
    'googleMaps',
    'uiRegistry'
], function (
    $,
    Component,
    ko,
    quote,
    modal,
    $t,
    fullScreenLoader,
    resourceUrlManager,
    storage,
    googleMaps,
    uiRegistry
) {
    'use strict';

    var popUp = null,
        localStorage = [];
    return Component.extend({
        defaults: {
            methodCode: 'inpost'
        },
        isLockerPopUpVisible: ko.observable(false),
        isPopUpButtonVisible: ko.observable(false),
        selectedLockerAddress: ko.observable(''),
        getPostCode: ko.observable(''),
        findOutShow: ko.observable(false),
        defaultPostCode: ko.observable('London'),

        /**
         * @return {exports}
         */
        initialize: function () {
            var self = this;
            this._super();

            this.isLockerPopUpVisible.subscribe(function (value) {
                if (value) {
                    self.getPopUp().openModal();
                }
            });

            quote.shippingMethod.subscribe(function (method) {
                var popupButtonVisible = method.carrier_code === self.methodCode;
                if (!popupButtonVisible) {
                    self.setLocker({});
                }
                self.isPopUpButtonVisible(popupButtonVisible);
            });

            quote.shippingAddress.subscribe(function (address) {
                self.setLocker({});
            });

            return this;
        },

        showFindOut: function () {
            document.getElementById('inpost-overlay').style.display = 'block'
            document.getElementById('inpost-popup').style.display = 'block'
        },

        resetSelectedLocker: function () {
            var self = this,
                parent = uiRegistry.get(this.parentName),
                address = quote.shippingAddress();
            parent.lockerErrors('');
            if (address.extensionAttributes.hasOwnProperty('lockerMachine') && address.extensionAttributes.lockerMachine) {
                var lockers = JSON.parse(window.localStorage.getItem(window.localStorage.getItem('geoRequestData')));
                if (lockers) {
                    lockers = lockers.response;
                    $.each(lockers, function (index, locker) {
                        if (locker.id == address.extensionAttributes.lockerMachine) {
                            var html = "<p class='title'>Locker selected:</p>" +
                                "<p>InPost Locker - " + locker.building_no + "</p>" +
                                "<p>" + locker.street + "</p>" +
                                "<p>" + locker.city + ", " + locker.post_code + "</p>";
                            self.selectedLockerAddress(html);
                            self.getPopUp().closeModal();
                            return;
                        }
                    });
                }
            }
        },

        setLocker: function (locker) {
            var address = quote.shippingAddress();
            if (!address.hasOwnProperty('extensionAttributes')) {
                Object.defineProperty(address, 'extensionAttributes', {
                    value: {},
                    writable: true,
                    enumerable: true,
                    configurable: true
                });
            }

            if (!address.extensionAttributes.hasOwnProperty('lockerMachine')) {
                Object.defineProperty(address.extensionAttributes, 'lockerMachine', {
                    writable: true,
                    enumerable: true,
                    configurable: true
                });
            }

            address.extensionAttributes.lockerMachine = $.isEmptyObject(locker) ? false : locker.id;
            this.selectedLockerAddress($.isEmptyObject(locker) ? '' : locker.building_no);
        },

        /**
         * @return {*}
         */
        getPopUp: function () {
            var self = this,
                buttons;

            if (!popUp) {
                this.popUpForm.options.closed = function () {
                    self.isLockerPopUpVisible(false);
                };

                this.popUpForm.options.modalCloseBtnHandler = this.onClosePopUp.bind(this);
                this.popUpForm.options.keyEventHandlers = {
                    escapeKey: this.onClosePopUp.bind(this)
                };

                popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
            }

            return popUp;
        },

        /**
         * Close lockers map popup
         */
        onClosePopUp: function () {
            this.getPopUp().closeModal();
        },

        /**
         * Show lockers map popup
         *
         * @param {Object} init
         * @param {Array} lockers
         */
        showLockerPopUp: function (init, lockers) {
            window.initLocation = init;
            this.isLockerPopUpVisible(true);
            $('#reload-left-lockers-block').trigger('click');
            fullScreenLoader.stopLoader();
        },

        precisionRound: function (number, precision) {
            var factor = Math.pow(10, precision);
            return Math.round(number * factor) / factor;
        },

        getDescription: function () {
            return window.inpostDescription;
        },

        /**
         * Trigger request to geocode shipping address and
         * retrieve nearest locker machine list
         *
         * @param {Object} element
         * @param {Object} event
         */
        showLockersMap: function (element, event, loadWithPostcode = null) {
            var self = this,
                shippingAddress = quote.shippingAddress(),
                geoCoder = new googleMaps.Geocoder(),
                parent = uiRegistry.get(this.parentName),
                geoRequestData,
                location,
                payload,
                storageData;
            event.stopPropagation();

            try {
                var address = shippingAddress;
                if (quote.billingAddress() != null) {
                    address = quote.billingAddress()
                }
                var flagUsedDefaultPostcode = false;
                if (!loadWithPostcode) {
                    var postcode = address.postcode;
                    if (!postcode) {
                        postcode = self.defaultPostCode();
                        flagUsedDefaultPostcode = true;
                    }
                } else {
                    var postcode = loadWithPostcode;
                    flagUsedDefaultPostcode = true;
                }

                if (!flagUsedDefaultPostcode) {
                    postcode = postcode.replace(' ', '').toUpperCase();
                    switch (postcode.length) {
                        case 5:
                            postcode = postcode.slice(0, 2) + " " + postcode.slice(2);
                            break;
                        case 6:
                            postcode = postcode.slice(0, 3) + " " + postcode.slice(3);
                            break;
                        case 7:
                            postcode = postcode.slice(0, 4) + " " + postcode.slice(4);
                            break;
                    }
                }

                this.getPostCode(postcode);
                document.getElementById('lockers-postcode').value = postcode;
                geoRequestData = [postcode, 'United Kingdom'];
                storageData = window.localStorage.getItem(geoRequestData.join(', '));
                window.localStorage.setItem('geoRequestData', geoRequestData.join(', '));

                if (storageData) {
                    storageData = JSON.parse(storageData);

                    location = storageData.location;
                    self.showLockerPopUp(location, storageData.response);
                } else {
                    fullScreenLoader.startLoader();
                    geoCoder.geocode({'address': geoRequestData.join(', ')}, function (results, status) {
                        if (status === 'OK') {
                            if (!results[0].formatted_address.includes(postcode) && !flagUsedDefaultPostcode) {
                                self.showLockersMap(element, event, self.defaultPostCode());
                                return;
                            }
                            if (results.length) {
                                location = results[0].geometry.location;
                                payload = {
                                    coordinate: {
                                        'latitude': location.lat(),
                                        'longitude': location.lng()
                                    }
                                };

                                self.getLockers(payload, geoRequestData, location);
                            } else {
                                console.error('Geocode returns empty result');
                            }
                        } else {
                            console.error('Geocode was not successful for the following reason: ' + status);
                            payload = {
                                coordinate: {
                                    'latitude': 51.5419891,
                                    'longitude': -0.1473598
                                }
                            };
                            self.getLockers(payload, ['London', 'United Kingdom'], location);
                        }
                    });
                }
            } catch (e) {
                console.error(e);
                fullScreenLoader.stopLoader();
            }
        },

        getLockers: function (payload, geoRequestData, locationCenter) {
            var self = this;
            storage.post(
                resourceUrlManager.getUrl({'default': self.searchMachineUrl}, {}),
                JSON.stringify(payload)
            ).done(
                function (response) {
                    fullScreenLoader.stopLoader();
                    window.localStorage.setItem('geoRequestData', geoRequestData.join(', '));
                    window.localStorage.setItem(geoRequestData.join(', '), JSON.stringify({
                        location: locationCenter,
                        response: response
                    }));

                    self.showLockerPopUp(location, response);
                    document.getElementById('lockers-postcode').value = geoRequestData[0];
                }
            ).fail(
                function (response) {
                    fullScreenLoader.stopLoader();
                    console.log('Error', response);
                }
            );
        }
    });
});