<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Helper;

class Lockers
{
    public $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {

        $this->scopeConfig = $scopeConfig;
    }

    public function isActive()
    {
        return (bool)$this->scopeConfig->getValue(
            'carriers/inpost/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getApiToken()
    {
        return $this->scopeConfig->getValue(
            'carriers/inpost/api_token',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getGoogleMapsApiKey()
    {
        return $this->scopeConfig->getValue(
            'carriers/inpost/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getMerchantEmail()
    {
        return $this->scopeConfig->getValue(
            'carriers/inpost/merchant_email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getApiEndPoint()
    {
        return $this->scopeConfig->getValue(
            'carriers/inpost/endpoint',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getLabelsFormat()
    {
        return $this->scopeConfig->getValue(
            'carriers/inpost/labels',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isDebug()
    {
        return $this->scopeConfig->getValue(
            'carriers/inpost/debug',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getMetric()
    {
        return $this->scopeConfig->getValue(
            'carriers/inpost/weight',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function createLabelInMagento()
    {
        return $this->scopeConfig->getValue(
            'carriers/inpost/parcel_create',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
