<?php

namespace App\Services;

use Twilio\Rest\Client;
use App\Interfaces\OTPInterface;
use Twilio\Exceptions\TwilioException;

class TwilioService implements OTPInterface
{
    public function generateOTP($to, $message)
    {
        try {
            // Your Account SID and Auth Token from twilio.com/console
            $sid = 'ACffcea15692c673fea1d48077a9775ad4';
            $token = '56fa93fa0d762860635d6d274df003a6';
            $client = new Client($sid, $token);

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
                'status' => $response->status,
                'delivered_message' => $response->body
            ];
        } catch (TwilioException $e) {
            return [
                'status' => $response->status,
                'message' => 'Could not send SMS notification.' . ' Twilio replied with: ' . $e
            ];
        }
    }
}
