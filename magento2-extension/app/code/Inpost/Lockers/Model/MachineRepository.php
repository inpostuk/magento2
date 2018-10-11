<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model;

use Magento\Framework\Api\CriteriaInterface;
use Magento\Framework\DB\QueryBuilderFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Inpost\Lockers\Api\Data\MachineInterface;
use Inpost\Lockers\Api\Data\MachineSearchResultsInterface;
use Inpost\Lockers\Api\Data\MachineInterfaceFactory;
use Inpost\Lockers\Api\MachineRepositoryInterface;
use Inpost\Lockers\Model\ResourceModel\Machine as ResourceMachine;
use Inpost\Lockers\Model\ResourceModel\Machine\CollectionFactory;

/**
 * Class MachineRepository
 * @package Inpost\Lockers\Model
 */
class MachineRepository implements MachineRepositoryInterface
{
    /**
     * @var ResourceMachine
     */
    private $resource;

    /**
     * @var MachineInterfaceFactory
     */
    private $machineFactory;

    /**
     * @var CollectionFactory
     */
    private $machineCollectionFactory;

    /**
     * @var QueryBuilderFactory
     */
    private $queryBuilderFactory;

    /**
     * MachineRepository constructor
     *
     * @param ResourceMachine $resource
     * @param MachineInterfaceFactory $machineFactory
     * @param CollectionFactory $machineCollectionFactory
     * @param QueryBuilderFactory $queryBuilderFactory
     */
    public function __construct(
        ResourceMachine $resource,
        MachineInterfaceFactory $machineFactory,
        CollectionFactory $machineCollectionFactory,
        QueryBuilderFactory $queryBuilderFactory
    ) {
        $this->resource = $resource;
        $this->machineFactory = $machineFactory;
        $this->machineCollectionFactory = $machineCollectionFactory;
        $this->queryBuilderFactory = $queryBuilderFactory;
    }

    /**
     * Save machine
     *
     * @param MachineInterface $machine
     * @return MachineInterface
     * @throws CouldNotSaveException
     */
    public function save(MachineInterface $machine)
    {
        try {
            $this->resource->save($machine);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $machine;
    }

    /**
     * Retrieve machine
     *
     * @param int $machineId
     * @return MachineInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $machineId)
    {
        $machine = $this->machineFactory->create();
        $this->resource->load($machine, $machineId);

        if (!$machine->getId()) {
            throw new NoSuchEntityException(__('InPost machine with id "%1" does not exist.', $machineId));
        }

        return $machine;
    }

    /**
     * Retrieve machines matching the specified criteria
     *
     * @param CriteriaInterface $criteria
     * @return MachineSearchResultsInterface
     */
    public function getList(CriteriaInterface $criteria)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder->setCriteria($criteria);
        $queryBuilder->setResource($this->resource);
        $query = $queryBuilder->create();
        $collection = $this->machineCollectionFactory->create(['query' => $query]);

        return $collection;
    }
}
