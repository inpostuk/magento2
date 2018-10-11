<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Api\Data;

interface CoordinateTransportInterface
{
    const LATITUDE  = 'latitude';
    const LONGITUDE = 'longitude';

    /**
     * @return float
     */
    public function getLatitude();

    /**
     * @return float
     */
    public function getLongitude();

    /**
     * @param float $latitude
     * @return CoordinateTransportInterface
     */
    public function setLatitude(float $latitude);

    /**
     * @param float $longitude
     * @return CoordinateTransportInterface
     */
    public function setLongitude(float $longitude);
}
