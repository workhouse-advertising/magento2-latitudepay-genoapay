<?php
/**
 * Created by PhpStorm.
 * User: brockie
 * Category: Latitude
 * Package: Payment
 * Date: 11/09/19
 * Time: 13:49 PM
 */

namespace Latitude\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class Curl
 *
 * Note this class is agnotic to both
 *
 * @package Latitude\Payment\Helper\Client
 */
class Curl extends AbstractHelper
{

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curlClient;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Latitude\Payment\Logger\Logger
     */
    protected $customLogger;
    /**
     * @var string
     */
    protected $authToken;

    /**
     * @var string
     */
    protected $expiryDate;

    /**
     * Curl constructor.
     * @param Config $configHelper
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param EncryptorInterface $encryptor
     * @param \Latitude\Payment\Logger\Logger $customLogger
     * @param Context $context
     */
    public function __construct(
        \Latitude\Payment\Helper\Config $configHelper,
        \Magento\Framework\HTTP\Client\Curl $curl,
        EncryptorInterface $encryptor,
        \Latitude\Payment\Logger\Logger $customLogger,
        Context $context
    )
    {
        $this->configHelper = $configHelper;
        $this->curlClient = $curl;
        $this->curlClient->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->encryptor = $encryptor;
        $this->customLogger = $customLogger;
        parent::__construct($context);
    }

