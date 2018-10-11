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
    /** @var \Magento\Framework\App\ObjectManager */
    public $objectManager;

    /** @var \Inpost\Lockers\Helper\Lockers */
    public $helper;

    /** @var \Inpost\Lockers\Adapter\Client */
    private $client;

    /** @var \Inpost\Lockers\Model\ResourceModel\Machine\DataCollection */
    private $dataCollection;
    /** @var \Inpost\Lockers\Model\ResourceModel\Machine  */
    private $resource;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Inpost\Lockers\Helper\Lockers $helper,
        \Inpost\Lockers\Adapter\Client $client,
        \Inpost\Lockers\Model\ResourceModel\Machine\DataCollection $dataCollection,
        \Inpost\Lockers\Model\ResourceModel\Machine $resource
    )
    {
        $this->objectManager = $objectManager;
        $this->helper = $helper;
        $this->client = $client;
        $this->dataCollection = $dataCollection;
        $this->resource = $resource;
    }

    public function execute()
    {
        $this->client->setToken($this->helper->getApiToken());
        $machines = $this->client->getMachinesList();

        foreach ($machines as $machine) {
            if ($machine->getData('status') == 'Operating' && $machine->getData('id')) {
                $model = $this->dataCollection
                    ->clear()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('id', $machine['id'])
                    ->setPageSize(1)
                    ->setCurPage(1)
                    ->getLastItem();
                $attributesForUpdate = array();
                if ($model->getId()) {
                    foreach ($machine->getData() as $key => $value) {
                        if ($model->getData('key') !== $value) {
                            $attributesForUpdate[$key] = $value;
                        }
                    }
                } else {
                    $attributesForUpdate = $machine->getData();
                }

                $model->updateAttributes($attributesForUpdate);
            } else {
                $model = $this->dataCollection
                    ->clear()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('id', $machine['id'])
                    ->setPageSize(1)
                    ->setCurPage(1)
                    ->getLastItem();
                if ($model->getId()) {
                    $this->resource->removeMachineById($model);
                }
            }
        }
    }
}
