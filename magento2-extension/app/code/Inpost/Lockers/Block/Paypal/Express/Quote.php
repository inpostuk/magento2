<?php

namespace Inpost\Lockers\Block\Paypal\Express;

use Magento\Framework\View\Element\Template;

class Quote extends Template
{
    private $configProvider;

    /** @var \Magento\Checkout\Helper\Data */
    private $checkoutHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Magento\Checkout\Helper\Data $checkoutHelper
    ) {
    
        parent::__construct($context);
        $this->configProvider = $configProvider;
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * Retrieve checkout configuration
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * @return bool|string
     * @since 100.2.0
     */
    public function getSerializedCheckoutConfig()
    {
        return json_encode($this->getCheckoutConfig(), JSON_HEX_TAG);
    }

    public function getSerializedShippingAddressData()
    {
        $quote = $this->checkoutHelper->getCheckout()->getQuote();
        $shippingAddressData = $quote->getShippingAddress()->getData();
        return json_encode($shippingAddressData, JSON_HEX_TAG);
    }
}
