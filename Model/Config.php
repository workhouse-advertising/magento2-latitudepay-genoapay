<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Latitude\Payment\Model;

use Magento\Payment\Helper\Formatter;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Config model

 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Config extends AbstractConfig
{

    use Formatter;

    /**
     * Latitude payments
     */
    const METHOD_LATITUDEPAY = 'latitudepay';

    const METHOD_GENOAPAY = 'genoapay';

    /**#@+
     * Require Billing Address
     */
    const REQUIRE_BILLING_ADDRESS_NO = 0;

    const REQUIRE_BILLING_ADDRESS_ALL = 1;

    const REQUIRE_BILLING_ADDRESS_VIRTUAL = 2;

    const TRANSFER_CART_LINE_ITEMS = 'lineItemsEnabled';

    const TRANSFER_SHIPPING_OPTIONS = 'transferShippingOptions';

    /**
     * Core data
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;
    /**
     * Core data
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Latitude\Payment\Helper\Config $configHelper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param StoreManagerInterface $storeManager
     * @param array $params
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Latitude\Payment\Helper\Config $configHelper,
        \Magento\Directory\Helper\Data $directoryHelper,
        StoreManagerInterface $storeManager,
        $params = []
    ) {
        parent::__construct($scopeConfig,$configHelper);
        $this->directoryHelper = $directoryHelper;
        $this->storeManager = $storeManager;
        if ($params) {
            $method = array_shift($params);
            $this->setMethod($method);
            $storeId = array_shift($params);
            $this->setStoreId($storeId);

        }
    }
    /**
     * Map Latitudepay General Settings
     *
     * @param string $fieldName
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _mapMethodFieldset($fieldName)
    {
        if (!$this->_methodCode) {
            return null;
        }
        switch ($fieldName) {
            case 'active':
            case 'title':
            case 'payment_action':
            case 'allowspecific':
            case 'specificcountry':
            case 'line_items_enabled':
            case 'sort_order':
            case 'debug':
                return "payment/{$this->_methodCode}/{$fieldName}";
            default:
                return null;
        }
    }

    /**
     * Map any supported payment method into a config path by specified field name
     *
     * @param string $fieldName
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getSpecificConfigPath($fieldName)
    {
        $path = null;
        switch ($this->_methodCode) {

            case self::METHOD_LATITUDEPAY:
                $path = $this->_mapMethodFieldset($fieldName);
                break;
            case self::METHOD_GENOAPAY:
                $path = $this->_mapMethodFieldset($fieldName);
                break;
        }
        return $path;
    }

}
