<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Api\Data;

interface MachineInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const MACHINE_ID = 'machine_id';
    const IDENTIFIER = 'identifier';
    const LATITUDE = 'latitude';
    const LONGITUDE = 'longitude';
    const ADDRESS = 'address';
    const LOCATION_DESCRIPTION = 'location_description';
    const STATUS = 'status';
    const TYPE = 'type';
    const PAYMENT_TYPE = 'payment_type';
    /**#@-*/

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return float|null
     */
    public function getLatitude();

    /**
     * @return float|null
     */
    public function getLongitude();

    /**
     * @return string|null
     */
    public function getAddress();

    /**
     * @return string|null
     */
    public function getLocationDescription();

    /**
     * @return int|null
     */
    public function getStatus();

    /**
     * @return int|null
     */
    public function getType();

    /**
     * @return int|null
     */
    public function getPaymentType();

    /**
     * @param int $machineId
     * @return MachineInterface
     */
    public function setId($machineId);

    /**
     * @param string $identifier
     * @return MachineInterface
     */
    public function setIdentifier($identifier);

    /**
     * @param float $latitude
     * @return MachineInterface
     */
    public function setLatitude($latitude);

    /**
     * @param float $longitude
     * @return MachineInterface
     */
    public function setLongitude($longitude);

    /**
     * @param string $address
     * @return MachineInterface
     */
    public function setAddress($address);

    /**
     * @param string $locationDescription
     * @return MachineInterface
     */
    public function setLocationDescription($locationDescription);

    /**
     * @param int $status
     * @return MachineInterface
     */
    public function setStatus($status);

    /**
     * @param int $type
     * @return MachineInterface
     */
    public function setType($type);

    /**
     * @param int $paymentType
     * @return MachineInterface
     */
    public function setPaymentType($paymentType);
}