    /**
     * @param int $storeId
     * @param string $methodCode
     * @return mixed Eg: {"authToken":"e3b7bf84-ee63-4b0f-85d2-e45e2e39ab6f","expiryDate":"2019-09-13T21:01:05+09:30"}
     * @throws \Exception
     */
    public function getToken($storeId = null,$methodCode= null)
    {
        try {
            $url = $this->configHelper->getEnvironment($storeId,$methodCode) . '/token';
            $username = $this->encryptor->decrypt($this->configHelper->getConfigData('client_key',$storeId,$methodCode)) ;
            $password = $this->encryptor->decrypt($this->configHelper->getConfigData('client_secret',$storeId,$methodCode));

            $options = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION =>CURL_HTTP_VERSION_1_1
            );
            $headers = [
                'Content-Type' => "application/com.latitudepay.ecom-v3.0+json",
                "Accept" => "application/com.latitudepay.ecom-v3.0+json",
                "Cache-Control" => "no-cache"
            ];
            $this->curlClient->setHeaders($headers);
            $this->curlClient->setOptions($options);
            $this->curlClient->setCredentials($username, $password);
            $this->curlClient->post($url, array());
            $response = $this->curlClient->getBody();
            if (!property_exists(json_decode($response), 'error')) {

                $responseJsonDecoded = json_decode($response);
                $this->authToken = $responseJsonDecoded->authToken;
                $this->expiryDate = $responseJsonDecoded->expiryDate;

                if ($this->configHelper->getConfigData('logging',$storeId,$methodCode)) {
                    $this->customLogger->info('Auth Token: ' . json_encode($responseJsonDecoded, true));
                }

                return $responseJsonDecoded;
            } else {
                return $responseJsonDecoded = $response;
            }


        } catch (\Exception $e) {
            //string(57) "Invalid response line returned from server: HTTP/2 200 "
            $message = $e->getMessage();
            $this->customLogger->log(\Psr\Log\LogLevel::DEBUG,$message, array('errors' => 'createEcommercePurchase'));
            throw new \Exception($e);
        }
    }
    /**
     * getConfiguration
     *
     * @return mixed
     */

    public function getConfiguration()
    {

        try {
            $url      = $this->configHelper->getEnvironment() . '/configuration';
            $username = $this->configHelper->getConfigData('client_key');
            $password = $this->configHelper->getConfigData('client_secret');
            $this->curlClient->addHeader("Content-Type", "application/json");
            $this->curlClient->setCredentials($username, $password);
            $this->curlClient->get($url);
            return $response = $this->curlClient->getBody();
        } catch (\Exception $e) {
            $this->customLogger->critical($e->getMessage());
        }
        return null;
    }

    /**
     * Strip out the json formatting, leaving only the name and values
     *
     * @param array $requestBody
     * @return mixed
     */
    public function stripJsonFromSalesString($requestBody)
    {
        $pattern = '/{"|":{"|","|":"|"},"|}],"|":|\[{"|"}}],"|}}|"}]"|},|,"|"}}|"}/';
        $replacement = '';

        $removeJsonFormatting = preg_replace($pattern, $replacement, $requestBody);
        $removeAllSpace = str_replace(' ', '', $removeJsonFormatting);
        $JSONStringWithoutFormatting = $removeAllSpace;

        return $JSONStringWithoutFormatting;
    }

    /**
     * Return a base 64 encoded string
     *
     * @param string $salesStringStripped
     * @return string
     */
    public function base64EncodeSalesString($salesStringStripped)
    {
        return base64_encode(str_replace(' ', '', $salesStringStripped));
    }

    /**
     * Get the signatiure hash for sending to latitude/GenoaPay
     *
     * @param string $salesStringStrippedBase64encoded
     * @param int $storeId
     * @param string $methodCode
     * @return string
     */
    public function getSignatureHash($salesStringStrippedBase64encoded,$storeId= null,$methodCode = null)
    {
        try {
            $clientSecret = $this->encryptor->decrypt($this->configHelper->getConfigData('client_secret',$storeId,$methodCode));
            return hash_hmac('sha256', str_replace(' ', '', $salesStringStrippedBase64encoded), $clientSecret);
        } catch (\Exception $e) {
            $this->customLogger->critical($e->getMessage());
        }
        return  null;
    }

    /**
     * Create an ecommerce pruchase
     *
     * @param array $requestBody
     * @param array $token
     * @param string $signatureHash
     * @return mixed
     * @throws \Exception
     */
    public function createEcommercePurchase($requestBody, $token, $signatureHash)
    {
        try {
            $url = $this->configHelper->getEnvironment() . '/sale/online?signature='.$signatureHash;
            $options =  array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTP_VERSION   =>CURL_HTTP_VERSION_1_1
            );
            $headers = [
                'Content-Type'       => "application/com.latitudepay.ecom-v3.0+json",
                "Accept"             => "application/com.latitudepay.ecom-v3.0+json",
                "Authorization"     => "Bearer ".$token->authToken,
                "Cache-Control"      => "no-cache"
            ];
            $this->curlClient->setHeaders($headers);
            $this->curlClient->setOptions($options);
            $this->curlClient->post($url,$requestBody);
            $output =  $this->curlClient->getBody();

            if ($this->configHelper->getConfigData('logging')) {
                $this->customLogger->info('createEcommercePurchase (REQUEST): ' . $requestBody);
                $this->customLogger->info('createEcommercePurchase (RESPONSE): ' . $output);
            }

            return $output;

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->customLogger->debug(print_r($message,true), array('errors' => 'createEcommercePurchase'));
            throw new \Exception($e);
        }

    }

    /**
     * Create an ecommerce pruchase
     * @param array $requestBody
     * @param array $token
     * @param int $storeId
     * @param string $methodCode
     * @return mixed
     * @throws \Exception
     */
    public function createRefund($requestBody, $token,$storeId,$methodCode)
    {
        try {
            $authToken = $this->getToken($storeId,$methodCode);
            $salesStringStripped = $this->stripJsonFromSalesString(trim(json_encode($requestBody, JSON_UNESCAPED_SLASHES)));
            $salesStringStrippedBase64encoded = $this->base64EncodeSalesString(trim($salesStringStripped));
            $signatureHash = $this->getSignatureHash(trim($salesStringStrippedBase64encoded),$storeId,$methodCode);
            $url = $this->configHelper->getEnvironment($storeId,$methodCode) . '/sale/'.$token.'/refund?signature='.$signatureHash;
            $options =  array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTP_VERSION   =>CURL_HTTP_VERSION_1_1
            );
            $headers = [
                'Content-Type'       => "application/com.latitudepay.ecom-v3.0+json",
                "Accept"             => "application/com.latitudepay.ecom-v3.0+json",
                "Authorization"      => "Bearer ".$authToken->authToken,
                "Cache-Control"      => "no-cache"
            ];
            $this->curlClient->setHeaders($headers);
            $this->curlClient->setOptions($options);
            $this->curlClient->post($url,json_encode($requestBody, JSON_UNESCAPED_SLASHES));
            $output =  $this->curlClient->getBody();
            if ($this->configHelper->getConfigData('logging')) {
                $this->customLogger->info('createRefund (REQUEST): ' . json_encode($requestBody, JSON_UNESCAPED_SLASHES));
                $this->customLogger->info('createRefund (RESPONSE): ' . $output);
            }

            return $output;

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->customLogger->info( 'createRefund(errors): ' . $message);
            throw new \Exception($e);
        }

    }

}
