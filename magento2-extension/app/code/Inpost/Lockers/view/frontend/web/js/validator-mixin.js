/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

define([
    'jquery',
    'moment',
    'Magento_Checkout/js/model/quote'
], function ($, moment, quote) {
    'use strict';

    return function (validator) {
        var message = 'Please specify a valid mobile phone number in format 077 1234 5678';

        validator.addRule(
            'phoneINPOST',
            function (value, params, additionalParams) {
                if (quote.shippingMethod()) {
                    if (quote.shippingMethod().carrier_code == 'inpost') {
                        return value.match(/^((((\+|00)?447\s?\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3})|(((\+|00)447\s?\d{2}|\(?07\d{2}\)?)\s?\d{3}\s?\d{4})|(((\+|00)447\s?\d{1}|\(?07\d{1}\)?)\s?\d{4}\s?\d{4}))(\s?\#(\d{4}|\d{3}))?$/);
                    }
                }
                return true;
            },
            $.mage.__(message)
        );

        return validator;
    };
});