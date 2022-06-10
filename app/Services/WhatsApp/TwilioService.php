<?php

namespace App\Services\WhatsApp;

use Twilio\Rest\Client;
use App\Interfaces\OTPInterface;
use Twilio\Exceptions\TwilioException;

class TwilioService implements OTPInterface
{
    public function sendOTP($to, $message)
    {
        try {
            $client = new Client(config('services.twilio.account_sid'), config('services.twilio.auth_token'));

            $response = $client->messages->create(
                "whatsapp:{$to}",
                [
                    "from" => "whatsapp:" . config('services.twilio.whatsapp_from'),
                    "body" => "Your OTP is: {$message}"
                ]
            );
            return [
                'status' => $response->status,
                'otp'    => $message
            ];
        } catch (TwilioException $e) {
            return [
                'status' => 'error',
                'message' => 'Could not send SMS notification.' . ' Twilio replied with: ' . $e
            ];
        }
    }
}
