<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\NexmoService;
use App\Services\TwilioService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class OTPController extends Controller
{
    public function __invoke(Request $request, NexmoService $nexmoService, TwilioService $twilioService)
    {
        $validator = Validator::make($request->all(), [
            "phone_number" => ['required', 'string', 'regex:/^\+[0-9]{13,18}$/', 'starts_with:234,+234'],
            "message" => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 401,
                'message' => 'Error Validation',
                'data' => [
                    $validator->errors()
                ],
            ]);
        }

        $phone = $request->phone_number;
        $message = $request->message;

        $nexmo = $nexmoService->generateOTP($phone, $message);

        if ($nexmo['status'] != 0) {
            $twilio = $twilioService->generateOTP($phone, $message);

            if ($twilio['status'] != 'sent') {
                // $clicksend = $clicksendService->generateOTP($phone, $message);
            };
        }

        $service1 = ($twilio['status'] == 'sent') ? 'twilio' : 'clicksend';
        $service = ($nexmo['status'] == 0) ? 'nexmo' : $service1;

        if ($nexmo['status'] != 0 && $twilio['status'] != 'sent' /* && $clicksend['status'] != 'sent' */) {
            return response()->json([
                'status_code' => 401,
                'status' => 'error',
                'data' => [
                    'nexmo' => $nexmo['message'],
                    'twilio' => $twilio['message'],
                    // 'clicksend' => $clicksend
                ],
            ]);
        }

        return response()->json([
            'status_code' => 200,
            'status' => 'success',
            'data' => [
                'service' => $service,
                'delivered_message' => $nexmo['delivered_message'] ?? $twilio['delivered_message']
            ],
        ]);
    }
}
