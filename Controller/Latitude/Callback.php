<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Latitude\Payment\Controller\Latitude;

class Callback extends \Latitude\Payment\Controller\Latitude\AbstractLatitude\Callback
{
    /**
     * Config mode type
     *
     * @var string
     */
    protected $configType = \Latitude\Payment\Model\Config::class;

    /**
     * Config method type
     *
     * @var string
     */
    protected $configMethod = \Latitude\Payment\Model\Config::METHOD_LATITUDEPAY;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $checkoutType = \Latitude\Payment\Model\Latitude\Checkout::class;
}
