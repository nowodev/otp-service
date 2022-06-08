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
                    "from" => "whatsapp:+14155238886",
                    "body" => $message
                ]
            );
            return [
                'status'            => $response->status,
                'delivered_message' => $response->body
            ];
        } catch (TwilioException $e) {
            return [
                'status' => 'error',
                'message' => 'Could not send SMS notification.' . ' Twilio replied with: ' . $e
            ];
        }
    }
}
