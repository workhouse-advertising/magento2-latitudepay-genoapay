define(['jquery'],function($) {
    'use strict';
    return function(config, element) {
        function initPaymentOptionPopup () {
            $("img[src*='https://images.latitudepayapps.com/v2/snippet.svg'], img[src*='https://images.latitudepayapps.com/v2/api/banner'], img[src*='https://images.latitudepayapps.com/v2/LatitudePayPlusSnippet.svg']").click(function(){
                var url = $(this).attr('src').replace('snippet.svg','modal.html');
                $.get(url,function(html){
                    $( "body" ).append(html);
                });
            })
        }
        $(document).ready(function () {
            initPaymentOptionPopup();
        });
        return {
            initPaymentOptionPopup: initPaymentOptionPopup
        }
    }
});
