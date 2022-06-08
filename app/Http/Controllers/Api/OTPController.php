<?php

namespace App\Http\Controllers\Api;

use App\Actions\GenerateOTP;
use Illuminate\Http\Request;
use App\Services\SMS\NexmoService;
use App\Services\SMS\TwilioService;
use App\Http\Controllers\Controller;
use App\Services\SMS\ClickSendService;
use Illuminate\Support\Facades\Validator;
use App\Services\WhatsApp\TwilioService as WhatsAppTwilioService;

class OTPController extends Controller
{
    public $service;
    public $error = 0;
    public $generateOTP;
    public $nexmoService;
    public $twilioService;
    public $clickSendService;
    public $whatsAppTwilioService;

    public function __construct(
        GenerateOTP $generateOTP,
        NexmoService $nexmoService,
        TwilioService $twilioService,
        ClickSendService $clickSendService,
        WhatsAppTwilioService $whatsAppTwilioService
    ) {
        $this->generateOTP           = $generateOTP;
        $this->nexmoService          = $nexmoService;
        $this->twilioService         = $twilioService;
        $this->clickSendService      = $clickSendService;
        $this->whatsAppTwilioService = $whatsAppTwilioService;
    }

    public function sendSMS(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "phone_number" => ['required', 'string', 'regex:/^\+[0-9]{13,18}$/', 'starts_with:234,+234'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 401,
                'message'     => 'Error Validation',
                'data'        => [
                    $validator->errors()
                ],
            ]);
        }

        $phone   = $request->phone_number;

        $message = $this->generateOTP->handle($phone);

        switch ($this->error) {
            case 0:
                $this->service = $clickSend = $this->clicksendSMSService($phone, $message);
                if ($this->service[0]['status'] != 'SUCCESS') $this->error = 1;
                break;

            case 1:
                $this->service = $nexmo = $this->nexmoSMSService($phone, $message);
                if ($this->service[0]['status'] != 0) $this->error = 2;
                break;

            case 2:
                $this->service = $twilio = $this->twilioSMSService($phone, $message);
                if ($this->service[0]['status'] != 'queued') $this->error = 3;
                break;
        }

        if ($this->error == 3) {
            return response()->json([
                'status_code' => 401,
                'status'      => 'error',
                'data'        => [
                    'clicksend' => $clickSend[0]['message'],
                    'nexmo'     => $nexmo[0]['message'],
                    'twilio'    => $twilio[0]['message']
                ],
            ]);
        }

        return response()->json([
            'status_code' => 200,
            'status'      => 'success',
            'data'        => [
                'service'           => $this->service[1],
                'delivered_message' => $this->service[0]['delivered_message'],
            ],
        ]);
    }


    public function sendWhatsApp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "phone_number" => ['required', 'string', 'regex:/^\+[0-9]{13,18}$/', 'starts_with:234,+234'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 401,
                'message'     => 'Error Validation',
                'data'        => [
                    $validator->errors()
                ],
            ]);
        }

        $phone   = $request->phone_number;

        $message = $this->generateOTP->handle($phone);

        $twilioWhatsapp = $this->whatsAppTwilioService->generateOTP($phone, $message);


        // if ($twilioWhatsapp['status'] != 'sent') {
        if ($twilioWhatsapp['status'] != 'queued') {
            return response()->json([
                'status_code' => 401,
                'status'      => 'error',
                'data'        => [
                    'twilio' => $twilioWhatsapp['message']
                ],
            ]);
        }

        return response()->json([
            'status_code' => 200,
            'status'      => 'success',
            'data'        => [
                'service'           => 'twilio-whatsapp',
                'delivered_message' => $twilioWhatsapp['delivered_message']
            ],
        ]);
    }

    function clicksendSMSService($phone, $message)
    {
        return [$this->clickSendService->generateOTP($phone, $message), 'clicksend-sms'];
    }

    function twilioSMSService($phone, $message)
    {
        return [$this->twilioService->generateOTP($phone, $message), 'twilio-sms'];
    }

    function nexmoSMSService($phone, $message)
    {
        return [$this->nexmoService->generateOTP($phone, $message), 'nexmo-sms'];
    }
}
