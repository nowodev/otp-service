<?php

namespace App\Actions;

use App\Models\Otp;

class GenerateOTP
{
    public function handle(string $phone_number)
    {
        $otp = mt_rand(100000, 999999);

        Otp::query()->create([
            'phone_number' => $phone_number,
            'otp' => $otp
        ]);

        return "Your OTP is: {$otp}";
    }
}
