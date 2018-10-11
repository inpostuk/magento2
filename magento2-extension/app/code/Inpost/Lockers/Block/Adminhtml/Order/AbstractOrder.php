<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Block\Adminhtml\Order;

use Inpost\Lockers\Api\Checkout\AddressRepositoryInterface;

class AbstractOrder extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{

    public $addressRepository;
    public $quoteFactory;
    public $machineCollection;
    public $locker;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        AddressRepositoryInterface $addressRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Inpost\Lockers\Model\ResourceModel\Machine\DataCollection $machineCollection,
        array $data = []
    ) {
    
        $this->_adminHelper = $adminHelper;
        $this->_coreRegistry = $registry;
        $this->quoteFactory = $quoteFactory;
        $this->machineCollection = $machineCollection;
        $this->addressRepository = $addressRepository;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    public function isShippingInpost()
    {
        $order = $this->getOrder();
        if ($order->getId()) {
            if ($order->getShippingMethod() == 'inpost_inpost') {
                $quote = $this->quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
                $quoteAddressId = $quote->getShippingAddress()->getId();
                if ($quoteAddressId) {
                    $lockerId = $this->addressRepository->getByQuoteAddressId($quoteAddressId)->getLockerMachine();
                    $locker = $this->machineCollection
                        ->addFieldToFilter('id', ['eq' => $lockerId])
                        ->setPageSize(1, 1)
                        ->getLastItem();
                    if ($locker->getId()) {
                        $this->locker = $locker;
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getLocker()
    {
        return $this->locker;
    }

    public function getLockerAddress()
    {
        if ($this->locker) {
            return sprintf(
                '%s, %s, %s, %s',
                $this->locker->getData('building_no'),
                $this->locker->getStreet(),
                $this->locker->getCity(),
                $this->locker->getPostCode()
            );
        }
        return '';
    }
}
