<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Latitude\Payment\Controller\Latitude;

class Fail extends \Latitude\Payment\Controller\Latitude\AbstractLatitude\Fail
{

    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = \Latitude\Payment\Model\Config::class;

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = \Latitude\Payment\Model\Config::METHOD_LATITUDEPAY;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType = \Latitude\Payment\Model\Latitude\Checkout::class;
}
