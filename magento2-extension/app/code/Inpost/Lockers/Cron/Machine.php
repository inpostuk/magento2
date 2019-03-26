<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Cron;

class Machine
{
    /** @var \Inpost\Lockers\Helper\Lockers */
    public $helper;

    /** @var \Inpost_Api_Client  */
    private $client;

    /** @var \Inpost\Lockers\Model\ResourceModel\Machine\DataCollection */
    private $dataCollection;
    /** @var \Inpost\Lockers\Model\ResourceModel\Machine */
    private $resource;
    /** @var \Inpost\Lockers\Model\MachineFactory */
    private $machineFactory;

    private $apiClient;

    private $lockers = [];

    public function __construct(
        \Inpost\Lockers\Helper\Lockers $helper,
        \Inpost\Lockers\Model\ResourceModel\Machine\DataCollection $dataCollection,
        \Inpost\Lockers\Model\ResourceModel\Machine $resource,
        \Inpost\Lockers\Model\MachineFactory $machineFactory,
        \Inpost_Api_Client $apiClient
    ) {
        $this->machineFactory = $machineFactory;
        $this->helper = $helper;
        $this->client = $apiClient;
        $this->dataCollection = $dataCollection;
        $this->resource = $resource;
    }

    public function execute()
    {
        foreach ($this->dataCollection->getItems() as $locker) {
            $this->lockers[$locker->getData('id')] = [
                'locker' => $locker,
                'is_in_inpost' => false
            ];
        }

        $this->client->setToken($this->helper->getApiToken());
        $machines = $this->client->getMachinesList();

        foreach ($machines as $machine) {
            if (array_key_exists($machine->getData('id'), $this->lockers)) {
                $this->lockers[$machine->getData('id')]['is_in_inpost'] = true;
                $locker = $this->lockers[$machine->getData('id')]['locker'];
                if ($machine->getData('status') == 'Operating') {
                    $changedFlag = false;
                    foreach ($machine->getData() as $key => $value) {
                        if ($value !== $locker->getData($key)) {
                            $changedFlag = true;
                            $locker->setData($key, $value);
                        }
                    }
                    if ($changedFlag) {
                        $locker->updateAttributes();
                    }
                } else {
                    $this->resource->removeMachineById($locker);
                }
            } else {
                /** @var \Inpost\Lockers\Model\Machine $newMachine */
                $newMachine = $this->machineFactory->create();
                $newMachine->setData($machine->getData());
                $newMachine->updateAttributes();
            }
        }

        foreach ($this->lockers as $locker) {
            if (!$locker['is_in_inpost']) {
                $this->resource->removeMachineById($locker['locker']);
            }
        }
    }
}
