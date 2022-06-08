<?php

namespace App\Http\Controllers\Api;

use App\Models\Otp;
use App\Actions\GenerateOTP;
use Illuminate\Http\Request;
use App\Services\SMS\NexmoService;
use App\Services\SMS\TwilioService;
use App\Http\Controllers\Controller;
use App\Services\SMS\ClickSendService;
use Illuminate\Support\Facades\Validator;
use App\Services\Call\TwilioService as CallTwilioService;
use App\Services\WhatsApp\TwilioService as WhatsAppTwilioService;

class OTPController extends Controller
{
    public $service;
    public $error = 0;
    public $generateOTP;
    public $nexmoService;
    public $twilioService;
    public $clickSendService;
    public $callTwilioService;
    public $whatsAppTwilioService;

    public function __construct(
        GenerateOTP $generateOTP,
        NexmoService $nexmoService,
        TwilioService $twilioService,
        ClickSendService $clickSendService,
        CallTwilioService $callTwilioService,
        WhatsAppTwilioService $whatsAppTwilioService
    ) {
        $this->generateOTP           = $generateOTP;
        $this->nexmoService          = $nexmoService;
        $this->twilioService         = $twilioService;
        $this->clickSendService      = $clickSendService;
        $this->callTwilioService     = $callTwilioService;
        $this->whatsAppTwilioService = $whatsAppTwilioService;
    }

    public function sendSMS(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "phone_number" => ['required', 'string', 'regex:/^\+[0-9]{13,18}$/', 'starts_with:234,+234'],
        ]);

        if ($validator->fails()) {
            $this->checkValidation($validator);
        }

        $phone = $request->phone_number;

        $message = $this->generateOTP->handle($phone);

        if ($this->error == 0) { // +2347030935403
            $this->service = $nexmo = $this->nexmoSMS($phone, $message);
            if ($this->service[0]['status'] != 0) $this->error = 1;
        }

        if ($this->error == 1) { // +2347030935403
            $this->service = $clickSend = $this->clicksendSMS($phone, $message);
            if ($this->service[0]['status'] != 'SUCCESS') $this->error = 2;
        }

        if ($this->error == 2) { // +2349018164782
            $this->service = $twilio = $this->twilioSMS($phone, $message);
            if ($this->service[0]['status'] != 'accepted') $this->error = 3;
        }

        if ($this->error == 3) {
            return response()->json([
                'status_code' => 400,
                'status'      => 'error',
                'data'        => [
                    'clicksend' => $clickSend[0]['message'] ?? null,
                    'nexmo'     => $nexmo[0]['message'] ?? null,
                    'twilio'    => $twilio[0]['message'] ?? null,
                ],
            ], 400);
        }

        return response()->json([
            'status_code' => 200,
            'status'      => 'success',
            'data'        => [
                'service'           => $this->service[1],
                'delivered_message' => $this->service[0],
            ],
        ]);
    }


    public function sendWhatsApp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "phone_number" => ['required', 'string', 'regex:/^\+[0-9]{13,18}$/', 'starts_with:234,+234'],
        ]);

        if ($validator->fails()) {
            $this->checkValidation($validator);
        }

        $phone = $request->phone_number;

        $message = $this->generateOTP->handle($phone);

        $twilioWhatsapp = $this->whatsAppTwilioService->sendOTP($phone, $message);


        // if ($twilioWhatsapp['status'] != 'sent') {
        if ($twilioWhatsapp['status'] != 'queued') {
            return response()->json([
                'status_code' => 400,
                'status'      => 'error',
                'data'        => [
                    'twilio' => $twilioWhatsapp['message']
                ],
            ], 400);
        }

        return response()->json([
            'status_code' => 200,
            'status'      => 'success',
            'data'        => [
                'service'           => 'twilio-whatsapp',
                'delivered_message' => $twilioWhatsapp['otp']
            ],
        ]);
    }

    public function call(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "phone_number" => ['required', 'string', 'regex:/^\+[0-9]{13,18}$/', 'starts_with:234,+234'],
        ]);

        if ($validator->fails()) {
            $this->checkValidation($validator);
        }

        $phone = $request->phone_number;

        $message = $this->generateOTP->handle($phone);

        $twilioVoiceCall = $this->callTwilioService->sendOTP($phone, $message);

        // if ($twilioVoiceCall['status'] != 'completed') {
        if ($twilioVoiceCall['status'] != 'queued') {
            return response()->json([
                'status_code' => 400,
                'status'      => 'error',
                'data'        => [
                    'twilio' => $twilioVoiceCall['message']
                ],
            ], 400);
        }

        return response()->json([
            'status_code' => 200,
            'status'      => 'success',
            'data'        => [
                'service'           => 'twilio-voice',
                'delivered_message' => $twilioVoiceCall['otp']
            ],
        ]);
    }

    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "phone_number" => ['required', 'string', 'regex:/^\+[0-9]{13,18}$/', 'starts_with:234,+234'],
            "otp"          => ['required', 'numeric', 'digits:6']
        ]);

        if ($validator->fails()) {
            $this->checkValidation($validator);
        }

        $verify = Otp::query()->where([
            'phone_number' => $request->phone_number,
            'otp'          => $request->otp
        ])->first();

        if (!$verify) {
            return response()->json([
                'status_code' => 400,
                'status'      => 'error',
                'message'     => 'Invalid OTP. Please check the OTP and try again.'
            ], 400);
        }

        return response()->json([
            'status_code' => 200,
            'status'      => 'success',
            'message'     => 'OTP Verified successfully.'
        ]);
    }

    function checkValidation($validator)
    {
        return response()->json([
            'status_code' => 400,
            'message'     => 'Error Validation',
            'data'        => [
                $validator->errors()
            ],
        ], 400);
    }

    function clicksendSMS($phone, $message)
    {
        return [$this->clickSendService->sendOTP($phone, $message), 'clicksend-sms'];
    }

    function twilioSMS($phone, $message)
    {
        return [$this->twilioService->sendOTP($phone, $message), 'twilio-sms'];
    }

    function nexmoSMS($phone, $message)
    {
        return [$this->nexmoService->sendOTP($phone, $message), 'nexmo-sms'];
    }
}
