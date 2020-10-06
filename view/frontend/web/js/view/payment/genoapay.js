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
                type: 'genoapay',
                component: 'Latitude_Payment/js/view/payment/method-renderer/genoapay'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({
        });
    }
);