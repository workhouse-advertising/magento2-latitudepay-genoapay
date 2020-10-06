<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Latitude\Payment\Plugin;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Store\Model\ScopeInterface;
/**
 * Directory data processor.
 *
 * This class adds various country and region dictionaries to checkout page.
 * This data can be used by other UI components during checkout flow.
 */
class DirectoryDataProcessor
{

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    private $countryCollectionFactory;
    /**
     * @var array
     */
    private $countryOptions;
    /**
     * @var \Magento\Directory\Model\AllowedCountries
     */
    protected $allowedCountryModel;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Payment method code
     *
     * @var string
     */
    protected $code;

    /**
     * Available Countries Code
     *
     * @var string
     */
    protected $availableCountries;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;
    /**
     * @var string
     */
    protected $currentCurrencyCode;
    /**
     * @var string
     */
    protected $latitudepayCurrency;
    /**
     * @var string
     */
    protected $genoapayCurrency;

    /**
     * @var array
     */
    protected $supportedCurrencyCodes;


    CONST LATITUDE_CURRENCY  = "payment/latitudepay/currency";
    CONST GENOAPAY_CURRENCY  = "payment/genoapay/currency";
    CONST DISPLAY_BILLING_ADDRESS_ON_CONFIG_PATH = 'checkout/options/display_billing_address_on';

    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollection
     * @param \Magento\Directory\Model\AllowedCountries $allowedCountryModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param DirectoryHelper $directoryHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Latitude\Payment\Helper\Config $configHelper
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollection,
        \Magento\Directory\Model\AllowedCountries $allowedCountryModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DirectoryHelper $directoryHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Latitude\Payment\Helper\Config $configHelper
    )
    {
        $this->countryCollectionFactory     = $countryCollection;
        $this->allowedCountryModel          = $allowedCountryModel;
        $this->storeManager                 = $storeManager;
        $this->directoryHelper              = $directoryHelper;
        $this->scopeConfig                  = $scopeConfig;
        $this->currentCurrencyCode          =  $this->storeManager->getStore()->getCurrentCurrencyCode();
        $this->latitudepayCurrency          =  $this->scopeConfig->getValue(self::LATITUDE_CURRENCY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->genoapayCurrency             =  $this->scopeConfig->getValue(self::GENOAPAY_CURRENCY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->supportedCurrencyCodes       =  array($this->latitudepayCurrency=>'latitudepay',$this->genoapayCurrency=>'genoapay');
        $this->checkoutSession              =   $checkoutSession;
        $this->configHelper                 = $configHelper;


        if (array_key_exists($this->currentCurrencyCode, $this->supportedCurrencyCodes)) {
            $this->code = $this->supportedCurrencyCodes[$this->currentCurrencyCode];
        }
        if( $this->code == 'latitudepay') {
            $this->availableCountries = trim(
                $this->scopeConfig->getValue(
                    'payment/latitudepay/specificcountry',
                    ScopeInterface::SCOPE_STORE
                )
            );
        }




        if( $this->code == 'genoapay') {
            $this->availableCountries = trim(
                $this->scopeConfig->getValue(
                    'payment/genoapay/specificcountry',
                    ScopeInterface::SCOPE_STORE
                )
            );
        }


    }

    /**
     * @param \Magento\Checkout\Block\Checkout\DirectoryDataProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    // @codingStandardsIgnoreStart
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\DirectoryDataProcessor $subject,
        array $jsLayout
    )
    {
        // @codingStandardsIgnoreEnd
        if(isset($jsLayout['components']['checkoutProvider']['dictionaries'])) {

        $jsLayout['components']['checkoutProvider']['dictionaries']['billing_country_id'] = $this->getCountryOptions();
        if( $this->code == 'genoapay'){
            $billingGenoapayConfiguration = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['payments-list'];

            if(isset($billingGenoapayConfiguration['children']['genoapay-form'])) {

                if ($billingGenoapayConfiguration['children']['genoapay-form']['component'] == 'Magento_Checkout/js/view/billing-address' && $billingGenoapayConfiguration['children']['genoapay-form']['dataScopePrefix'] == 'billingAddressgenoapay') {

                        $billingGenoapayConfiguration['children']['genoapay-form']['children']['form-fields']['children']['country_id']['imports'] = [
                            'initialOptions' => 'index = ' . 'checkoutProvider' . ':dictionaries.' . 'billing_country_id',
                            'setOptions' => 'index = ' . 'checkoutProvider' . ':dictionaries.' . 'billing_country_id'
                        ];
                    }
                }
            }

            if( $this->code == 'latitudepay'){
                $billingPaymentsListConfiguration = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                ['children']['payment']['children']['payments-list'];
                if(isset($billingPaymentsListConfiguration['children']['latitudepay-form'])) {
                    if ($billingPaymentsListConfiguration['children']['latitudepay-form']['component'] == 'Magento_Checkout/js/view/billing-address' && $billingPaymentsListConfiguration['children']['latitudepay-form']['dataScopePrefix'] == 'billingAddresslatitudepay') {
                        $billingPaymentsListConfiguration['children']['latitudepay-form']['children']['form-fields']['children']['country_id']['imports'] = [
                            'initialOptions' => 'index = ' . 'checkoutProvider' . ':dictionaries.' . 'billing_country_id',
                            'setOptions' => 'index = ' . 'checkoutProvider' . ':dictionaries.' . 'billing_country_id'
                        ];

                    }
                }
            }
            $isDisplayBillingOnPaymentPageAvailable = $this->configHelper->isDisplayBillingOnPaymentPageAvailable();

            if ($isDisplayBillingOnPaymentPageAvailable) {
                $billingafterMethodsConfiguration = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                ['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields'];
                if (isset($billingafterMethodsConfiguration)) {
                    $billingafterMethodsConfiguration['children']['country_id']['imports'] = [
                        'initialOptions' => 'index = ' . 'checkoutProvider' . ':dictionaries.' . 'billing_country_id',
                        'setOptions' => 'index = ' . 'checkoutProvider' . ':dictionaries.' . 'billing_country_id'
                    ];
                }
            }




        }
        return $jsLayout;
    }

    /**
     * Get country options list.
     *
     * @return array
     */
    private function getCountryOptions()
    {
        if (!isset($this->countryOptions)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $countryCollection = $this->countryCollectionFactory->create();
            if (!empty($this->availableCountries)) {
                /** @noinspection PhpUndefinedMethodInspection */
                $countryCollection->addFieldToFilter("country_id", ['in' =>  $this->availableCountries]);
            }
            /** @noinspection PhpUndefinedMethodInspection */
            $this->countryOptions = $countryCollection->toOptionArray();
            $this->countryOptions = $this->orderCountryOptions($this->countryOptions);
        }

        return $this->countryOptions;
    }
    /**
     * Sort country options by top country codes.
     *
     * @param array $countryOptions
     * @return array
     */
    private function orderCountryOptions(array $countryOptions)
    {
        $topCountryCodes = $this->directoryHelper->getTopCountryCodes();
        if (empty($topCountryCodes)) {
            return $countryOptions;
        }

        $headOptions = [];
        $tailOptions = [[
            'value' => 'delimiter',
            'label' => '──────────',
            'disabled' => true,
        ]];
        foreach ($countryOptions as $countryOption) {
            if (empty($countryOption['value']) || in_array($countryOption['value'], $topCountryCodes)) {
                array_push($headOptions, $countryOption);
            } else {
                array_push($tailOptions, $countryOption);
            }
        }
        return array_merge($headOptions, $tailOptions);
    }
    /**
     * Retrieve all allowed countries for scope or scopes
     *
     * @param string | null $scopeCode
     * @param string $scope
     * @return array
     * @since 100.1.2
     */
    public function getAllowedCountries(
        $scope = ScopeInterface::SCOPE_WEBSITE,
        $scopeCode = null
    )
    {
        if (empty($scopeCode)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $scopeCode = $this->getDefaultScopeCode($scope);
        }

        switch ($scope) {
            case ScopeInterface::SCOPE_WEBSITES:
            case ScopeInterface::SCOPE_STORES:
                $allowedCountries = [];
                foreach ($scopeCode as $singleFilter) {
                    $allowedCountries = array_merge(
                        $allowedCountries,
                        $this->allowedCountryModel->getCountriesFromConfig($this->getSingleScope($scope), $singleFilter)
                    );
                }
                break;
            default:
                $allowedCountries = $this->allowedCountryModel->getCountriesFromConfig($scope, $scopeCode);
        }

        return $this->allowedCountryModel->makeCountriesUnique($allowedCountries);
    }

    /**
     * Return Single Scope
     *
     * @param string $scope
     * @return string
     */
    private function getSingleScope($scope)
    {
        if ($scope == ScopeInterface::SCOPE_WEBSITES) {
            return ScopeInterface::SCOPE_WEBSITE;
        }

        if ($scope == ScopeInterface::SCOPE_STORES) {
            return ScopeInterface::SCOPE_STORE;
        }

        return $scope;
    }
}
