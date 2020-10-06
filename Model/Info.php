<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace  Latitude\Payment\Model;

/**
 * Latitude payment information model

 */
class Info
{

    const PAYER_ID              = 'payer_id';

    const PAYER_EMAIL           = 'email';

    const PAYER_STATUS          = 'payer_status';

    const ADDRESS_ID            = 'address_id';

    const ADDRESS_STATUS        = 'address_status';

    const PROTECTION_EL         = 'protection_eligibility';

    const FRAUD_FILTERS         = 'collected_fraud_filters';

    const PAYMENT_STATUS        = 'payment_status';

    const PENDING_REASON        = 'pending_reason';

    const IS_FRAUD              = 'is_fraud_detected';

    const PAYMENT_STATUS_GLOBAL = 'latitude_payment_status';

    const PENDING_REASON_GLOBAL = 'latitude_pending_reason';

    const IS_FRAUD_GLOBAL = 'latitude_is_fraud_detected';

    /**
     * payer id code key
     */
    const LATITUDE_PAYER_ID = 'latitude_payer_id';

    /**
     *  payer email code key
     */
    const LATITUDE_PAYER_EMAIL = 'latitude_payer_email';

    /**
     * Payer status code key
     */
    const LATITUDE_PAYER_STATUS = 'latitude_payer_status';

    /**
     * Address id code key
     */
    const LATITUDE_ADDRESS_ID = 'latitude_address_id';

    /**
     * Address status code key
     */
    const LATITUDE_ADDRESS_STATUS = 'latitude_address_status';

    /**
     * protection eligibility code key
     */
    const LATITUDE_PROTECTION_ELIGIBILITY = 'latitude_protection_eligibility';

    /**
     * Fraud filters code key
     */
    const LATITUDE_FRAUD_FILTERS = 'latitude_fraud_filters';



    /**
 *
 *
 * All payment information map
 *
 * @var array
 */
    protected $_paymentMap = [
        self::PAYER_ID => self::LATITUDE_PAYER_ID,
        self::PAYER_EMAIL => self::LATITUDE_PAYER_EMAIL,
        self::PAYER_STATUS => self::LATITUDE_PAYER_STATUS,
        self::ADDRESS_ID => self::LATITUDE_ADDRESS_ID,
        self::ADDRESS_STATUS => self::LATITUDE_ADDRESS_STATUS,
        self::PROTECTION_EL => self::LATITUDE_PROTECTION_ELIGIBILITY,
        self::FRAUD_FILTERS => self::LATITUDE_FRAUD_FILTERS

    ];
    /**
     * System information map
     *
     * @var array
     */
    protected $_systemMap = [
        self::PAYMENT_STATUS => self::PAYMENT_STATUS_GLOBAL,
        self::PENDING_REASON => self::PENDING_REASON_GLOBAL,
        self::IS_FRAUD => self::IS_FRAUD_GLOBAL,
    ];
    /**
     * Grab data from source and map it into payment
     *
     * @param array|\Magento\Framework\DataObject|callback $from
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return void
     */
    public function importToPayment($from, \Magento\Payment\Model\InfoInterface $payment)
    {
        $fullMap = array_merge($this->_paymentMap, $this->_systemMap);
        if (is_object($from)) {
            $from = [$from, 'getDataUsingMethod'];
        }
        \Magento\Framework\DataObject\Mapper::accumulateByMap($from, [$payment, 'setAdditionalInformation'], $fullMap);
    }
}
