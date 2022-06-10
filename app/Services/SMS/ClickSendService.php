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
        ->setUsername(config('services.clicksend.username'))
        ->setPassword(config('services.clicksend.api_key'));

        $apiInstance = new ClickSend\Api\SMSApi(new \GuzzleHttp\Client(), $config);
        $msg         = new \ClickSend\Model\SmsMessage();

        if (!empty(config('services.clicksend.from'))) $msg->setFrom(config('app.clicksend.from'));
        $msg->setBody("Your OTP is: {$message}");
        $msg->setTo($to);
        $msg->setSource("sdk");

        // \ClickSend\Model\SmsMessageCollection | SmsMessageCollection model
        $sms_messages = new \ClickSend\Model\SmsMessageCollection();
        $sms_messages->setMessages([$msg]);

        try {
            $result   = $apiInstance->smsSendPost($sms_messages);
            $response = json_decode($result);

            return [
                'status' => $response->data->messages[0]->status,
                'otp'    => $message
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'ERROR',
                'message' => 'Exception when calling SMSApi->smsPricePost: ', $e->getMessage(), PHP_EOL
            ];
        }
    }
}
