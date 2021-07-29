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
namespace Latitude\Payment\Block\Checkout\Cart;

use \Magento\Catalog\Block\Product\Context;
/**
 * PaymentOptions block
 *
 */
class PaymentOptions extends \Magento\Framework\View\Element\Template
{
    const IMAGE_API_URL= 'https://images.latitudepayapps.com/v2/snippet.svg';
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Latitude\Payment\Helper\Config
     */
    protected $configHelper;

    /**
     * PaymentOptions constructor.
     * @param Context $context
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Latitude\Payment\Helper\Config $configHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Latitude\Payment\Helper\Config $configHelper,
        array $data = []
        ) {
        $this->cart  = $cart;
        $this->configHelper  = $configHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Gets Installment amount for current product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAmount()
    {
        $totalAmount = $this->cart->getQuote()->getGrandTotal();
        return $totalAmount;
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

    /**
     * Retrieve Block Html
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return string
     */
    public function _toHtml()
    {
        if($this->configHelper->isLatitudepayEnabled() || $this->configHelper->isGenoapayEnabled()){
            return parent::_toHtml();
        }
        return '';
    }
}