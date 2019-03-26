<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class Inpost extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{

    const CODE = 'inpost';

    // @codingStandardsIgnoreStart
    protected $_code = 'inpost';
    // @codingStandardsIgnoreEnd

    /**
     * Available carrier method
     *
     * @var string
     */
    const METHOD = 'inpost';
    /** @var \Magento\Framework\App\ResponseFactory */
    private $responseFactory;
    /** @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory */
    private $rateMethodFactory;
    /** @var \Magento\Shipping\Model\Rate\ResultFactory */
    private $rateResultFactory;
    /** @var \Inpost\Lockers\Helper\Data */
    private $helper;
    /** @var \Inpost\Lockers\Helper\Lockers */
    private $configHelper;

    /**
     * Inpost constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Inpost\Lockers\Helper\Data $helper,
        \Inpost\Lockers\Helper\Lockers $configHelper,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        $this->helper = $helper;
        $this->rateResultFactory = $rateResultFactory;
        $this->responseFactory = $responseFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['inpost' => $this->getConfigData('name')];
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        /** @var \Magento\Shipping\Model\Rate\Result $res
         * \`ult
         */
        $result = $this->rateResultFactory->create();

        if ((!$this->configHelper->createLabelInMagento() && $this->configHelper->getGoogleMapsApiKey()) ||
            ($this->configHelper->createLabelInMagento() &&
                $this->configHelper->getGoogleMapsApiKey() &&
                $this->configHelper->getApiToken() &&
                $this->configHelper->getMerchantEmail())
        ) {
            if (!$this->getConfigFlag('active')) {
                return false;
            }

            $maxWeight = $this->helper->getMaxWeight();
            $totalWeight = 0;
            foreach ($request->getAllItems() as $item) {
                if (!$item->getParentItemId()) {
                    $totalWeight += $item->getWeight();
                }
            }

            if ($totalWeight <= $maxWeight) {

                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                $method = $this->rateMethodFactory->create();

                $method->setCarrier(self::CODE);
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod(self::CODE);
                $method->setMethodTitle($this->getConfigData('name'));

                $amount = $this->getConfigData('price');
                if ($request->getFreeShipping()) {
                    $amount = 0;
                }

                $method->setPrice($amount);
                $method->setCost($amount);

                $result->append($method);
            }
        }

        return $result;
    }

    public function isTrackingAvailable()
    {
        return true;
    }

    public function isShippingLabelsAvailable()
    {
        return true;
    }

    public function getTrackingInfo($number)
    {
        $this->responseFactory
            ->create()
            ->setRedirect("https://tracking.inpost.co.uk/?parcel_number={$number}")
            ->sendResponse();
    }
}
