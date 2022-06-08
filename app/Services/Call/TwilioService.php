<?php

namespace App\Services\Call;

use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;
use App\Interfaces\OTPInterface;
use Twilio\Exceptions\TwilioException;

class TwilioService implements OTPInterface
{
    public function sendOTP($to, $message)
    {
        try {
            $client = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));

            $response = $client->calls->create(
                $to,
                "+19895821065", // from
                [
                    "twiml" => $this->generateTwiMLForVoiceCall($message)
                ]
            );

            return [
                'status' => $response->status,
                'otp'    => $message
            ];
        } catch (TwilioException $e) {
            return [
                'status' => 'error',
                'message' => 'Could not place call.' . $e
            ];
        }
    }

    function generateTwiMLForVoiceCall($otpCode)
    {
        /**
         * We add spaces between each digit in the otpCode so Twilio pronounces each number instead of pronouncing the whole word.
         *
         * @See https://www.twilio.com/docs/voice/twiml/say#hints
         */
        $otpCode = implode(' ', str_split($otpCode));

        $voiceMessage = new VoiceResponse();
        $voiceMessage->say('This is an automated call providing you your OTP from the test app.');
        $voiceMessage->say('Your one time password is ' . $otpCode);
        $voiceMessage->pause(['length' => 1]);
        $voiceMessage->say('Your one time password is ' . $otpCode);
        $voiceMessage->say('GoodBye');

        return $voiceMessage;
    }
}