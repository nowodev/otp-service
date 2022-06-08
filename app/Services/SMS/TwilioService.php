<?php

namespace App\Services\SMS;

use Twilio\Rest\Client;
use App\Interfaces\OTPInterface;
use Twilio\Exceptions\TwilioException;

class TwilioService implements OTPInterface
{
    public function generateOTP($to, $message)
    {
        try {
            // Your Account SID and Auth Token from twilio.com/console
            $client = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));

            $response = $client->messages->create(
                $to,
                [
                    // A Twilio phone number you purchased at twilio.com/console
                    'from' => '+19895821065',
                    // the body of the text message you'd like to send
                    'body' => $message
                ]
            );

            return [
                'status'            => $response->status,
                'delivered_message' => $response->body
            ];
        } catch (TwilioException $e) {
            return [
                'status'  => $response->status,
                'message' => 'Could not send SMS notification.' . ' Twilio replied with: ' . $e
            ];
        }
    }
}
