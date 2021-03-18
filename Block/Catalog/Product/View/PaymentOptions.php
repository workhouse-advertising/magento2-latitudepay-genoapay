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
    public function getInstallmentAmount()
    {
        $curInstallment = 10;
        $amountPerInstallment ='';
        $totalAmount = $this->getCurrentProduct()->getFinalPrice();
        $InstallmentNo = $this->configHelper->getConfigData('installment_no');
        if($InstallmentNo){
            $curInstallment = $InstallmentNo;
        }
        if($curInstallment){
            $amountPerInstallment = $totalAmount / $curInstallment;
        }
        return $this->priceCurrency->convertAndFormat($amountPerInstallment);
    }

    /**
     * Retrieve Payment Installment Text
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\Phrase
     */
    public function getInstallmentText()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $text = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId('latitude_product_block')->toHtml();
        return __($text, $this->getInstallmentAmount());
    }
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @param string $methodCode
     * @return bool
     */
    public function displayIconInMobile($methodCode = null)
    {
        return $this->configHelper->getConfigData('show_in_mobile','',$methodCode);
    }

}