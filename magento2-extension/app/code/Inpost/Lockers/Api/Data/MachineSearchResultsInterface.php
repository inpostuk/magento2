<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Api\Data;

use Magento\Framework\Api\CriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

interface MachineSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get machines list
     *
     * @return MachineInterface[]
     */
    public function getItems();

    /**
     * Set machines list
     *
     * @param MachineInterface[] $items
     * @return MachineSearchResultsInterface
     */
    public function setItems(array $items = []);

    /**
     * Retrieve search criteria
     *
     * @return CriteriaInterface
     */
    public function getSearchCriteria();
}
