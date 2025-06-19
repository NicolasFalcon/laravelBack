<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

class VerificationController extends Controller
{

    protected $base_url;

    public function __construct()
    {
        $this->base_url = url('/'); // or config('app.url')
    }
     public function user_verify(Request $request)
         {
           $email = request('email');
           $otp = request('otp');
           $time = now()->subMinutes(5);
        
           $user = DB::table('users')->where('email', $email)->first();
           
         
        if ($user && $user->otp_time > $time) {
            if ($user->otp_token == $otp) {
                $status = 1;
                DB::table('users')->where('email', $email)->update(['status' => $status]);
        
                return response()->json(['msg' => 'Email verified successfully'], 200);
            } else {
                return response()->json(['msg' => 'Wrong OTP'], 400);
            }
        } else {
            return response()->json(['msg' => 'OTP expired'], 400);
        }
             }
}
