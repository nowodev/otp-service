<?php

namespace App\Interfaces;

interface OTPInterface
{
    public function sendOTP(string $to, string $message);
}
