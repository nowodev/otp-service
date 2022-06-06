<?php

namespace App\Services;

use App\Interfaces\OTPInterface;

class NexmoService implements OTPInterface
{
    public function generateOTP($to, $message)
    {
        $basic  = new \Vonage\Client\Credentials\Basic(env('VONAGE_API_KEY'), env('VONAGE_API_SECRET'));
        $client = new \Vonage\Client($basic);

        $sendMessage = $client->sms()->send(
            new \Vonage\SMS\Message\SMS($to, 'BRAND_NAME', $message)
        );

        $response = $sendMessage->current();

        $status = $response->getStatus();

        if ($status == 0) {
            return [
                'status' => $status,
                'delivered_message' => $message
            ];
        } else {
            return [
                'status' => $status,
                'message' => 'OTP could not be sent'
            ];
        }
    }
}
