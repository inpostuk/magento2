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
    'mage/translate',
    'mage/storage',
    'googleMaps',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/resource-url-manager',
    'MarkerClusterer'
], function (
    $,
    Component,
    ko,
    quote,
    $t,
    storage,
    googleMaps,
    createShippingAddress,
    modal,
    resourceUrlManager
) {
    'use strict';

    var lockersCount = 5;
    var initLocation = '';
    var lockers = [];
    var self = false;

    return Component.extend({
        isPopUpVisible: ko.observable(false),
        lockers: ko.observableArray([]),
        lockersCount: ko.observableArray([]),
        activeItem: ko.observable(),
        showMoreAllowed: ko.observable(true),
        isMobile: ko.observable(false),
        defaultPostCode: ko.observable('London'),
        lockerDescriptionVisible: ko.observable(false),
        selectedLockerAddress: ko.observable(''),

        setModalElement: ko.observable(),
        isLoading: ko.observable(),
        registerUrl: ko.observable(),
        login: ko.observable(),
        autocomplete: ko.observable(),
        forgotPasswordUrl: ko.observable(),

        defaults: {
            template: 'Inpost_Lockers/paypal/express/map'
        },

        /**
         * @return {exports}
         */
        initialize: function () {
            var address = createShippingAddress(window.shippingAddressData);
            quote.shippingAddress(address);
            self = this;
            self._super();
            self.checkLockerDescriptionVisibility();
            self.emptySelectedLocker();
            if ($('#shipping-method').val() == 'inpost_inpost') {
                $('#review-button').attr('disabled', 'disabled').addClass('no-checkout').css('opacity', '0.5');
            }
            self.showLockersMap();
            return self;
        },

        showFindOut: function () {
            $('#inpost-overlay').show();
            $('#inpost-popup').show();
        },

        checkLockerDescriptionVisibility: function () {
            var self = this;
            if (document.getElementById('shipping-method').value == 'inpost_inpost') {
                self.lockerDescriptionVisible(true);
                return;
            }
            self.lockerDescriptionVisible(false);
        },

        resetSelectedLocker: function () {
            var self = this,
                address = quote.shippingAddress();
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
                            return;
                        }
                    });
                }
            }
        },

        emptySelectedLocker: function () {
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

            address.extensionAttributes.lockerMachine = false;
            window.localStorage.removeItem('selected_locker');
            self.selectedLockerAddress('');
        },

        showPopup: function () {
            var self = this;
            self.isPopUpVisible(true);
            self.checkLockerDescriptionVisibility();
        },

        closePopup: function () {
            var self = this;
            self.isPopUpVisible(false);
        },

        findPostCodeKeypress: function (element, event) {
            if (event.keyCode == 13) {
                var self = this;
                self.findPostcode(false, event);
            }
            return true;
        },

        setLocker: function (locker) {
            window.localStorage.removeItem('selected_locker');
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
            window.localStorage.setItem('selected_locker', JSON.stringify(locker));
            $('.block-order-details-view address').html(
                address.firstname + '<br>' + locker.street + '<br>' + locker.city + ', ' + this.post_code + '<br>' + 'United Kingdom' + '<br>'
            );
            self.resetSelectedLocker();
            $('#closePopup').trigger('click');
            $('#shipping-method').trigger('change');
        },

        precisionRound: function (number, precision) {
            var factor = Math.pow(10, precision);
            return Math.round(number * factor) / factor;
        },

        reloadMap: function () {
            var self = this,
                infoWindow = new googleMaps.InfoWindow(),
                marker;
            if (!initLocation) {
                initLocation = window.initLocation;
            }
            if (initLocation) {
                if (initLocation.lat < 38) {
                    self.findPostcode(false, false);
                    return;
                }

                var zoom = 10;
                if (lockers.length > 0) {
                    zoom = Math.round(14 - Math.log(lockers.slice(0, lockersCount)[lockers.length - 1].distance) / Math.LN2);
                }

                var map = new googleMaps.Map($('#map')[0], {
                    zoom: zoom,
                    mapTypeId: googleMaps.MapTypeId.ROADMAP,
                    center: initLocation
                });
                var icon = {
                    url: window.mapIconPath, // url
                    scaledSize: new google.maps.Size(22, 40), // scaled size
                    origin: new google.maps.Point(0, -7), // origin
                    anchor: new google.maps.Point(0, 0) // anchor
                };

                var i = 0, markers = [];
                $.each(lockers.slice(0, lockersCount), function (index, locker) {
                    marker = new googleMaps.Marker({
                        position: locker.coordinates,
                        map: map,
                        title: locker.building_no,
                        label: String(i + 1),
                        icon: icon
                    });
                    markers.push(marker);

                    var contentString = "<div class='info-window'>" +
                        "<div class='title'>" +
                        "<h5 class='info-window-name'>" + (i + 1) + ' - ' + locker.building_no + "</h5>" +
                        "<p class='right'>" + self.precisionRound(locker.distance, 2) + " miles</p>" +
                        "</div>" +
                        "<div class='info-window-address'><p>" + locker.province + "</p><p>" + locker.city + ", " + locker.post_code + "</p></div>" +
                        "<button row-id=" + locker.id + " class='button'>Select</button>" +
                        "</div>";

                    /*$('.point[data-id='+ locker.id +'] .select').trigger('click')*/
                    var infoWindow = new googleMaps.InfoWindow({
                        content: contentString,
                        maxWidth: 250
                    });

                    googleMaps.event.addListener(marker, 'click', (function (marker) {
                        return function () {
                            if (window.infowindow) {
                                window.infowindow.close();
                            }
                            $('.point').removeClass('active');
                            $('.point[data-id=' + locker.id + ']').addClass('active');
                            infoWindow.open(map, marker);
                            map.setCenter(marker.getPosition());
                            $('.info-window button').on('click', function () {
                                $('.point[data-id=' + $(this).attr('row-id') + '] button.select').trigger('click');
                            });
                            window.infowindow = infoWindow;
                        }
                    })(marker));
                    i++;
                });

                var centerIcon = {
                    url: window.centerIcon
                };

                var centerMarker = new google.maps.Marker({
                    position: initLocation,
                    icon: centerIcon
                });

                markers.push(centerMarker);

                var markerCluster = new MarkerClusterer(
                    map,
                    markers,
                    {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'}
                );
            }
            google.maps.event.trigger(map, 'resize')
        },

        findPostcode: function (element, event) {
            var self = this,
                shippingAddress = quote.shippingAddress(),
                geoCoder = new googleMaps.Geocoder(),
                geoRequestData,
                location,
                payload,
                storageData;
            if (event) {
                event.stopPropagation();
            }
            $('#load-overlay').show();
            lockersCount = 5;
            var postCodeInputValue = document.getElementById('lockers-postcode').value;
            if (postCodeInputValue != '' && postCodeInputValue != self.defaultPostCode()) {
                var country = window.defaultCountry;
                if (shippingAddress.postcode != null) {
                    country = shippingAddress.countryId;
                }
                geoRequestData = [postCodeInputValue, country];
            } else {
                geoRequestData = [self.defaultPostCode(), 'UK'];
            }
            geoCoder.geocode({'address': geoRequestData.join(', ')}, function (results, status) {
                if (status === 'OK') {
                    if (results.length) {
                        location = results[0].geometry.location;
                        payload = {
                            coordinate: {
                                'latitude': location.lat(),
                                'longitude': location.lng()
                            }
                        };

                        storage.post(
                            resourceUrlManager.getUrl({'default': '/inpost/find-machines-by-coordinate'}, {}),
                            JSON.stringify(payload)
                        ).done(
                            function (response) {
                                window.localStorage.setItem(geoRequestData.join(', '), JSON.stringify({
                                    location: location,
                                    response: response
                                }));
                                window.localStorage.setItem('geoRequestData', geoRequestData.join(', '));
                                initLocation = location;
                                lockers = response;
                                self.reloadLeft();
                                self.reloadMap();
                                $('#load-overlay').hide();
                            }
                        ).fail(
                            function (response) {
                                console.log('Error', response);
                                $('#load-overlay').hide();
                            }
                        );
                    } else {
                        console.error('Geocode returns empty result');
                        $('#load-overlay').hide();
                    }
                } else {
                    console.error('Geocode was not successful for the following reason: ' + status);
                    $('#load-overlay').hide();
                }
            });
        },

        reloadLeft: function () {
            var self = this;
            var geoRequestData = window.localStorage.getItem('geoRequestData');
            var lockerFromCache = window.localStorage.getItem(geoRequestData);
            if (lockerFromCache) {
                lockerFromCache = JSON.parse(lockerFromCache);
                if (lockersCount >= lockerFromCache.response.length) {
                    self.showMoreAllowed(false);
                } else {
                    self.showMoreAllowed(true);
                }
                if ($(window).width() < 750) {
                    self.isMobile(true);
                } else {
                    self.isMobile(false);
                }
                initLocation = lockerFromCache.location;
                lockers = lockerFromCache.response.slice(0, lockersCount);
                self.lockers(lockerFromCache.response.slice(0, lockersCount));
                self.lockersCount(lockerFromCache.response.length);
            }
        },

        getPostCode: function () {
            var shippingAddress = quote.shippingAddress();
            return shippingAddress.postcode;
        },

        reloadPopup: function () {
            var self = this;
            lockersCount = 5;
            self.reloadLeft();
            self.reloadMap();
        },

        showMore: function () {
            var self = this;
            lockersCount = lockersCount + 5;
            self.reloadLeft();
            self.reloadMap();
        },

        toggleAccessible: function (elementId) {
            $('.point[data-id="' + elementId + '"] .hours').toggleClass('active');
        },

        imageExists: function (elementId) {
            $.ajax({
                url: 'https://geowidget.easypack24.net/uploads/uk/images/' + elementId + '.jpg',
                type: 'HEAD',
                crossDomain: true,
                success: function () {
                    return true;
                },
                error: function (data, textStatus, xhr) {
                    return false
                }
            });
        },

        getLoaderImage: function () {
            return window.loadIcon;
        },

        getMobileShowMoreImage: function () {
            return window.showMoreIcon;
        },

        showLockersMap: function () {
            var self = this,
                shippingAddress = quote.shippingAddress(),
                geoCoder = new googleMaps.Geocoder(),
                geoRequestData,
                location,
                payload,
                storageData;

            try {
                var address = shippingAddress;
                if (quote.billingAddress() != null) {
                    address = quote.billingAddress()
                }
                this.getPostCode(address.postcode);
                geoRequestData = [address.postcode, address.countryId];
                storageData = window.localStorage.getItem(geoRequestData.join(', '));
                window.localStorage.setItem('geoRequestData', geoRequestData.join(', '));

                if (storageData) {
                    storageData = JSON.parse(storageData);
                    location = storageData.location;
                } else {
                    geoCoder.geocode({'address': geoRequestData.join(', ')}, function (results, status) {
                        if (status === 'OK') {
                            if (results.length) {
                                location = results[0].geometry.location;
                                payload = {
                                    coordinate: {
                                        'latitude': location.lat(),
                                        'longitude': location.lng()
                                    }
                                };

                                storage.post(
                                    resourceUrlManager.getUrl({'default': '/inpost/find-machines-by-coordinate'}, {}),
                                    JSON.stringify(payload)
                                ).done(
                                    function (response) {
                                        window.localStorage.setItem(geoRequestData.join(', '), JSON.stringify({
                                            location: location,
                                            response: response
                                        }));
                                        self.reloadLeft();
                                        self.reloadMap();
                                    }
                                ).fail(
                                    function (response) {
                                        console.log('Error', response);
                                    }
                                );
                            } else {
                                console.error('Geocode returns empty result');
                            }
                        } else {
                            console.error('Geocode was not successful for the following reason: ' + status);
                        }
                    });
                }
            } catch (e) {
                console.error(e);
            }
        }
    });
});