<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Inpost\Lockers\Api\Data\CoordinateTransportInterface;

/**
 * Class CoordinateTransport
 * @package Inpost\Lockers\Model
 */
class CoordinateTransport extends AbstractExtensibleModel implements CoordinateTransportInterface
{
    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->getData(static::LATITUDE);
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->getData(static::LONGITUDE);
    }

    /**
     * @param float $latitude
     * @return CoordinateTransportInterface
     */
    public function setLatitude(float $latitude)
    {
        $this->setData(static::LATITUDE, $latitude);
        return $this;
    }

    /**
     * @param float $longitude
     * @return CoordinateTransportInterface
     */
    public function setLongitude(float $longitude)
    {
        $this->setData(static::LONGITUDE, $longitude);
        return $this;
    }
}
