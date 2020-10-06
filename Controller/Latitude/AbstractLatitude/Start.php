<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Latitude\Payment\Controller\Latitude\AbstractLatitude;

use Latitude\Payment\Controller\Latitude\GetToken;
/**
 * Class Start
 */
class Start extends GetToken
{
    /**
     * Start Latitudepay Checkout by requesting initial token and dispatching customer to Latitudepay
     *
     * @return null|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $response ='';
        try {

            $token = $this->getToken();

            if ($token === null) {
                return null;
            }

            $url = $this->checkout->getRedirectUrl();
            if (!$url) {
                $msg = __('There was an error with your payment, please try again or select other payment method.');
                $response = ['error' => 'true', 'message' => $msg];
            }
            if ($token && $url) {
                $this->_initToken($token);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->getResponse()->setRedirect($url);

            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
            return $this->_redirect('checkout', ['_fragment' => 'payment']);

        } catch (\Exception $e) {
            $response = ['error' => 'true', 'message' => $e->getMessage()];
            $this->messageManager->addExceptionMessage(
                $e,
                __('There was an error with your payment, please try again or select other payment method.')
            );
        }

        return $resultJson->setData($response);

    }
}
