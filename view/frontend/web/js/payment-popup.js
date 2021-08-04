define(['jquery','Magento_Ui/js/modal/modal'],function($,modal) {
    'use strict';
    return function(config, element) {
        function initPaymentOptionPopup () {
            var e = document.querySelectorAll("img[src*='https://images.latitudepayapps.com/v2/snippet.svg'], img[src*='https://images.latitudepayapps.com/v2/api/banner'], img[src*='https://images.latitudepayapps.com/v2/LatitudePayPlusSnippet.svg']");
            [].forEach.call(
                e, function (e) {
                    e.style.cursor = "pointer",
                        e.addEventListener("click", handleClick)
                })
            function handleClick(e) {
                if (0 == document.getElementsByClassName("lpay-modal-wrapper").length) {
                    var t = new XMLHttpRequest;
                    t.onreadystatechange = function () {
                        4 == t.readyState && 200 == t.status && null != t.responseText && (document.body.insertAdjacentHTML("beforeend", t.responseText))
                        var lpaySvgModal = document.querySelector('.lpay-modal svg');
                        if(lpaySvgModal){
                            var ua = window.navigator.userAgent;
                            var msie = ua.indexOf('MSIE ');
                            lpaySvgModal.style.width = '100%';
                            if (msie <= 0){
                                lpaySvgModal.style.height = '100%';
                            }
                        }
                    },
                        t.open("GET", e.srcElement.currentSrc.replace('snippet.svg','modal.html'), !0),
                        t.send(null)
                } else document.querySelector(".lpay-modal-wrapper").style.display = "block"
            }
        }

        $(document).ready(function () {
            initPaymentOptionPopup();
        });
        return {
            initPaymentOptionPopup: initPaymentOptionPopup
        }
    }
});
