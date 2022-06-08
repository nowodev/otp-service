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
            $client = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));

            $response = $client->messages->create(
                "whatsapp:{$to}",
                [
                    "from" => "whatsapp:" . env('TWILIO_WHATSAPP_FROM'),
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
