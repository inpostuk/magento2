<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Api;

use Inpost\Lockers\Api\Data\CoordinateTransportInterface;

interface CoordinateServiceInterface
{
    /**
     * Retrieve nearest machines coordinates
     *
     * @param CoordinateTransportInterface $coordinate
     * @return array
     */
    public function findMachinesByCoordinate(CoordinateTransportInterface $coordinate);
}
