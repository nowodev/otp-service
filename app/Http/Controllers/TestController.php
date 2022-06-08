<?php

namespace App\Http\Controllers;

use App\Services\OTPService;
use App\Traits\OTPTrait;
use Illuminate\Http\Request;

class TestController extends Controller
{
    use OTPTrait;

    public function index(Request $request)
    {
        return $this->handleMessageDispatch($request->phone_number, $request->message, $request->type);
    }
}
