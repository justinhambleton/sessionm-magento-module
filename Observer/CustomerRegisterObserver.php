<?php
namespace Sessionm\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CustomerRegisterObserver implements ObserverInterface
{
    protected $curl;
    protected $scopeConfig;

    public function __construct(Curl $curl, ScopeConfigInterface $scopeConfig)
    {
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        $data = [
            'user' => [
                'external_id' => $customer->getId(),
                'external_id_type' => 'magento_customer',
                'email' => $customer->getEmail(),
                'first_name' => $customer->getFirstname(),
                'last_name' => $customer->getLastname(),
                'opted_in' => true,
                'dob' => $customer->getDob(),
                'address' => $customer->getDefaultBillingAddress() ? $customer->getDefaultBillingAddress()->getStreetLine(1) : '',
                'city' => $customer->getDefaultBillingAddress() ? $customer->getDefaultBillingAddress()->getCity() : '',
                'zip' => $customer->getDefaultBillingAddress() ? $customer->getDefaultBillingAddress()->getPostcode() : '',
                'state' => $customer->getDefaultBillingAddress() ? $customer->getDefaultBillingAddress()->getRegion() : '',
                'country' => $customer->getDefaultBillingAddress() ? $customer->getDefaultBillingAddress()->getCountryId() : '',
                'locale' => 'en-us',
                'user_profile' => [
                    'brand' => ['vrg', 'kr', 'ta', 'psg', 'gho', 'me']
                ]
            ]
        ];

        // Fetch API endpoint and credentials from config
        $host = $this->scopeConfig->getValue('sessionm/retail_host');
        $endpointTemplate = $this->scopeConfig->getValue('sessionm/customers_endpoint');
        $apiKey = $this->scopeConfig->getValue('sessionm/retail_api_key');
        $apiSecret = $this->scopeConfig->getValue('sessionm/retail_api_secret');
        $endpoint = str_replace('{sessionm_retail_api_key}', $apiKey, $endpointTemplate);

        // Base64 encode the API key and secret
        $authString = base64_encode("$apiKey:$apiSecret");

        $headers = [
            "Content-Type: application/json",
            "Authorization: Basic $authString"
        ];

        $this->curl->setHeaders($headers);
        $this->curl->post($url, json_encode($data));
        $response = $this->curl->getBody();

        // Handle the response if needed
        // ...
    }
}
