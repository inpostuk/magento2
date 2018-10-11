<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Inpost\Lockers\Model\Carrier\Inpost as Carrier;

/**
 * Class Data
 * @package MageToolkit\InPost\Helper
 */
class Data extends AbstractHelper
{
    const GOOGLE_MAPS_API_URL = 'https://maps.googleapis.com/maps/api/js';
    const XML_PATH_GOOGLE_MAPS_API_KEY = 'carriers/inpost/api_key';
    const XML_PATH_MACHINE_SEARCH_RADIUS = 'carriers/inpost/map_radius';
    const XML_PATH_API_ENDPOINT_URL = 'carriers/inpost/endpoint';
    const XML_PATH_DESCRIPTION = 'carriers/inpost/description';

    /**
     * @return int
     */
    public function getMachineSearchRadius()
    {
        return (float)$this->scopeConfig->getValue(
            static::XML_PATH_MACHINE_SEARCH_RADIUS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getMethodCode()
    {
        return Carrier::CODE;
    }

    /**
     * @return string
     */
    public function getGoogleMapsApiUrl()
    {
        $apiKey = trim($this->scopeConfig
            ->getValue(static::XML_PATH_GOOGLE_MAPS_API_KEY, ScopeInterface::SCOPE_STORE));

        return self::GOOGLE_MAPS_API_URL . '?key=' . $apiKey;
    }

    public function getDefaultCountry()
    {
        return trim($this->scopeConfig->getValue('general/country/default'));
    }

    /**
     * @return string
     */
    public function getGatewayUrl()
    {
        return trim($this->scopeConfig->getValue(static::XML_PATH_API_ENDPOINT_URL));
    }

    public function getDescription()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_DESCRIPTION);
    }
}
