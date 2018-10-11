/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Inpost_Lockers/js/view/shipping/mixin': true
            },
            'Magento_Ui/js/lib/validation/validator': {
                'Inpost_Lockers/js/validator-mixin': true
            },
            'Magento_Paypal/order-review': {
                'Inpost_Lockers/js/paypal/order-review/mixin': true
            }
        }
    },
    map: {
        '*': {
            'Magento_Checkout/js/view/shipping-information/list': 'Inpost_Lockers/js/view/shipping-information/list'
        }
    }
};