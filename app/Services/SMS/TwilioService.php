<?php

namespace App\Services\SMS;

use Twilio\Rest\Client;
use App\Interfaces\OTPInterface;
use Twilio\Exceptions\TwilioException;

class TwilioService implements OTPInterface
{
    public function sendOTP($to, $message)
    {
        try {
            // Your Account SID and Auth Token from twilio.com/console
            $client = new Client(config('services.twilio.account_sid'), config('services.twilio.auth_token'));

            $response = $client->messages->create(
                $to,
                [
                    // A Twilio phone number you purchased at twilio.com/console
                    // 'from' => '+19895821065',
                    'from' => config('services.twilio.from') != '' ? config('services.twilio.from') : '',
                    "messagingServiceSid" => config('services.twilio.account_mssid'),
                    // the body of the text message you'd like to send
                    'body' =>  "Your OTP is: {$message}"
                ]
            );

            return [
                'status' => $response->status,
                'otp'    => $message
            ];
        } catch (TwilioException $e) {
            return [
                'status'  => 'error', // $response->status,
                'message' => 'Could not send SMS notification.' . ' Twilio replied with: ' . $e
            ];
        }
    }
}
