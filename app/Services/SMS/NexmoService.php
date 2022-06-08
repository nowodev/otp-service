<?php

namespace App\Services\SMS;

use App\Interfaces\OTPInterface;

class NexmoService implements OTPInterface
{
    public function sendOTP($to, $message)
    {
        try {
            $basic  = new \Vonage\Client\Credentials\Basic(env('VONAGE_API_KEY'), env('VONAGE_API_SECRET'));
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
