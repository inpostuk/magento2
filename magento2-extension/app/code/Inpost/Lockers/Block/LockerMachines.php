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
use Magento\Framework\Api\CriteriaInterfaceFactory;
use Inpost\Lockers\Api\MachineRepositoryInterface;
use Inpost\Lockers\Api\Data\MachineInterface;
use Inpost\Lockers\Api\Data\MachineSearchResultsInterface;

class LockerMachines extends Template
{
    /**
     * @var MachineRepositoryInterface
     */
    private $machineRepository;

    /**
     * @var CriteriaInterfaceFactory
     */
    private $criteriaFactory;

    /**
     * LockerMachines constructor
     *
     * @param Template\Context $context
     * @param MachineRepositoryInterface $machineRepository
     * @param CriteriaInterfaceFactory $scriteriaFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        MachineRepositoryInterface $machineRepository,
        CriteriaInterfaceFactory $criteriaFactory,
        array $data = []
    ) {
        $this->machineRepository = $machineRepository;
        $this->criteriaFactory = $criteriaFactory;

        parent::__construct($context, $data);
    }

    /**
     * @return MachineInterface[]
     */
    public function getMachines()
    {
        $criteria = $this->criteriaFactory->create();
        $criteria->setNearestCoordinateFilter(
            ['latitude' => '51.635110', 'longitude' => '-0.053000'],
            10
        );

        /** @var MachineSearchResultsInterface $result */
        $result = $this->machineRepository->getList($criteria);

        return $result->getItems();
    }
}
