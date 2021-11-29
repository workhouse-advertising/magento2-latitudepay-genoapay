<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Latitude\Payment\Model\Api;

use Magento\Payment\Model\Cart;
use Latitude\Payment\Logger\Logger;


/**
 * Lpay API wrappers model
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Lpay extends AbstractApi
{

     protected $purchaseResponse;
    /**
     * Global public interface map
     *
     * @var array
     */
    protected $_globalMap = [

        // commands
        'failUrl' => 'fail_url',
        'successUrl' => 'success_url',
        'callbackUrl' => 'callback_url',

    ];
    /**
     * SetLatitudeCheckout request map
     *
     * @var string[]
     */
    protected $setLatitudeCheckoutRequest = [
        'successUrl',
        'failUrl',
        'callbackUrl',

    ];
    /**
     * SetLatitudeCheckout request map
     *
     * @var string[]
     */
    protected $setShippingRequest = [
        Cart::AMOUNT_SHIPPING => 'SHIPPINGAMT'
    ];

    /**
     * SetLatitudeCheckout request map
     *
     * @var string[]
     */
    protected $setTaxRequest = [
        Cart::AMOUNT_TAX => 'TAXAMT'
    ];
    /**
     * SetExpressCheckout request map
     *
     * @var string[]
     */
    protected $setTotalAmountRequest = [
        Cart::AMOUNT_SUBTOTAL => 'ITEMAMT'
    ];
    /**
     * SetLatitudeCheckout response map
     *
     * @var string[]
     */
    protected $setLatitudeCheckoutResponse = ['TOKEN'];
    /**
     * Map for customer import/export
     *
     * @var array
     */
    protected $billingCustomerMap = [
        'mobileNumber' => 'telephone',
        'firstName' => 'firstname',
        'surname' => 'lastname'
    ];

    /**
     * Map for billing address import/export
     *
     * @var array
     */
    protected $billingAddressMap = [
        'addressLine1' => 'street',
        'suburb' => 'region',
        'cityTown' => 'city',
        'state' => 'region',
        'postcode' => 'postcode',
        'countryCode' => 'country_id', // iso-3166 two-character code

    ];

    /**
     * Map for billing address to do request (not response)
     * Merging with $_billingAddressMap
     *
     * @var array
     */
    protected $billingAddressMapRequest = [];

    /**
     * Map for shipping address import/export (extends billing address mapper)
     * @var array
     */
    protected $shippingAddressMap = [
        'addressLine1' => 'street',
        'suburb' => 'region',
        'cityTown' => 'city',
        'STATE' => 'region',
        'state' => 'region',
        'postcode' => 'postcode',
        'countryCode' => 'country_id',
        // 'SHIPTONAME' will be treated manually in address import/export methods
    ];

    /**
     * @var \Latitude\Payment\Helper\Curl
     */
    protected $curlHelper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    /**
     * @param \Magento\Customer\Helper\Address $customerAddress
     * @param Logger $logger
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Directory\Model\RegionFactory $regionFactory,
     * @param \Latitude\Payment\Helper\Curl $curlHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Cart $cart
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *  /*@noinspection PhpUndefinedMethodInspection
     */
    public function __construct(
        \Magento\Customer\Helper\Address $customerAddress,
        Logger $logger,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Latitude\Payment\Helper\Curl $curlHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Cart $cart,
        array $data = []
    ) {
        parent::__construct($customerAddress, $logger, $localeResolver, $regionFactory, $checkoutSession,$data);
        $this->curlHelper      = $curlHelper;
        $this->messageManager   = $messageManager;
        $this->cart  = $cart;
    }

    /**
     * callLatitudePayCheckout call
     **
     * @param array $token
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function callLatitudePayCheckout($token)
    {
        $request = '';
        $response= '';
        /** @noinspection PhpUndefinedMethodInspection */
        // phpstan-ignore-next-line
        if ($this->getAddress()) {
           $request = $this->_prepareLatitudeCallRequest();
        }

        $salesStringStripped              = $this->curlHelper->stripJsonFromSalesString(json_encode($request, JSON_UNESCAPED_SLASHES));
        $salesStringStrippedBase64encoded = $this->curlHelper->base64EncodeSalesString(trim($salesStringStripped));
        $signatureHash                    = $this->curlHelper->getSignatureHash(trim($salesStringStrippedBase64encoded));
        try {
            $payload = json_encode($request,JSON_UNESCAPED_SLASHES);
            $response                     =  $this->curlHelper->createEcommercePurchase($payload, $token, $signatureHash);
            $this->saveSessionTotalAmount($request, $token, $signatureHash);
        } catch (\Exception $e) {
             $this->logger->critical($e->getMessage());
        }

        $this->purchaseResponse =  $response;

        $this->_importFromResponse($this->setLatitudeCheckoutResponse, json_decode($response, JSON_UNESCAPED_SLASHES));
    }

    /**
     * LatitudePayCheckout call
     * @return array

     */
    public function getToken()
    {
        $token = null;
        try {
            $token       =   $this->curlHelper->getToken();             ;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return  $token;
    }

    /**
     * LatitudePayCheckout call
     *
     * @return array
     */
    public function getRedirectUrl()
    {
        return  json_decode($this->purchaseResponse, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Prepare request data basing on provided addresses
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @noinspection PhpUndefinedMethodInspection
     */

    protected function _prepareLatitudeCallRequest()
    {
        $to = [];
        /** @noinspection PhpUndefinedMethodInspection */
        // phpstan-ignore-next-line
        $billingAddress = $this->getBillingAddress() ? $this->getBillingAddress() : $this->getAddress();
        /** @noinspection PhpUndefinedMethodInspection */
        $shippingAddress = $this->getAddress();

        $to['customer'] = \Magento\Framework\DataObject\Mapper::accumulateByMap(
            $billingAddress,
            $to,
            array_merge(array_flip($this->billingCustomerMap), $this->billingAddressMapRequest)
        );
        /** @noinspection PhpUndefinedMethodInspection */
        // phpstan-ignore-next-line
        $to['customer']['email'] = $this->getBillingAddress()->getEmail();
        $to['customer']['dateOfBirth'] = '0000-10-10';
        $address = [];
        $to['customer']['address'] = \Magento\Framework\DataObject\Mapper::accumulateByMap(
            $billingAddress,
            $address,
            array_merge(array_flip($this->billingAddressMap), $this->billingAddressMapRequest)
        );
        $shipping = [];
        $to['shippingAddress'] = \Magento\Framework\DataObject\Mapper::accumulateByMap(
            $shippingAddress,
            $shipping,
            array_flip($this->shippingAddressMap)
        );
        $billing = [];
        $to['billingAddress'] = \Magento\Framework\DataObject\Mapper::accumulateByMap(
            $billingAddress,
            $billing,
            array_merge(array_flip($this->billingAddressMap), $this->billingAddressMapRequest)
        );
        /** @noinspection PhpUndefinedMethodInspection */
        // phpstan-ignore-next-line
        $items   = $this->getQuote()->getAllVisibleItems();
        $products = array();
        foreach ($items as $key => $value) {
            /** @noinspection PhpUndefinedMethodInspection */
            // phpstan-ignore-next-line
            $products[] = array(
                "name" => $value->getName(),
                "price" => array(
                    "amount" => $value->getPriceInclTax(),
                    "currency" => $this->config->getCurrentCurrencyCode()
                ),
                "sku" => $value->getSku(),
                "quantity" => $value->getQty(),
                "taxIncluded" => false
            );
        }
        $to['products']       =  $products;
        $to['shippingLines']  =   array($this->getShippingDetails());
        $to['taxAmount']      =  $this->_exportTotal($this->setTaxRequest);
        /** @noinspection PhpUndefinedMethodInspection */
        $to['reference']      =  $this->getQuote()->getReservedOrderId();
        $to['totalAmount']    =  $this->_exportTotal($this->setTotalAmountRequest);
        $to['returnUrls']     =  $this->_exportToRequest($this->setLatitudeCheckoutRequest);
        return $to;
    }

    protected function saveSessionTotalAmount($payload, $token, $signatureHash)
    {
        $totalAmount = $payload['totalAmount']['amount'];
        $currency = $payload['totalAmount']['currency'];
        $requestHash = sha1(implode('||',[$totalAmount,$currency]));
        $this->checkoutSession->setLatitudeTotalAmount($requestHash);
    }

    public function validateTotalAmount($token,$signature)
    {
        $totalAmount = $this->formatPrice($this->cart->getQuote()->getBaseGrandTotal());
        $currency = $this->cart->getQuote()->getBaseCurrencyCode();
        $requestHash = sha1(implode('||',[$totalAmount,$currency]));
        if($requestHash !== $this->checkoutSession->getLatitudeTotalAmount()){
            $this->checkoutSession->unsLatitudeTotalAmount();
            return false;
        }
        $this->checkoutSession->unsLatitudeTotalAmount();
        return true;
    }

    public function validatePayload($payload)
    {
        if($payload['reference'] !== $this->cart->getQuote()->getReservedOrderId()) {
            throw new  \Magento\Framework\Exception\LocalizedException(__('Invalid Token'));
        }
        return true;
    }
}
