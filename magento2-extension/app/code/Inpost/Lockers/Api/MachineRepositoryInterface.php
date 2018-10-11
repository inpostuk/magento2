<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Api;

use Magento\Framework\Api\CriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Inpost\Lockers\Api\Data\MachineInterface;
use Inpost\Lockers\Api\Data\MachineSearchResultsInterface;

interface MachineRepositoryInterface
{
    /**
     * Save machine
     *
     * @param MachineInterface $machine
     * @return MachineInterface
     * @throws LocalizedException
     */
    public function save(MachineInterface $machine);

    /**
     * Retrieve machine
     *
     * @param int $machineId
     * @return MachineInterface
     * @throws LocalizedException
     */
    public function getById(int $machineId);

    /**
     * Retrieve machines matching the specified criteria
     *
     * @param CriteriaInterface $criteria
     * @return MachineSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(CriteriaInterface $criteria);
}
