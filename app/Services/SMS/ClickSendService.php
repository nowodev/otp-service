<?php

namespace App\Services\SMS;

use ClickSend;
use Exception;
use App\Interfaces\OTPInterface;

class ClickSendService implements OTPInterface
{
    public function sendOTP($to, $message)
    {
        // Configure HTTP basic authorization: BasicAuth
        $config = ClickSend\Configuration::getDefaultConfiguration()
            ->setUsername(env('CLICKSEND_USERNAME'))
            ->setPassword(env('CLICKSEND_API_KEY'));


        $apiInstance = new ClickSend\Api\SMSApi(new \GuzzleHttp\Client(), $config);
        $msg         = new \ClickSend\Model\SmsMessage();

        $msg->setFrom(env('CLICKSEND_FROM_NUMBER'));
        $msg->setBody($message);
        $msg->setTo($to);
        $msg->setSource("php-sdk");

        // \ClickSend\Model\SmsMessageCollection | SmsMessageCollection model
        $sms_messages = new \ClickSend\Model\SmsMessageCollection();
        $sms_messages->setMessages([$msg]);

        try {
            $result   = $apiInstance->smsPricePost($sms_messages);
            $response = json_decode($result);

            return [
                'status'            => $response->data->messages[0]->status,
                'delivered_message' => $response->data->messages[0]->body,
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'ERROR',
                'message' => 'Exception when calling SMSApi->smsPricePost: ', $e->getMessage(), PHP_EOL
            ];
        }
    }
}
