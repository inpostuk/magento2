<?php

/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Adapter;

class Client
{
    const PRODUCTION_API_ENDPOINT = 'https://api-uk.easypack24.net/v4/';
    const SANDBOX_API_ENDPOINT = 'https://stage-api-uk.easypack24.net/v4/';
    const LABEL_SIZE_A4 = 'A4';
    const LABEL_SIZE_A6 = 'A6P';
    const LABEL_FILE_FORMAT_PDF = 'pdf';
    const LABEL_FILE_FORMAT_ZPL = 'zpl';

    const LABEL_PARAMS_MAPPING = [
        'pdf' => 'normal',
        'zpl' => 'a6p'
    ];

    private $token;
    /** @var \Zend\Http\Client $apiClient */
    private $apiClient;

    private $apiEndPoint = self::PRODUCTION_API_ENDPOINT;
    private $merchantEmail;

    /** @var  \Inpost\Lockers\Model\Parcel $parcel */
    private $parcel;
    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;
    /** @var \Inpost\Lockers\Logger\Logger */
    private $logger;
    /** @var \Inpost\Lockers\Helper\Lockers */
    private $helper;

    public function __construct(
        \Inpost\Lockers\Model\Parcel $parcel,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Inpost\Lockers\Helper\Lockers $helper,
        \Inpost\Lockers\Logger\Logger $logger
    ) {
    
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->parcel = $parcel;
        $this->helper = $helper;
        if ($endpoint = $this->helper->getApiEndPoint()) {
            $this->apiEndPoint = $endpoint;
        }
        /** @var \Zend\Http\Client apiClient */
        $this->apiClient = $this->objectManager->create('Zend\Http\Client', [$this->apiEndPoint, [
            'maxredirects' => 0,
            'timeout' => 300]
        ]);
    }

    public function getParcelData($parcelId, $jsonFormat = false)
    {
        $path = "/parcels/{$parcelId}";
        $response = $this->getFromEndpoint($path, [], $jsonFormat);
        if ($jsonFormat) {
            return $response;
        } else {
            return $response;
        }
    }

    private function getMerchantEmail()
    {
        if (!$this->merchantEmail) {
            $this->merchantEmail = $this->helper->getMerchantEmail();
        }

        return $this->merchantEmail;
    }

    private function getToken()
    {
        if (!$this->token) {
            $this->token = $this->helper->getApiToken();
        }

        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function setEndpoint($apiEndpoint)
    {
        $this->apiEndPoint = $apiEndpoint;
    }

    public function setMerchantEmail($merchantEmail)
    {
        $this->merchantEmail = $merchantEmail;
    }

    public function createParcel(
        $receiverPhone,
        $machineId,
        $size,
        $weight,
        $receiverEmail,
        $orderId = false,
        $jsonFormat = false
    ) {
    
        $path = "customers/{$this->getMerchantEmail()}/parcels";
        $receiverPhone = $this->parcel->preparePhone($receiverPhone);
        if (strlen($receiverPhone) == 10) {
            $params = [
                'receiver_phone' => $receiverPhone,
                'target_machine_id' => $machineId,
                'size' => strtoupper($size),
                'weight' => $weight,
                'receiver_email' => $receiverEmail
            ];

            if ($orderId) {
                $params['customer_reference'] = $orderId;
            }

            $response = $this->postOnEndpoint($path, $params);

            if ($jsonFormat) {
                return $response->getBody();
            }

            $body = (array)json_decode($response->getBody());

            $this->parcel->addData($body);

            return $this->parcel;
        } else {
            throw new \Magento\Framework\Exception\LocalizedException('Receiver number is not valid');
        }
    }

    public function getLabel($parcelId, $fileType = self::LABEL_FILE_FORMAT_PDF)
    {
        $path = "/parcels/{$parcelId}/sticker";
        $params = [
            'sticker_format' => ucfirst($fileType),
            'type' => self::LABEL_PARAMS_MAPPING[$fileType]
        ];
        $response = $this->getFromEndpoint($path, $params, true);
        return $response;
    }


    public function getMachinesList($jsonFormat = false)
    {
        $path = 'machines';
        $machineList = [];
        $response = $this->getFromEndpoint($path);
        if ($jsonFormat) {
            return $response;
        }
        if (is_object($response)) {
            if (property_exists($response, '_embedded')) {
                if (property_exists($response->_embedded, 'machines')) {
                    $machinesListJson = $response->_embedded->machines;
                    foreach ($machinesListJson as $machineArray) {
                        $object = $this->objectManager->create('\Inpost\Lockers\Model\Api\Machine');
                        $object->addData((array)$machineArray);
                        $machineList[] = $object;
                    }
                    return $machineList;
                }
            }
        }

        return $machineList;
    }

    private function getFromEndpoint($path, $params = [], $jsonFormat = false)
    {
        $request = $this->objectManager->create('Zend\Http\Request');
        $this->apiClient->resetParameters();

        $httpHeaders = $this->objectManager->create('Zend\Http\Headers');
        $httpHeaders->addHeaders([
            'Authorization' => 'Bearer ' . $this->getToken(),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);

        $request->setHeaders($httpHeaders);
        $request->setUri($this->apiEndPoint . $path);
        $request->setMethod(\Zend\Http\Request::METHOD_GET);


        $response = $this->apiClient->send($request);
        if ($response->getStatusCode()) {
            if ($jsonFormat) {
                return $response->getBody();
            }
            return json_decode($response->getBody());
        } else {
            $message = json_decode($response->getBody())->message;
            $this->logger->error($message);
            throw new \Exception($message);
        }
        return $response;
    }

    public function pay($parcelId, $jsonFormat = false)
    {
        $path = "/parcels/{$parcelId}/pay";
        $response = $this->postOnEndpoint($path);
        if ($jsonFormat) {
            return $response;
        }
    }

    private function postOnEndpoint($path, array $params = [])
    {
        if (array_key_exists('weight', $params) && $params['weight'] == 0) {
            unset($params['weight']);
        }
        /** @var \Zend\Http\Request $request */
        $request = $this->objectManager->create('Zend\Http\Request');
        $this->apiClient->resetParameters();

        /** @var \Zend\Http\Headers $httpHeaders */
        $httpHeaders = $this->objectManager->create('Zend\Http\Headers');
        $httpHeaders->addHeaders([
            'Authorization' => 'Bearer ' . $this->getToken(),
            'Content-Type' => 'application/json'
        ]);

        $request->setHeaders($httpHeaders);
        $request->setUri($this->apiEndPoint . $path);
        $request->setMethod(\Zend\Http\Request::METHOD_POST);
        $request->setContent(json_encode($params));

        if ($this->helper->isDebug()) {
            $this->logger->info("Request: {$request->getContent()}");
        }
        $response = $this->apiClient->send($request);
        if ($this->helper->isDebug()) {
            $this->logger->info("Response: {$response->getBody()}");
        }
        if ($response->isSuccess()) {
            return $response;
        } else {
            $response = (array)json_decode($response->getBody());
            if (array_key_exists('errors', $response)) {
                $message = $response['message'] . ': ' . implode(',', array_keys((array)$response['errors']));
                $this->logger->error($message);
                throw new \Magento\Framework\Exception\LocalizedException($message);
            }
            throw new \Magento\Framework\Exception\LocalizedException($response['message']);
        }
    }
}
