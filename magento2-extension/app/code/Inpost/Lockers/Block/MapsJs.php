<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Block;

use Magento\Framework\View\Element\Template;
use Inpost\Lockers\Helper\Data;

class MapsJs extends Template
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(Template\Context $context, Data $helper, array $data = [])
    {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getGoogleMapsApiUrl()
    {
        return $this->helper->getGoogleMapsApiUrl();
    }

    /**
     * @return string
     */
    public function getDefaultCountry()
    {
        return $this->helper->getDefaultCountry();
    }

    public function getInpostDescription()
    {
        return $this->helper->getDescription();
    }
}
