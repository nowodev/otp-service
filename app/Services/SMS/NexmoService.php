<?php

namespace App\Services\SMS;

use App\Interfaces\OTPInterface;

class NexmoService implements OTPInterface
{
    public function sendOTP($to, $message)
    {
        try {
            $basic  = new \Vonage\Client\Credentials\Basic(config('services.vonage.key'), config('services.vonage.secret'));
            $client = new \Vonage\Client($basic);

            $sendMessage = $client->sms()->send(
                new \Vonage\SMS\Message\SMS($to, 'Vonage APIs', "Your OTP is: {$message}")
            );

            $response = $sendMessage->current();

            $status = $response->getStatus();

            if ($status == 0) {
                return [
                    'status' => $status,
                    'otp'    => $message
                ];
            } else {
                return [
                    'status'  => $status,
                    'message' => 'OTP could not be sent'
                ];
            }
        } catch (\Throwable $th) {
            return [
                'status' => 'error',
                'message' => 'Could not place call.' . $th
            ];
        }
    }
}
