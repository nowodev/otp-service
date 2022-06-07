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
    public $nexmo;
    public $twilio;
    public $service0;
    public $clickSend;
    public $generateOTP;
    public $nexmoService;
    public $twilioService;
    public $clickSendService;
    public $delivered_message0;
    public $whatsAppTwilioService;

    public function __construct(
        GenerateOTP $generateOTP,
        NexmoService $nexmoService,
        TwilioService $twilioService,
        ClickSendService $clickSendService,
        WhatsAppTwilioService $whatsAppTwilioService
    ) {
        $this->nexmo                 = $nexmoService;
        $this->twilio                = $twilioService;
        $this->generateOTP           = $generateOTP;
        $this->clickSend             = $clickSendService;
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
        $message = $this->generateOTP->handle();

        $this->clicksend = $this->clickSendService->generateOTP($phone, $message);

        if ($this->clicksend['status'] != 'SUCCESS') {
            $this->nexmo = $this->nexmoService->generateOTP($phone, $message);

            if ($this->nexmo['status'] != 0) {
                $this->twilio = $this->twilioService->generateOTP($phone, $message);
            };
        }

        if (!is_null($this->nexmo)) {
            $this->service0 = $this->nexmo['status'] == 0 ? 'nexmo-sms' : 'twilio-sms';
        }

        $service = ($this->clicksend['status'] == 'SUCCESS') ? 'clicksend-sms' : $this->service0;

        // if ($this->clicksend['status'] != 'SUCCESS' && $this->nexmo['status'] != 0 && $this->twilio['status'] != 'sent') {
        if ($this->clicksend['status'] != 'SUCCESS' && $this->nexmo['status'] != 0 && $this->twilio['status'] != 'queued') {
            return response()->json([
                'status_code' => 401,
                'status'      => 'error',
                'data'        => [
                    'clicksend' => $this->clicksend['message'],
                    'nexmo'     => $this->nexmo['message'],
                    'twilio'    => $this->twilio['message']
                ],
            ]);
        }


        if (!is_null($this->nexmo)) {
            $this->delivered_message0 = (!is_null($this->nexmo) && $this->nexmo['status'] == 0) ? $this->nexmo['delivered_message'] : $this->twilio['delivered_message'];
        }

        $delivered_message = ($this->clicksend['status'] == 'SUCCESS') ? $this->clicksend['delivered_message'] : $this->delivered_message0;

        return response()->json([
            'status_code' => 200,
            'status'      => 'success',
            'data'        => [
                'service'           => $service,
                'delivered_message' => $delivered_message
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
        $message = $this->generateOTP->handle();

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
}
