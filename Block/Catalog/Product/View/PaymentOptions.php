<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Payment Options rendering block
 * Class PaymentOptions
 * @package Latitude\Payment\Block\Catalog\Product\View\PaymentOptions
 */
namespace Latitude\Payment\Block\Catalog\Product\View;

use \Magento\Catalog\Block\Product\Context;
/**
 * PaymentOptions block
 *
 */
class PaymentOptions extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     *Product model
     *
     * @var \Magento\Catalog\Model\Product
     */

    protected $product;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    protected $configHelper;

    const INSTALLMENT_NO = 10;
    const IMAGE_API_URL= 'https://images.latitudepayapps.com/v2/snippet.svg';

    /**
     * PaymentOptions constructor.
     * @param Context $context
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Latitude\Payment\Helper\Config $configHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Latitude\Payment\Helper\Config $configHelper,
        array $data = []
        ) {
        $this->coreRegistry  = $context->getRegistry();
        $this->priceCurrency = $priceCurrency;
        $this->configHelper  = $configHelper;
        parent::__construct(
            $context,
            $data
        );
    }
    /**
     * @return mixed
     */
    public function getCurrentProduct()
    {

        if($this->product == null) {
            $this->product = $this->coreRegistry->registry('current_product');
        }
        return $this->product;

    }
    /**
     * Gets Installment amount for current product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAmount()
    {
        $amountPerInstallment ='';
        $totalAmount = $this->getCurrentProduct()->getFinalPrice();
        $InstallmentNo = $this->configHelper->getConfigData('installment_no');
        if($InstallmentNo){
            $curInstallment = $InstallmentNo;
        }
        if($curInstallment){
            $amountPerInstallment = $totalAmount;
        }
        return $amountPerInstallment;
    }
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @param string $methodCode
     * @return bool
     */
    public function displayIconInMobile($methodCode = null)
    {
        return $this->configHelper->getConfigData('show_in_mobile',null, $methodCode);
    }

    /**
     * Retrieve Snippet Image
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\Phrase
     */
    public function getSnippetImage()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $param = [
            'amount' => $this->getAmount(),
            'services' => $this->configHelper->getLatitudepayPaymentServices(),
            'terms' => $this->configHelper->getLatitudepayPaymentTerms(),
            'style' => 'default'
        ];
        return self::IMAGE_API_URL . '?' . http_build_query($param);
    }

    public function _toHtml()
    {
        $_product = $this->getCurrentProduct();
        if(!$_product){
            return '';
        }
        if( $_product->isAvailable() && $_product->isSaleable() && ($this->configHelper->isLatitudepayEnabled() || $this->configHelper->isGenoapayEnabled())){
            return parent::_toHtml();
        }
        return '';
    }
}