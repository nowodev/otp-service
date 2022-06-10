<?php

namespace App\Traits;

use App\Services\SMS\NexmoService;
use App\Services\SMS\TwilioService;
use App\Services\SMS\ClickSendService;
use App\Services\Call\TwilioService as CallTwilioService;
use App\Services\WhatsApp\TwilioService as WhatsAppTwilioService;

trait OTPTrait
{
    public $error = 0;
    public $nexmoService;
    public $twilioService;
    public $clickSendService;
    public $callTwilioService;
    public $whatsAppTwilioService;

    public function __construct(
        NexmoService $nexmoService,
        TwilioService $twilioService,
        ClickSendService $clickSendService,
        CallTwilioService $callTwilioService,
        WhatsAppTwilioService $whatsAppTwilioService
    ) {
        $this->nexmoService          = $nexmoService;
        $this->twilioService         = $twilioService;
        $this->clickSendService      = $clickSendService;
        $this->callTwilioService     = $callTwilioService;
        $this->whatsAppTwilioService = $whatsAppTwilioService;
    }

    public function handleMessageDispatch($phone, $message, $type)
    {
        switch ($type) {
            case 'whatsapp':
                $twilioWhatsapp = $this->whatsAppTwilioService->sendOTP($phone, $message);

                if ($twilioWhatsapp['status'] != 'queued') return false;

                return true;

                break;

            case 'call':
                $twilioVoiceCall = $this->callTwilioService->sendOTP($phone, $message);

                if ($twilioVoiceCall['status'] != 'queued') return false;

                return true;

                break;

            default:
                if ($this->error == 0) {
                    $nexmo = $this->nexmoService->sendOTP($phone, $message);

                    if ($nexmo['status'] != 0) $this->error = 1;
                }

                if ($this->error == 1) {
                    $clickSend = $this->clickSendService->sendOTP($phone, $message);

                    if ($clickSend['status'] != 'SUCCESS') $this->error = 2;
                }

                if ($this->error == 2) {
                    $twilio = $this->twilioService->sendOTP($phone, $message);

                    if ($twilio['status'] != 'accepted') $this->error = 3;
                }

                if ($this->error == 3) return false;

                return true;

                break;
        }
    }
}
