<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model\ResourceModel\Machine;

use Magento\Framework\Api\CriteriaInterface;
use Magento\Framework\Data\AbstractCriteria;

/**
 * Class MachineCriteria
 * @package Inpost\Lockers\Model\ResourceModel\Machine
 */
class MachineCriteria extends AbstractCriteria implements CriteriaInterface
{
    /**
     * MachineCriteria constructor
     *
     * @param string $mapper
     */
    public function __construct($mapper = MachineCriteriaMapper::class)
    {
        $this->mapperInterfaceName = $mapper;
    }

    /**
     * Set nearest machines filter by coordinate
     *
     * @param array $coordinate
     * @param float $distance
     * @return bool
     */
    public function setNearestCoordinateFilter(array $coordinate, float $distance)
    {
        $this->data['nearest_coordinate_filter'] = [$coordinate, $distance];
        return true;
    }
}
