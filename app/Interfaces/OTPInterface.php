<?php

namespace App\Interfaces;

interface OTPInterface
{
    public function generateOTP(string $to, string $message);
}
