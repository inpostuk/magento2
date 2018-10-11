<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model\ResourceModel\Machine;

use Magento\Framework\Data\AbstractSearchResult;
use Inpost\Lockers\Api\Data\MachineSearchResultsInterface;
use Inpost\Lockers\Api\Data\MachineInterface;

/**
 * Class Collection
 * @package Inpost\Lockers\Model\ResourceModel\Machine
 */
class Collection extends AbstractSearchResult implements MachineSearchResultsInterface
{
    /**
     * @var string
     */
    protected $eventPrefix = 'inpost_machine_collection';

    /**
     * @var string
     */
    protected $eventObject = 'machine_collection';

    /**
     * Set Data Interface name for collection items
     */
    protected function init()
    {
        $this->setDataInterfaceName(MachineInterface::class);
    }
}
