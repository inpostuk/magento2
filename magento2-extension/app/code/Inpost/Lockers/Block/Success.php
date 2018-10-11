<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Block;

class Success extends \Magento\Checkout\Block\Onepage\Success
{
    public function isInpost()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        if ($order->getShippingMethod() == 'inpost_inpost') {
            return true;
        }
        return false;
    }
}
