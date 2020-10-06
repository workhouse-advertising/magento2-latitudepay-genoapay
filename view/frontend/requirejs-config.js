var config = {
    config: {
        mixins: {
            'Magento_Bundle/js/product-summary': {
             'Latitude_Payment/js/product-summary': true
            },
            'Magento_Swatches/js/swatch-renderer': {
                'Latitude_Payment/js/swatch-renderer': true
            }
        }
    },
    map: {
        '*': {
            "paymentOptionPopup": "Latitude_Payment/js/payment-popup"

        }
    }

};
