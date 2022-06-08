<?php

namespace App\Actions;

use App\Models\Otp;

class GenerateOTP
{
    public function handle(string $phone_number)
    {
        $otp = mt_rand(100000, 999999);

        Otp::query()->where('phone_number', $phone_number)->delete();

        Otp::query()->create([
            'phone_number' => $phone_number,
            'otp'          => $otp
        ]);

        return $otp;
    }
}
