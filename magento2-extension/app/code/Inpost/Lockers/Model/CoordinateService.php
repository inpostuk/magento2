<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model;

use Magento\Framework\Api\CriteriaInterfaceFactory;
use Inpost\Lockers\Helper\Data;
use Inpost\Lockers\Api\CoordinateServiceInterface;
use Inpost\Lockers\Api\MachineRepositoryInterface;
use Inpost\Lockers\Api\Data\CoordinateTransportInterface;
use Inpost\Lockers\Api\Data\MachineSearchResultsInterface;
use Inpost\Lockers\Api\Data\MachineInterface;
use Inpost\Lockers\Model\ResourceModel\Machine\MachineCriteria;

/**
 * Class CoordinateService
 * @package Inpost\Lockers\Model
 */
class CoordinateService implements CoordinateServiceInterface
{
    /**
     * @var CriteriaInterfaceFactory
     */
    private $criteriaFactory;

    /**
     * @var MachineRepositoryInterface
     */
    private $machineRepository;

    /**
     * @var Data
     */
    private $helper;

    /**
     * CoordinateService constructor
     *
     * @param CriteriaInterfaceFactory $criteriaFactory
     * @param MachineRepositoryInterface $machineRepository
     * @param Data $helper
     */
    public function __construct(
        CriteriaInterfaceFactory $criteriaFactory,
        MachineRepositoryInterface $machineRepository,
        Data $helper
    ) {
        $this->criteriaFactory = $criteriaFactory;
        $this->machineRepository = $machineRepository;
        $this->helper = $helper;
    }

    /**
     * Retrieve nearest machines coordinates
     *
     * @param CoordinateTransportInterface $coordinate
     * @return array
     */
    public function findMachinesByCoordinate(CoordinateTransportInterface $coordinate)
    {
        /** @var MachineCriteria $criteria */
        $criteria = $this->criteriaFactory->create();
        $criteria->setNearestCoordinateFilter(
            [
                'latitude'  => $coordinate->getLatitude(),
                'longitude' => $coordinate->getLongitude()
            ],
            $this->helper->getMachineSearchRadius()
        );
        /** @var MachineSearchResultsInterface $result */
        $result = $this->machineRepository->getList($criteria);
        $data = [];
        /** @var MachineInterface $item */
        foreach ($result->getItems() as $item) {
            $data[] = [
                'id' => $item->getData('id'),
                'title' => $item->getAddress(),
                'coordinates' => [
                    'lat' => (float) $item->getLatitude(),
                    'lng' => (float) $item->getLongitude()
                ],
                'distance' => round($item->getDistance(), 1),
                'building_no' => preg_replace("|inpost locker-|i", "", $item->getBuildingNo()),
                'city' => $item->getCity(),
                'province' => $item->getProvince(),
                'post_code' => $item->getPostCode(),
                'street' => $item->getStreet(),
                'location_description' => $item->getLocationDescription()
            ];
        }

        return $data;
    }
}
