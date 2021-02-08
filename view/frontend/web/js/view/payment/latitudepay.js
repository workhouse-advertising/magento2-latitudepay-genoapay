/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'latitudepay',
                component: 'Latitude_Payment/js/view/payment/method-renderer/latitudepay'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
