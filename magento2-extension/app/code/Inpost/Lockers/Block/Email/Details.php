<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Block\Email;

use Magento\Framework\View\Element\Template;
use Inpost\Lockers\Api\Checkout\AddressRepositoryInterface;

class Details extends Template
{
    /** @var \Magento\Quote\Model\QuoteFactory  */
    private $quoteFactory;
    /** @var AddressRepositoryInterface  */
    private $addressRepository;
    /** @var \Inpost\Lockers\Model\ResourceModel\Machine\DataCollection  */
    private $collection;

    public function __construct(
        Template\Context $context,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        AddressRepositoryInterface $addressRepository,
        \Inpost\Lockers\Model\ResourceModel\Machine\DataCollection $collection,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->quoteFactory = $quoteFactory;
        $this->collection = $collection;
        $this->addressRepository = $addressRepository;
    }

    public function isInpost()
    {
        if ($this->getOrder()->getShippingMethod() == 'inpost_inpost') {
            return true;
        }
        return false;
    }

    public function getLocker()
    {
        $quote = $this->quoteFactory->create()->loadByIdWithoutStore($this->getOrder()->getQuoteId());
        $quoteAddressId = $quote->getShippingAddress()->getId();
        if ($quoteAddressId) {
            $machineId = $this->addressRepository->getByQuoteAddressId($quoteAddressId)->getLockerMachine();
            $locker = $this->collection
                ->addFieldToFilter('id', $machineId)
                ->setPageSize(1, 1)
                ->getLastItem();
            ;
            return $locker;
        }
    }
}
