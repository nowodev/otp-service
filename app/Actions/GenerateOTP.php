<?php

namespace App\Actions;

class GenerateOTP
{
    public function handle()
    {
        return 'Your OTP is: ' . mt_rand(100000, 999999);
    }
}