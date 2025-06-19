<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use Carbon\Carbon;
use App\Services\MailchimpTransactionalService;
use Illuminate\Support\Facades\View;
use PHPMailer\PHPMailer\Exception;
use Newsletter;

class UserController extends Controller
{
    protected $mailchimp;
    protected $base_url;

    public function __construct(MailchimpTransactionalService $mailchimp)
    {
       
        $this->base_url = url('/'); // or config('app.url')
        
        $this->mailchimp = $mailchimp;
    }
    public function user_registration(Request $request)
    {
            $otpTime = now();
            $version = $request->input('version');
            $deviceid = $request->input('deviceid');
            $name = $request->input('name');
            $email = $request->input('email');
            $password = $request->input('password');
            $socialID = $request->input('socialid');
            $socialtoken = $request->input('socialtoken');
            $signuptype = $request->input('signuptype');
            $socialtype = $request->input('socialtype');
            $deviceToken = $request->input('devicetoken');
            $platform = $request->input('platform');
    
            $loginToken = rand(100000000000, 999999999999999);
    
            $userWithDevice = DB::table('users')->where('device_id', $deviceid)->first();
            $check_user = DB::table('users')
                ->where('email', $email)
                ->where('device_id', $deviceid)
                // ->where('status',1)
                ->first();
            $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
            $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
            $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
            // if($name === 'undefined' || $name = 0 || $name = '0') {
            //         $name = null;
            //     }
    
            if ($versions_current) {
                if ($check_user) {
    
                    return response()->json(['id' => $check_user->id ?? null, 'msg' => 'User already exists', 'profile_compl_status' => $check_user->profile_compl_status ?? null]);
                }
    
                if (!$email) {
                    return response()
                        ->json(['msg' => 'email is required']);
                }
                if ($email === "NULL" || $email === "null") {
                    return response()
                        ->json(['msg' => 'please enter valid email']);
                }
                $user = DB::table('users')->where('device_id', $deviceid)->first();
                if ($user) {
                    return response()->json([
                        'id' => $user->id ?? null,
                        'msg' => 'registered with given these details',
                        'email' => $user->email,
                        'device_id' => $user->device_id,
                        'social_type' => $user->social_type,
                        'platform' => $user->platform,
                        'profile_compl_status' => $user->profile_compl_status ?? null
                    ]);
                }
                DB::table('test_users')
                    ->insert([
                        'name' => $name,
                        'email' => $email ?? null,
                        'social_id' => $socialID,
                        'social_token' => $socialtoken,
                        'signup_type' => $signuptype,
                        'social_type' => $socialtype,
                        'image' => null,
                        'status' => 0,
                        'profile_compl_status' => 0,
                        'device_id' => $deviceid,
                        'device_token' => $deviceToken,
                        'login_token' => $loginToken,
                        'platform' => $platform,
                    ]);
    
                if (empty($userWithDevice) && empty($socialtype)) {
    
                    // User with device already exists, send OTP and update details
                    $randomNumber = rand(1000, 9999);
                    $loginToken = rand(100000000000, 999999999999999);
                    $otpTime = now();
                    // dd($otpTime);
                    DB::table('users')->where('device_id', $deviceid)->insert([
                        'name' => $name,
                        'email' => $email,
                        'password' => $password,
                        'otp_token' => $randomNumber,
                        'otp_time' => $otpTime,
                        'login_token' => $loginToken,
                        'platform' => $platform,
                        'device_id' => $deviceid,
                        'profile_compl_status' => 0
                    ]);
    
    
                    $data = ['name' => $name, 'email' => $email, 'randomNumber' => $randomNumber];
    
                    Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
                        $message->to($email)->subject('Verification Code');
                    });
    
                    // $socialuser = DB::table('users')->where('email', $email)->first();
                    // $id = $socialuser->id;
    
                    return response()->json([
                        // 'id' => $id,
                        'msg' => 'OTP sent to your email',
                        'status' => 0,
                        'email' => $request->input('email'),
    
                    ]);
                } else {
                    if (!$socialID && !$socialtoken) {
    
                        //  $userWithDevice = '05B2D934-571C-4607-A689-403FB9A0737D';
                        // Register via traditional signup
                        $user = DB::table('users')->where('email', $email)->orWhere('device_id', $deviceid)->first();
    
    
    
                        if ($user) {
                            if ($user->status === 0) {
    
                                $randomNumber = rand(1000, 9999);
                                $loginToken = rand(100000000000, 999999999999999);
                                $otpTime = now();
    
                                DB::table('users')->where('email', $email)->update(['name' => $name, 'password' => $password, 'otp_token' => $randomNumber, 'otp_time' => $otpTime, 'login_token' => $loginToken, 'platform' => $platform]);
    
                                $data = ['name' => $name, 'email' => $email, 'randomNumber' => $randomNumber,];
    
                                Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
                                    $message->to($email)->subject('Verification Code');
                                });
                                $socialuser = DB::table('users')->where('email', $email)->orWhere('device_id', $deviceid)->first();
    
                                $id = $socialuser->id;
                                // Insert the user into the database
                                DB::table('users')->where('id', $id)
                                    ->update([
    
                                        'name' => $name,
                                        'email' => $email,
                                        'password' => $password,
                                        'otp_token' => $randomNumber,
                                        'otp_time' => $otpTime,
                                        'status' => 0,
                                        'image' => null,
                                        'profile_compl_status' => 1,
                                        'device_id' => $deviceid,
                                        'device_token' => $deviceToken,
                                        'login_token' => $loginToken,
                                        'platform' => $platform,
                                    ]);
    
                                return response()
                                    ->json(['id' => $id, 'msg' => 'OTP sent to your email', 'status' => 0, 'email' => $request->input('email')]);
                            }
                            return response()
                                ->json(['msg' => 'User already registered with deviceID and active', 'status' => 1, 'email' => $user->email]);
                        }
    
                        // Register a new user via traditional signup
                        $randomNumber = rand(1000, 9999);
                        $loginToken = rand(100000000000, 999999999999999);
                        $otptime = now();
    
                        $data = ['name' => $name, 'email' => $email, 'randomNumber' => $randomNumber,];
    
                        Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
                            $message->to($email)->subject('Verification Code');
                        });
    
                        // Insert the user into the database
                        DB::table('users')
                            ->insert([
    
                                'name' => $name,
                                'email' => $email,
                                'password' => $password,
                                'otp_token' => $randomNumber,
                                'otp_time' => $otptime,
                                'status' => 0,
                                'image' => null,
                                'profile_compl_status' => 0,
                                'device_id' => $deviceid,
                                'signup_type' => $signuptype,
                                'social_type' => $socialtype,
                                'device_token' => $deviceToken,
                                'login_token' => $loginToken,
                                'platform' => $platform,
                            ]);
    
                        $socialuser = DB::table('users')->where('email', $email)->first();
    
                        return response()->json(['id' => $socialuser->id ?? null, 'msg' => 'OTP sent to your email', 'status' => 0, 'email' => $request->input('email'), 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                    } else {
    
                        // Register via social login when device id or socialid exist
                        $socialuser = DB::table('users')->where('social_id', $socialID)->orWhere('device_id', $deviceid)->first();
                        $socialuseremail = DB::table('users')->where('social_id', $socialID)->orWhere('email', $email)->orWhere('device_id', $deviceid)->first();
    
    
                        if ($socialuser) {
    
                            //   DB::table('users')->where('social_id',$socialID)
                            // ->update([
                            //     'name' => $name, // Add name from social data if available
                            //     'email' => $email??null, // Add email from social data if available
                            //     'social_id' => $socialID,
                            //     'social_token' => $socialtoken,
                            //     'signup_type' => $signuptype,
                            //     'social_type' => $socialtype,
                            //     'image' => null,
                            //     'status' => 1,
                            //     'profile_compl_status' => 1,
                            //     'device_id' => $deviceid,
                            //     'device_token' => $deviceToken,
                            //     'login_token' => $loginToken,
                            //     'platform' => $platform,
                            // ]);
                            return response()->json(['id' => $socialuser->id ?? null, 'msg' => 'User already exists', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                        }
    
                        if ($socialuseremail) {
                            return response()->json(['id' => $socialuser->id ?? null, 'msg' => 'User already exists via other authentication', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                        }
                        // Register a new user via social login
                        DB::table('users')
                            ->insert([
                                'name' => $name, // Add name from social data if available
                                'email' => $email ?? null, // Add email from social data if available
                                'social_id' => $socialID,
                                'social_token' => $socialtoken,
                                'signup_type' => $signuptype,
                                'social_type' => $socialtype,
                                'image' => null,
                                'status' => 1,
                                'profile_compl_status' => 0,
                                'device_id' => $deviceid,
                                'device_token' => $deviceToken,
                                'login_token' => $loginToken,
                                'platform' => $platform,
                            ]);
                        $socialuser = DB::table('users')->where('social_id', $socialID)->first();
    
                        return response()
                            ->json(['id' => $socialuser->id ?? null, 'msg' => 'User registered via social login', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                    }
                }
            } elseif ($versions_middle) {
                if ($check_user) {
                    return response()
                        ->json(['msg' => 'you are already exist']);
                }
                $user = DB::table('users')->where('email', $email)->orWhere('device_id', $deviceid)->first();
                if ($user) {
                    return response()->json([
                        'id' => $user->id ?? null,
                        'msg' => 'registered with given these details',
                        'email' => $user->email,
                        'device_id' => $user->device_id,
                        'social_type' => $user->social_type,
                        'platform' => $user->platform,
                        'profile_compl_status' => $user->profile_compl_status ?? null
                    ]);
                }
                DB::table('test_users')
                    ->insert([
                        'name' => $name,
                        'email' => $email ?? null,
                        'social_id' => $socialID,
                        'social_token' => $socialtoken,
                        'signup_type' => $signuptype,
                        'social_type' => $socialtype,
                        'image' => null,
                        'status' => 0,
                        'profile_compl_status' => 0,
                        'device_id' => $deviceid,
                        'device_token' => $deviceToken,
                        'login_token' => $loginToken,
                        'platform' => $platform,
                    ]);
                if (empty($userWithDevice) && empty($socialtype)) {
    
    
                    // User with device already exists, send OTP and update details
                    $randomNumber = rand(1000, 9999);
                    $loginToken = rand(100000000000, 999999999999999);
                    $otpTime = now();
                    DB::table('users')->where('device_id', $deviceid)->insert([
                        'name' => $name,
                        'email' => $email,
                        'password' => $password,
                        'otp_token' => $randomNumber,
                        'otp_time' => $otpTime,
                        'login_token' => $loginToken,
                        'platform' => $platform,
                        'device_id' => $deviceid,
                        'profile_compl_status' => 0
                    ]);
    
    
                    $data = ['name' => $name, 'email' => $email, 'randomNumber' => $randomNumber];
    
                    Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
                        $message->to($email)->subject('Verification Code');
                    });
    
                    // $socialuser = DB::table('users')->where('email', $email)->first();
                    // $id = $socialuser->id;
    
                    return response()->json([
                        // 'id' => $id,
                        'msg' => 'OTP sent to your email',
                        'status' => 0,
                        'email' => $request->input('email'),
    
                    ]);
                } else {
                    if (!$socialID && !$socialtoken) {
    
                        //  $userWithDevice = '05B2D934-571C-4607-A689-403FB9A0737D';
                        // Register via traditional signup
                        $user = DB::table('users')->where('email', $email)->orWhere('device_id', $deviceid)->first();
    
    
    
                        if ($user) {
                            if ($user->status === 0) {
    
                                $randomNumber = rand(1000, 9999);
                                $loginToken = rand(100000000000, 999999999999999);
                                $otpTime = now();
    
                                DB::table('users')->where('email', $email)->update(['name' => $name, 'password' => $password, 'otp_token' => $randomNumber, 'otp_time' => $otpTime, 'login_token' => $loginToken, 'platform' => $platform]);
    
                                $data = ['name' => $name, 'email' => $email, 'randomNumber' => $randomNumber,];
    
                                Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
                                    $message->to($email)->subject('Verification Code');
                                });
                                $socialuser = DB::table('users')->where('email', $email)->orWhere('device_id', $deviceid)->first();
    
                                $id = $socialuser->id;
                                // Insert the user into the database
                                DB::table('users')->where('id', $id)
                                    ->update([
    
                                        'name' => $name,
                                        'email' => $email,
                                        'password' => $password,
                                        'otp_token' => $randomNumber,
                                        'otp_time' => $otptime,
                                        'status' => 0,
                                        'image' => null,
                                        'profile_compl_status' => 1,
                                        'device_id' => $deviceid,
                                        'device_token' => $deviceToken,
                                        'login_token' => $loginToken,
                                        'platform' => $platform,
                                    ]);
    
                                return response()
                                    ->json(['id' => $id, 'msg' => 'OTP sent to your email', 'status' => 0, 'email' => $request->input('email')]);
                            }
                            return response()
                                ->json(['msg' => 'User already registered with deviceID and active', 'status' => 1, 'email' => $user->email]);
                        }
    
                        // Register a new user via traditional signup
                        $randomNumber = rand(1000, 9999);
                        $loginToken = rand(100000000000, 999999999999999);
                        $otptime = now();
    
                        $data = ['name' => $name, 'email' => $email, 'randomNumber' => $randomNumber,];
    
                        Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
                            $message->to($email)->subject('Verification Code');
                        });
    
                        // Insert the user into the database
                        DB::table('users')
                            ->insert([
    
                                'name' => $name,
                                'email' => $email,
                                'password' => $password,
                                'otp_token' => $randomNumber,
                                'otp_time' => $otptime,
                                'status' => 0,
                                'image' => null,
                                'profile_compl_status' => 0,
                                'device_id' => $deviceid,
                                'device_token' => $deviceToken,
                                'signup_type' => $signuptype,
                                'social_type' => $socialtype,
                                'login_token' => $loginToken,
                                'platform' => $platform,
                            ]);
    
                        $socialuser = DB::table('users')->where('email', $email)->first();
    
                        return response()->json(['id' => $socialuser->id ?? null, 'msg' => 'OTP sent to your email', 'status' => 0, 'email' => $request->input('email'), 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                    } else {
    
                        // Register via social login when device id or socialid exist
                        $socialuser = DB::table('users')->where('social_id', $socialID)->orWhere('device_id', $deviceid)->first();
                        $socialuseremail = DB::table('users')->where('social_id', $socialID)->orWhere('email', $email)->orWhere('device_id', $deviceid)->first();
    
    
                        if ($socialuser) {
    
                            //   DB::table('users')->where('device_id',$deviceid)
                            // ->update([
                            //     'name' => $name, // Add name from social data if available
                            //     'email' => $email??null, // Add email from social data if available
                            //     'social_id' => $socialID,
                            //     'social_token' => $socialtoken,
                            //     'signup_type' => $signuptype,
                            //     'social_type' => $socialtype,
                            //     'image' => null,
                            //     'status' => 1,
                            //     'profile_compl_status' => 1,
                            //     'device_id' => $deviceid,
                            //     'device_token' => $deviceToken,
                            //     'login_token' => $loginToken,
                            //     'platform' => $platform,
                            // ]);
                            return response()->json(['id' => $socialuser->id ?? null, 'msg' => 'User already exists', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                        }
    
                        if ($socialuseremail) {
                            return response()->json(['id' => $socialuser->id ?? null, 'msg' => 'User already exists via other authentication', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                        }
                        // Register a new user via social login
                        DB::table('users')
                            ->insert([
                                'name' => $name, // Add name from social data if available
                                'email' => $email ?? null, // Add email from social data if available
                                'social_id' => $socialID,
                                'social_token' => $socialtoken,
                                'signup_type' => $signuptype,
                                'social_type' => $socialtype,
                                'image' => null,
                                'status' => 1,
                                'profile_compl_status' => 0,
                                'device_id' => $deviceid,
                                'device_token' => $deviceToken,
                                'login_token' => $loginToken,
                                'platform' => $platform,
                            ]);
                        $socialuser = DB::table('users')->where('social_id', $socialID)->first();
    
                        return response()
                            ->json(['id' => $socialuser->id ?? null, 'msg' => 'User registered via social login', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                    }
                }
            } elseif ($versions_past) {
                $user = DB::table('users')->where('email', $email)->orWhere('device_id', $deviceid)->first();
                if ($user) {
                    return response()->json([
                        'id' => $user->id ?? null,
                        'msg' => 'registered with given these details',
                        'email' => $user->email,
                        'device_id' => $user->device_id,
                        'social_type' => $user->social_type,
                        'platform' => $user->platform,
                        'profile_compl_status' => $user->profile_compl_status ?? null
                    ]);
                }
                if ($check_user) {
                    return response()
                        ->json(['msg' => 'you are already exist']);
                }
                DB::table('test_users')
                    ->insert([
                        'name' => $name,
                        'email' => $email ?? null,
                        'social_id' => $socialID,
                        'social_token' => $socialtoken,
                        'signup_type' => $signuptype,
                        'social_type' => $socialtype,
                        'image' => null,
                        'status' => 0,
                        'profile_compl_status' => 0,
                        'device_id' => $deviceid,
                        'device_token' => $deviceToken,
                        'login_token' => $loginToken,
                        'platform' => $platform,
                    ]);
                if (empty($userWithDevice) && empty($socialtype)) {
    
    
                    // User with device already exists, send OTP and update details
                    $randomNumber = rand(1000, 9999);
                    $loginToken = rand(100000000000, 999999999999999);
                    $otpTime = now();
                    DB::table('users')->where('device_id', $deviceid)->insert([
                        'name' => $name,
                        'email' => $email,
                        'password' => $password,
                        'otp_token' => $randomNumber,
                        'otp_time' => $otpTime,
                        'login_token' => $loginToken,
                        'platform' => $platform,
                        'device_id' => $deviceid,
                        'profile_compl_status' => 0
                    ]);
    
    
                    $data = ['name' => $name, 'email' => $email, 'randomNumber' => $randomNumber];
    
                    Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
                        $message->to($email)->subject('Verification Code');
                    });
    
                    // $socialuser = DB::table('users')->where('email', $email)->first();
                    // $id = $socialuser->id;
    
                    return response()->json([
                        // 'id' => $id,
                        'msg' => 'OTP sent to your email',
                        'status' => 0,
                        'email' => $request->input('email'),
    
                    ]);
                } else {
                    if (!$socialID && !$socialtoken) {
    
                        //  $userWithDevice = '05B2D934-571C-4607-A689-403FB9A0737D';
                        // Register via traditional signup
                        $user = DB::table('users')->where('email', $email)->orWhere('device_id', $deviceid)->first();
    
    
    
                        if ($user) {
                            if ($user->status === 0) {
    
                                $randomNumber = rand(1000, 9999);
                                $loginToken = rand(100000000000, 999999999999999);
                                $otpTime = now();
    
                                DB::table('users')->where('email', $email)->update(['name' => $name, 'password' => $password, 'otp_token' => $randomNumber, 'otp_time' => $otpTime, 'login_token' => $loginToken, 'platform' => $platform]);
    
                                $data = ['name' => $name, 'email' => $email, 'randomNumber' => $randomNumber,];
    
                                Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
                                    $message->to($email)->subject('Verification Code');
                                });
                                $socialuser = DB::table('users')->where('email', $email)->orWhere('device_id', $deviceid)->first();
    
                                $id = $socialuser->id;
                                // Insert the user into the database
                                DB::table('users')->where('id', $id)
                                    ->update([
    
                                        'name' => $name,
                                        'email' => $email,
                                        'password' => $password,
                                        'otp_token' => $randomNumber,
                                        'status' => 0,
                                        'image' => null,
                                        'profile_compl_status' => 1,
                                        'device_id' => $deviceid,
                                        'device_token' => $deviceToken,
                                        'login_token' => $loginToken,
                                        'platform' => $platform,
                                    ]);
    
                                return response()
                                    ->json(['id' => $id, 'msg' => 'OTP sent to your email', 'status' => 0, 'email' => $request->input('email')]);
                            }
                            return response()
                                ->json(['msg' => 'User already registered and active', 'status' => 1]);
                        }
    
                        // Register a new user via traditional signup
                        $randomNumber = rand(1000, 9999);
                        $loginToken = rand(100000000000, 999999999999999);
                        $otptime = now();
    
                        $data = ['name' => $name, 'email' => $email, 'randomNumber' => $randomNumber,];
    
                        Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
                            $message->to($email)->subject('Verification Code');
                        });
    
                        // Insert the user into the database
                        DB::table('users')
                            ->insert([
    
                                'name' => $name,
                                'email' => $email,
                                'password' => $password,
                                'otp_token' => $randomNumber,
                                'otp_time' => $otptime,
                                'status' => 0,
                                'image' => null,
                                'profile_compl_status' => 0,
                                'device_id' => $deviceid,
                                'device_token' => $deviceToken,
                                'login_token' => $loginToken,
                                'platform' => $platform,
                            ]);
    
                        $socialuser = DB::table('users')->where('email', $email)->first();
    
                        return response()->json(['id' => $socialuser->id ?? null, 'msg' => 'OTP sent to your email', 'status' => 0, 'email' => $request->input('email'), 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                    } else {
    
                        // Register via social login when device id or socialid exist
                        $socialuser = DB::table('users')->where('social_id', $socialID)->orWhere('device_id', $deviceid)->first();
                        $socialuseremail = DB::table('users')->where('social_id', $socialID)->orWhere('email', $email)->orWhere('device_id', $deviceid)->first();
    
    
                        if ($socialuser) {
    
                            //   DB::table('users')->where('device_id',$deviceid)
                            // ->update([
                            //     'name' => $name, // Add name from social data if available
                            //     'email' => $email??null, // Add email from social data if available
                            //     'social_id' => $socialID,
                            //     'social_token' => $socialtoken,
                            //     'signup_type' => $signuptype,
                            //     'social_type' => $socialtype,
                            //     'image' => null,
                            //     'status' => 1,
                            //     'profile_compl_status' => 1,
                            //     'device_id' => $deviceid,
                            //     'device_token' => $deviceToken,
                            //     'login_token' => $loginToken,
                            //     'platform' => $platform,
                            // ]);
                            return response()->json(['id' => $socialuser->id ?? null, 'msg' => 'User already exists', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                        }
    
                        if ($socialuseremail) {
                            return response()->json(['id' => $socialuser->id ?? null, 'msg' => 'User already exists via other authentication', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                        }
                        // Register a new user via social login
                        DB::table('users')
                            ->insert([
                                'name' => $name, // Add name from social data if available
                                'email' => $email ?? null, // Add email from social data if available
                                'social_id' => $socialID,
                                'social_token' => $socialtoken,
                                'signup_type' => $signuptype,
                                'social_type' => $socialtype,
                                'image' => null,
                                'status' => 1,
                                'profile_compl_status' => 0,
                                'device_id' => $deviceid,
                                'device_token' => $deviceToken,
                                'login_token' => $loginToken,
                                'platform' => $platform,
                            ]);
                        $socialuser = DB::table('users')->where('social_id', $socialID)->first();
    
                        return response()
                            ->json(['id' => $socialuser->id ?? null, 'msg' => 'User registered via social login', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                    }
                }
            } else {
                return response()->json([
                    'msg' => 'Please update the app to the latest version.'
    
                ]);
            }

    }


    public function user_login(Request $request)
    {
        $version = $request->input('version');
        $name = $request->input('name');
        $token = rand(100000000000, 999999999999999);
        $email = $request->input('email');
        $password = $request->input('password');
        $socialID = $request->input('socialid');
        $socialtoken = $request->input('socialtoken');
        $socialtype = $request->input('socialtype');
        $deviceToken = $request->input('devicetoken');
        $signuptype = $request->input('signuptype');
        $platform = $request->input('platform');

        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        if ($versions_current) {

            if ($socialtype === 'google') {
                // Check Google social ID
                $socialuser = DB::table('users')->where('social_id', $socialID)->where('social_type', 'google')
                    ->first();

                if ($socialuser) {
                    $loginToken = rand(100000000000, 999999999999999);

                    // Update the login_token for the user
                    DB::table('users')
                        ->where('social_id', $socialID)
                        ->where('social_type', 'google')
                        ->update([
                            'login_token' => $loginToken,
                            'device_token' => $deviceToken,
                            'platform' => $platform
                        ]);

                    return response()->json(['msg' => 'Login successful', 'id' => $socialuser->id, 'name' => $socialuser->name, 'email' => $socialuser->email, 'goal' => $socialuser->goal, 'age' => $socialuser->age, 'height' => $socialuser->height, 'weight' => $socialuser->weight, 'fitness_level' => $socialuser->fitness_level, 'focus_area' => $socialuser->focus_area, 'gender' => $socialuser->gender, 'profile_status' => $socialuser->profile_compl_status ?? null, 'login_token' => $loginToken, 'device_token' => $deviceToken,]);
                } else {
                    return response()
                        ->json([
                            'msg' => 'User does not exist with provided Google social credentials',

                        ]);
                }
            } elseif ($socialtype === 'facebook') {
                // Check Facebook social ID
                $socialuser = DB::table('users')->where('social_id', $socialID)->where('social_type', 'facebook')
                    ->first();

                if ($socialuser) {
                    $loginToken = rand(100000000000, 999999999999999);

                    // Update the login_token for the user
                    DB::table('users')
                        ->where('social_id', $socialID)
                        ->where('social_type', 'google')
                        ->update([
                            'login_token' => $loginToken,
                            'device_token' => $deviceToken,
                            'platform' => $platform
                        ]);

                    return response()->json(['msg' => 'Login successful', 'status' => 1, 'id' => $socialuser->id, 'name' => $socialuser->name, 'email' => $socialuser->email, 'goal' => $socialuser->goal, 'age' => $socialuser->age, 'height' => $socialuser->height, 'weight' => $socialuser->weight, 'fitness_level' => $socialuser->fitness_level, 'focus_area' => $socialuser->focus_area, 'gender' => $socialuser->gender, 'profile_status' => $socialuser->profile_compl_status ?? null, 'login_token' => $loginToken, 'device_token' => $deviceToken,]);
                } else {
                    return response()
                        ->json(['msg' => 'User does not exist with provided Facebook social credentials',]);
                }
            } elseif ($socialtype === 'Apple') {

                // Check Apple social ID
                $socialuser = DB::table('users')->where('social_id', $socialID)->where('social_type', 'Apple')->first();

                if ($socialuser) {
                    // User exists, update login token
                    $loginToken = rand(100000000000, 999999999999999);

                    // Update the login_token for the user
                    DB::table('users')
                        ->where('social_id', $socialID)
                        ->where('social_type', 'Apple')
                        ->update([
                            'login_token' => $loginToken,
                            'device_token' => $deviceToken,
                            'platform' => $platform
                        ]);

                    return response()->json(['msg' => 'Login successful', 'status' => 1, 'id' => $socialuser->id, 'name' => $socialuser->name, 'email' => $socialuser->email, 'goal' => $socialuser->goal, 'age' => $socialuser->age, 'height' => $socialuser->height, 'weight' => $socialuser->weight, 'fitness_level' => $socialuser->fitness_level, 'focus_area' => $socialuser->focus_area, 'gender' => $socialuser->gender, 'profile_status' => $socialuser->profile_compl_status ?? null, 'login_token' => $loginToken, 'device_token' => $deviceToken,]);
                } else {
                    // $check_email = DB::table('users')->where('email',$email)->first();
                    // if($check_email){
                    //      return response()
                    //     ->json(['msg' => 'email alrady registerd','social_type' =>$check_email->social_type]);

                    // }
                }
                $check_email = DB::table('users')->where('email', $email)->first();
                if ($check_email) {
                    return response()
                        ->json(['msg' => 'email alrady registerd', 'social_type' => $check_email->social_type, 'user_id' => $check_email->id]);
                }
                // User does not exist, insert new user
                $loginToken = rand(100000000000, 999999999999999);

                DB::table('users')->insert([
                    'name' => $name,
                    'email' => $email ?? null,
                    'social_id' => $socialID ?? null,
                    'social_token' => $socialtoken ?? null,
                    'signup_type' => $signuptype ?? null,
                    'social_type' => $socialtype ?? null,
                    'image' => '',
                    'status' => 0,
                    'profile_compl_status' => 0,
                    'device_token' => $deviceToken ?? null,
                    'login_token' => $loginToken ?? null,
                    'platform' => $platform ?? null,

                ]);

                $socialuser = DB::table('users')->where('social_id', $socialID)->where('social_type', 'Apple')->first();


                return response()->json([
                    'msg' => 'User does not exist with provided Apple social credentials',
                    'id' => $socialuser->id,
                    'login_token' => $loginToken,
                    'profile_status' => $socialuser->profile_compl_status ?? null,
                ]);
            }
            // Attempt login with email and password if no social credentials provided
            $user = DB::table('users')->where('email', $email)->where('password', $password)->first();
            if ($user) {
                // Fetch user details if login is successful
                $userDetails = DB::table('users')->select('name', 'email', 'image', 'status', 'profile_compl_status', 'id')
                    ->where('email', $email)->first();

                if ($userDetails->status == 1) {
                    $loginToken = rand(100000000000, 999999999999999);

                    // Update the login_token for the user
                    DB::table('users')
                        ->where('email', $email)
                        ->update([
                            'login_token' => $loginToken,
                            'device_token' => $deviceToken,
                            'platform' => $platform
                        ]);

                    $data = ['msg' => 'Login successful', 'id' => $userDetails->id, 'email' => $userDetails->email, 'name' => $userDetails->name, 'image' =>  $this->base_url . "/json/profile_img/{$userDetails->image}", 'profile_status' => $userDetails->profile_compl_status ?? null, 'goal' => $userDetails->goal ?? null, 'age' => $userDetails->age ?? null, 'height' => $userDetails->height ?? null, 'weight' => $userDetails->weight ?? null, 'fitness_level' => $userDetails->fitness_level ?? null, 'focus_area' => $userDetails->focus_area ?? null, 'gender' => $userDetails->gender ?? null, 'login_token' => $loginToken, 'device_token' => $deviceToken,];

                    return response()
                        ->json($data, 200, [], JSON_NUMERIC_CHECK);
                } else {
                    return response()->json(['msg' => 'User exists, but login is currently disabled',]);
                }
            }

            return response()
                ->json(['msg' => 'Login failed, invalid credentials',]);
        } elseif ($versions_middle) {
            if ($socialtype === 'google') {
                // Check Google social ID
                $socialuser = DB::table('users')->where('social_id', $socialID)->where('social_type', 'google')
                    ->first();

                if ($socialuser) {
                    $loginToken = rand(100000000000, 999999999999999);

                    // Update the login_token for the user
                    DB::table('users')
                        ->where('social_id', $socialID)
                        ->where('social_type', 'google')
                        ->update([
                            'login_token' => $loginToken,
                            'device_token' => $deviceToken
                        ]);

                    return response()->json(['msg' => 'Login successful', 'id' => $socialuser->id, 'name' => $socialuser->name, 'email' => $socialuser->email, 'goal' => $socialuser->goal, 'age' => $socialuser->age, 'height' => $socialuser->height, 'weight' => $socialuser->weight, 'fitness_level' => $socialuser->fitness_level, 'focus_area' => $socialuser->focus_area, 'gender' => $socialuser->gender, 'profile_status' => $socialuser->profile_compl_status ?? null, 'login_token' => $loginToken, 'device_token' => $deviceToken,]);
                } else {
                    return response()
                        ->json([
                            'msg' => 'User does not exist with provided Google social credentials',

                        ]);
                }
            } elseif ($socialtype === 'facebook') {
                // Check Facebook social ID
                $socialuser = DB::table('users')->where('social_id', $socialID)->where('social_type', 'facebook')
                    ->first();

                if ($socialuser) {
                    $loginToken = rand(100000000000, 999999999999999);

                    // Update the login_token for the user
                    DB::table('users')
                        ->where('social_id', $socialID)
                        ->where('social_type', 'google')
                        ->update([
                            'login_token' => $loginToken,
                            'device_token' => $deviceToken
                        ]);

                    return response()->json(['msg' => 'Login successful', 'status' => 1, 'id' => $socialuser->id, 'name' => $socialuser->name, 'email' => $socialuser->email, 'goal' => $socialuser->goal, 'age' => $socialuser->age, 'height' => $socialuser->height, 'weight' => $socialuser->weight, 'fitness_level' => $socialuser->fitness_level, 'focus_area' => $socialuser->focus_area, 'gender' => $socialuser->gender, 'profile_status' => $socialuser->profile_compl_status ?? null, 'login_token' => $loginToken, 'device_token' => $deviceToken,]);
                } else {
                    return response()
                        ->json(['msg' => 'User does not exist with provided Facebook social credentials',]);
                }
            } elseif ($socialtype === 'Apple') {

                // Check Apple social ID
                $socialuser = DB::table('users')->where('social_id', $socialID)->where('social_type', 'Apple')->first();

                if ($socialuser) {
                    // User exists, update login token
                    $loginToken = rand(100000000000, 999999999999999);

                    // Update the login_token for the user
                    DB::table('users')
                        ->where('social_id', $socialID)
                        ->where('social_type', 'Apple')
                        ->update([
                            'login_token' => $loginToken,
                            'device_token' => $deviceToken
                        ]);

                    return response()->json(['msg' => 'Login successful', 'status' => 1, 'id' => $socialuser->id, 'name' => $socialuser->name, 'email' => $socialuser->email, 'goal' => $socialuser->goal, 'age' => $socialuser->age, 'height' => $socialuser->height, 'weight' => $socialuser->weight, 'fitness_level' => $socialuser->fitness_level, 'focus_area' => $socialuser->focus_area, 'gender' => $socialuser->gender, 'profile_status' => $socialuser->profile_compl_status ?? null, 'login_token' => $loginToken, 'device_token' => $deviceToken,]);
                }

                // User does not exist, insert new user
                $loginToken = rand(100000000000, 999999999999999);

                DB::table('users')->insert([
                    'name' => $name,
                    'email' => $email ?? null,
                    'social_id' => $socialID ?? null,
                    'social_token' => $socialtoken ?? null,
                    'signup_type' => $signuptype ?? null,
                    'social_type' => $socialtype ?? null,
                    'image' => '',
                    'status' => 0,
                    'profile_compl_status' => 0,
                    'device_token' => $deviceToken ?? null,
                    'login_token' => $loginToken ?? null,

                ]);

                $socialuser = DB::table('users')->where('social_id', $socialID)->where('social_type', 'Apple')->first();


                return response()->json([
                    'msg' => 'User does not exist with provided Apple social credentials',
                    'id' => $socialuser->id,
                    'login_token' => $loginToken,
                    'profile_status' => $socialuser->profile_compl_status ?? null,
                ]);
            }
            // Attempt login with email and password if no social credentials provided
            $user = DB::table('users')->where('email', $email)->where('password', $password)->first();
            if ($user) {
                // Fetch user details if login is successful
                $userDetails = DB::table('users')->select('name', 'email', 'image', 'status', 'profile_compl_status', 'id')
                    ->where('email', $email)->first();

                if ($userDetails->status == 1) {
                    $loginToken = rand(100000000000, 999999999999999);

                    // Update the login_token for the user
                    DB::table('users')
                        ->where('email', $email)
                        ->update([
                            'login_token' => $loginToken,
                            'device_token' => $deviceToken
                        ]);

                    $data = ['msg' => 'Login successful', 'id' => $userDetails->id, 'email' => $userDetails->email, 'name' => $userDetails->name, 'image' =>  $this->base_url . "/json/profile_img/{$userDetails->image}", 'profile_status' => $userDetails->profile_compl_status ?? null, 'goal' => $userDetails->goal ?? null, 'age' => $userDetails->age ?? null, 'height' => $userDetails->height ?? null, 'weight' => $userDetails->weight ?? null, 'fitness_level' => $userDetails->fitness_level ?? null, 'focus_area' => $userDetails->focus_area ?? null, 'gender' => $userDetails->gender ?? null, 'login_token' => $loginToken, 'device_token' => $deviceToken,];

                    return response()
                        ->json($data, 200, [], JSON_NUMERIC_CHECK);
                } else {
                    return response()->json(['msg' => 'User exists, but login is currently disabled',]);
                }
            }

            return response()
                ->json(['msg' => 'Login failed, invalid credentials',]);
        } elseif ($versions_past) {
            if ($socialtype === 'google') {
                // Check Google social ID
                $socialuser = DB::table('users')->where('social_id', $socialID)->where('social_type', 'google')
                    ->first();

                if ($socialuser) {
                    $loginToken = rand(100000000000, 999999999999999);

                    // Update the login_token for the user
                    DB::table('users')
                        ->where('social_id', $socialID)
                        ->where('social_type', 'google')
                        ->update([
                            'login_token' => $loginToken,
                            'device_token' => $deviceToken
                        ]);

                    return response()->json(['msg' => 'Login successful', 'id' => $socialuser->id, 'name' => $socialuser->name, 'email' => $socialuser->email, 'goal' => $socialuser->goal, 'age' => $socialuser->age, 'height' => $socialuser->height, 'weight' => $socialuser->weight, 'fitness_level' => $socialuser->fitness_level, 'focus_area' => $socialuser->focus_area, 'gender' => $socialuser->gender, 'profile_status' => $socialuser->profile_compl_status ?? null, 'login_token' => $loginToken, 'device_token' => $deviceToken,]);
                } else {
                    return response()
                        ->json([
                            'msg' => 'User does not exist with provided Google social credentials',

                        ]);
                }
            } elseif ($socialtype === 'facebook') {
                // Check Facebook social ID
                $socialuser = DB::table('users')->where('social_id', $socialID)->where('social_type', 'facebook')
                    ->first();

                if ($socialuser) {
                    $loginToken = rand(100000000000, 999999999999999);

                    // Update the login_token for the user
                    DB::table('users')
                        ->where('social_id', $socialID)
                        ->where('social_type', 'google')
                        ->update([
                            'login_token' => $loginToken,
                            'device_token' => $deviceToken
                        ]);

                    return response()->json(['msg' => 'Login successful', 'status' => 1, 'id' => $socialuser->id, 'name' => $socialuser->name, 'email' => $socialuser->email, 'goal' => $socialuser->goal, 'age' => $socialuser->age, 'height' => $socialuser->height, 'weight' => $socialuser->weight, 'fitness_level' => $socialuser->fitness_level, 'focus_area' => $socialuser->focus_area, 'gender' => $socialuser->gender, 'profile_status' => $socialuser->profile_compl_status ?? null, 'login_token' => $loginToken, 'device_token' => $deviceToken,]);
                } else {
                    return response()
                        ->json(['msg' => 'User does not exist with provided Facebook social credentials',]);
                }
            } elseif ($socialtype === 'Apple') {

                // Check Apple social ID
                $socialuser = DB::table('users')->where('social_id', $socialID)->where('social_type', 'Apple')->first();

                if ($socialuser) {
                    // User exists, update login token
                    $loginToken = rand(100000000000, 999999999999999);

                    // Update the login_token for the user
                    DB::table('users')
                        ->where('social_id', $socialID)
                        ->where('social_type', 'Apple')
                        ->update([
                            'login_token' => $loginToken,
                            'device_token' => $deviceToken
                        ]);

                    return response()->json(['msg' => 'Login successful', 'status' => 1, 'id' => $socialuser->id, 'name' => $socialuser->name, 'email' => $socialuser->email, 'goal' => $socialuser->goal, 'age' => $socialuser->age, 'height' => $socialuser->height, 'weight' => $socialuser->weight, 'fitness_level' => $socialuser->fitness_level, 'focus_area' => $socialuser->focus_area, 'gender' => $socialuser->gender, 'profile_status' => $socialuser->profile_compl_status ?? null, 'login_token' => $loginToken, 'device_token' => $deviceToken,]);
                }

                // User does not exist, insert new user
                $loginToken = rand(100000000000, 999999999999999);

                DB::table('users')->insert([
                    'name' => $name,
                    'email' => $email ?? null,
                    'social_id' => $socialID ?? null,
                    'social_token' => $socialtoken ?? null,
                    'signup_type' => $signuptype ?? null,
                    'social_type' => $socialtype ?? null,
                    'image' => '',
                    'status' => 0,
                    'profile_compl_status' => 0,
                    'device_token' => $deviceToken ?? null,
                    'login_token' => $loginToken ?? null,

                ]);

                $socialuser = DB::table('users')->where('social_id', $socialID)->where('social_type', 'Apple')->first();


                return response()->json([
                    'msg' => 'User does not exist with provided Apple social credentials',
                    'id' => $socialuser->id,
                    'login_token' => $loginToken,
                    'profile_status' => $socialuser->profile_compl_status ?? null,
                ]);
            }
            // Attempt login with email and password if no social credentials provided
            $user = DB::table('users')->where('email', $email)->where('password', $password)->first();
            if ($user) {
                // Fetch user details if login is successful
                $userDetails = DB::table('users')->select('name', 'email', 'image', 'status', 'profile_compl_status', 'id')
                    ->where('email', $email)->first();

                if ($userDetails->status == 1) {
                    $loginToken = rand(100000000000, 999999999999999);

                    // Update the login_token for the user
                    DB::table('users')
                        ->where('email', $email)
                        ->update([
                            'login_token' => $loginToken,
                            'device_token' => $deviceToken
                        ]);

                    $data = ['msg' => 'Login successful', 'id' => $userDetails->id, 'email' => $userDetails->email, 'name' => $userDetails->name, 'image' =>  $this->base_url . "/adserver/public/profile_image/{$userDetails->image}", 'profile_status' => $userDetails->profile_compl_status ?? null, 'goal' => $userDetails->goal ?? null, 'age' => $userDetails->age ?? null, 'height' => $userDetails->height ?? null, 'weight' => $userDetails->weight ?? null, 'fitness_level' => $userDetails->fitness_level ?? null, 'focus_area' => $userDetails->focus_area ?? null, 'gender' => $userDetails->gender ?? null, 'login_token' => $loginToken, 'device_token' => $deviceToken,];

                    return response()
                        ->json($data, 200, [], JSON_NUMERIC_CHECK);
                } else {
                    return response()->json(['msg' => 'User exists, but login is currently disabled',]);
                }
            }

            return response()
                ->json(['msg' => 'Login failed, invalid credentials',]);
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }

    public function sendemail_link(Request $request)
    {

        $version = $request->input('version');

        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        $email = request('email');
        $user = DB::table('users')->where('email', $email)->where('social_type','form')->first();
        if ($versions_current) {
            if ($user) {
                $data = ['email' => $email];
                $send['to'] = $email;
                Mail::send('passwordupdate_email', ['data' => $data], function ($messages) use ($send) {
                    $messages->to($send['to']);
                    $messages->subject('Email Id Verification');
                });
                $token = rand(100000000000, 999999999999999);
                $current_time = now();

                DB::table('users')->where('email', $email)->update(['token' => $token, 'updated_at' => $current_time]);
                $msg[] = [
                    'msg' => "Mail sent"
                ];
                echo json_encode($msg, JSON_NUMERIC_CHECK);
            } else {
                $msg[] = [
                    'msg' => "User does not exist"
                ];
                echo json_encode($msg, JSON_NUMERIC_CHECK);
            }
        } elseif ($versions_middle) {
            if ($user) {
                $data = ['email' => $email];
                $send['to'] = $email;
                Mail::send('passwordupdate_email', ['data' => $data], function ($messages) use ($send) {
                    $messages->to($send['to']);
                    $messages->subject('Email Id Verification');
                });
                $token = rand(100000000000, 999999999999999);
                $current_time = now();

                DB::table('users')->where('email', $email)->update(['token' => $token, 'updated_at' => $current_time]);
                $msg[] = [
                    'msg' => "Mail sent"
                ];
                echo json_encode($msg, JSON_NUMERIC_CHECK);
            } else {
                $msg[] = [
                    'msg' => "User does not exist"
                ];
                echo json_encode($msg, JSON_NUMERIC_CHECK);
            }
        } elseif ($versions_past) {
            if ($user) {
                $data = ['email' => $email];
                $send['to'] = $email;
                Mail::send('passwordupdate_email', ['data' => $data], function ($messages) use ($send) {
                    $messages->to($send['to']);
                    $messages->subject('Email Id Verification');
                });
                $token = rand(100000000000, 999999999999999);
                $current_time = now();

                DB::table('users')->where('email', $email)->update(['token' => $token, 'updated_at' => $current_time]);
                $msg[] = [
                    'msg' => "Mail sent"
                ];
                echo json_encode($msg, JSON_NUMERIC_CHECK);
            } else {
                $msg[] = [
                    'msg' => "User does not exist"
                ];
                echo json_encode($msg, JSON_NUMERIC_CHECK);
            }
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
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
                DB::table('users')->where('email', $email)->update(['status' => $status,]);

                return response()->json(['msg' => 'Email verified successfully', 'id' => $user->id, 'profile_compl_status' => $user->profile_compl_status ?? null,]);
            } else {
                return response()
                    ->json(['msg' => 'Wrong OTP']);
            }
        } else {
            return response()
                ->json(['msg' => 'OTP expired']);
        }
    }

    public function user_update_details(Request $request)
    {
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        $id = $request->input('id');
        $user = DB::table('users')->select('email')->where('id', $id)->first();
        $gender = $request->input('gender');
        $goal = $request->input('goal');
        $name = $request->input('name');
        $age = $request->input('age');
        $experience = $request->input('experience');
        $workout_plans = $request->input('workout_plans');
        $fitness_level = $request->input('fitnesslevel');
        $focus_area = $request->input('focusarea');
        $weight = $request->input('weight');
        $target_weight = $request->input('targetweight');
        $height = $request->input('height');
        $injury = $request->input('injury') ?? null;
        $equipment = $request->input('equipment');
        $workoutarea = $request->input('workoutarea');
        $workoutroutine = $request->input('workoutroutine');
        $sleepduration = $request->input('sleepduration');
        $mindstate = $request->input('mindstate');
        $alcoholconstent = $request->input('alcoholconstent');
        $alcoholquantity = $request->input('alcoholquantity');
        $deviceid = $request->input('deviceid');
        $deviceToken = $request->input('devicetoken');
        $registerconfirmation = 'registered';
        if ($versions_current) {

            if ($user) {
                DB::table('users')
                    ->where('id', $id)
                    ->update([
                        'gender' => $gender,
                        'age' => $age,
                        'height' => $height,
                        'weight' => $weight,
                        'target_weight' => $target_weight,
                        'fitness_level' => $fitness_level,
                        'focus_area' => $focus_area,
                        'experience' => $experience,
                        'workout_plans' => $workout_plans,
                        'goal' => $goal,
                        'profile_compl_status' => 1,
                        'injury' => $injury,
                        'equipment' => $equipment,
                        'workoutarea' => $workoutarea,
                        'workout_routine' => $workoutroutine,
                        'sleep_duration' => $sleepduration,
                        'mind_state' => $mindstate,
                        'Alcohol_Consent' => $alcoholconstent,
                        'Alcohol_Qauntity' => $alcoholquantity,
                        'device_id' => $deviceid,
                        'registered_notregistered' => 'registered',
                        'name' => $name,
                    ]);

                return response()->json(['msg' => 'User Updated Successfully']);
            } else {
                // Insert new user details
                DB::table('users')->insert([
                    'gender' => $gender,
                    'age' => $age,
                    'height' => $height,
                    'weight' => $weight,
                    'target_weight' => $target_weight,
                    'fitness_level' => $fitness_level,
                    'focus_area' => $focus_area,
                    'experience' => $experience,
                    'workout_plans' => $workout_plans,
                    'experience' => $experience,
                    'workout_plans' => $workout_plans,
                    'goal' => $goal,
                    'profile_compl_status' => 0,
                    'injury' => $injury,
                    'equipment' => $equipment,
                    'workoutarea' => $workoutarea,
                    'workout_routine' => $workoutroutine,
                    'sleep_duration' => $sleepduration,
                    'mind_state' => $mindstate,
                    'Alcohol_Consent' => $alcoholconstent,
                    'Alcohol_Qauntity' => $alcoholquantity,
                    'device_id' => $deviceid,
                    'device_token' => $deviceToken,
                    'registered_notregistered' => 'notregistered',
                    'name' => $name,
                ]);

                return response()->json(['msg' => 'New User Inserted Successfully']);
            }
        } elseif ($versions_middle) {
            if ($user) {
                DB::table('users')
                    ->where('id', $id)
                    ->update([
                        'gender' => $gender,
                        'age' => $age,
                        'height' => $height,
                        'weight' => $weight,
                        'target_weight' => $target_weight,
                        'fitness_level' => $fitness_level,
                        'focus_area' => $focus_area,
                        'experience' => $experience,
                        'workout_plans' => $workout_plans,
                        'experience' => $experience,
                        'workout_plans' => $workout_plans,
                        'goal' => $goal,
                        'profile_compl_status' => 1,
                        'injury' => $injury,
                        'equipment' => $equipment,
                        'workoutarea' => $workoutarea,
                        'workout_routine' => $workoutroutine,
                        'sleep_duration' => $sleepduration,
                        'mind_state' => $mindstate,
                        'Alcohol_Consent' => $alcoholconstent,
                        'Alcohol_Qauntity' => $alcoholquantity,
                        'device_id' => $deviceid,
                        'registered_notregistered' => 'registered'
                    ]);

                return response()->json(['msg' => 'User Updated Successfully']);
            } else {
                // Insert new user details
                DB::table('users')->insert([
                    'gender' => $gender,
                    'age' => $age,
                    'height' => $height,
                    'weight' => $weight,
                    'target_weight' => $target_weight,
                    'fitness_level' => $fitness_level,
                    'focus_area' => $focus_area,
                    'experience' => $experience,
                    'workout_plans' => $workout_plans,
                    'goal' => $goal,
                    'profile_compl_status' => 0,
                    'injury' => $injury,
                    'equipment' => $equipment,
                    'workoutarea' => $workoutarea,
                    'workout_routine' => $workoutroutine,
                    'sleep_duration' => $sleepduration,
                    'mind_state' => $mindstate,
                    'Alcohol_Consent' => $alcoholconstent,
                    'Alcohol_Qauntity' => $alcoholquantity,
                    'device_id' => $deviceid,
                    'device_token' => $deviceToken,
                    'registered_notregistered' => 'notregistered'
                ]);

                return response()->json(['msg' => 'New User Inserted Successfully']);
            }
        } elseif ($versions_past) {
            if ($user) {
                DB::table('users')
                    ->where('id', $id)
                    ->update([
                        'gender' => $gender,
                        'age' => $age,
                        'height' => $height,
                        'weight' => $weight,
                        'target_weight' => $target_weight,
                        'fitness_level' => $fitness_level,
                        'focus_area' => $focus_area,
                        'experience' => $experience,
                        'workout_plans' => $workout_plans,
                        'experience' => $experience,
                        'workout_plans' => $workout_plans,
                        'goal' => $goal,
                        'profile_compl_status' => 1,
                        'injury' => $injury,
                        'equipment' => $equipment,
                        'workoutarea' => $workoutarea,
                        'workout_routine' => $workoutroutine,
                        'sleep_duration' => $sleepduration,
                        'mind_state' => $mindstate,
                        'Alcohol_Consent' => $alcoholconstent,
                        'Alcohol_Qauntity' => $alcoholquantity,
                        'device_id' => $deviceid,
                        'registered_notregistered' => 'registered'
                    ]);

                return response()->json(['msg' => 'User Updated Successfully']);
            } else {
                // Insert new user details
                DB::table('users')->insert([
                    'gender' => $gender,
                    'age' => $age,
                    'height' => $height,
                    'weight' => $weight,
                    'target_weight' => $target_weight,
                    'fitness_level' => $fitness_level,
                    'focus_area' => $focus_area,
                    'experience' => $experience,
                    'workout_plans' => $workout_plans,
                    'goal' => $goal,
                    'profile_compl_status' => 0,
                    'injury' => $injury,
                    'equipment' => $equipment,
                    'workoutarea' => $workoutarea,
                    'workout_routine' => $workoutroutine,
                    'sleep_duration' => $sleepduration,
                    'mind_state' => $mindstate,
                    'Alcohol_Consent' => $alcoholconstent,
                    'Alcohol_Qauntity' => $alcoholquantity,
                    'device_id' => $deviceid,
                    'device_token' => $deviceToken,
                    'registered_notregistered' => 'notregistered'
                ]);

                return response()->json(['msg' => 'New User Inserted Successfully']);
            }
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }


    public function allworkout(Request $request)
    {
        $id = $request->id;
        $page = $request->input('page', 1);
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        $user = DB::table('users')->select('gender')->where('id', $id)->first();

        // Check if the user exists and has a gender specified
        if ($versions_current) {
            if ($user && $user->gender) {
                $mindsetworkout = DB::table('workout_mindset')->select('*')->get();
                $mindsetworkout = $mindsetworkout->map(function ($workout) {
                            $workout->workout_mindset_image_link =  $this->base_url . '/images/'.$workout->workout_mindset_image;
                            return $workout;
                        });

                $workoutdetails = DB::table('workouts')
                    ->select('workouts.workout_id', 'workouts.workout_level', 'workouts.workout_bodypart', 'workouts.workout_goal', 'goals.goal_title',  'workouts.workout_title', 'workouts.workout_duration', 'workouts.workout_image', 'workouts.workout_gender', 'levels.level_title', 'workout_image_link', 'workouts.workout_description', 'workouts.workout_price', 'workouts.total_workout_like', 'workouts.total_workout_views')
                    ->where('workout_gender', $user->gender)
                    ->join('levels', 'workouts.workout_level', '=', 'levels.level_id')
                    ->join('goals', 'workouts.workout_goal', '=', 'goals.goal_id')
                    ->get();



                $response = [];

                foreach ($workoutdetails as $workout) {
                    $workoutId = $workout->workout_id;
                    $days = [];

                    for ($i = 1; $i <= 7; $i++) {
                        $userdetails = DB::table('we_day' . $i)
                            ->select('exercise_id', 'workout_id', 'day_' . $i)
                            ->where('workout_id', $workoutId)
                            ->get();

                        $exercises = [];
                        $totalRestInSeconds = 0; // Initialize total rest in seconds
                        $totalCalories = 0;

                        foreach ($userdetails as $userdetail) {
                            $exercise_id = $userdetail->exercise_id;

                            $exercise = DB::table('exercises')
                                ->select('exercise_id', 'exercise_rest', 'exercise_calories')
                                ->where('exercise_id', $exercise_id)
                                ->first();

                            if ($exercise) {
                                $exercises[] = $exercise;

                                // Extract the numeric value from the string (assuming format like '20 Sec')
                                $restValue = (int) filter_var($exercise->exercise_rest, FILTER_SANITIZE_NUMBER_INT);

                                // Check if the unit is 'Sec', then add directly
                                if (strpos($exercise->exercise_rest, 'sec') !== false) {
                                    $totalRestInSeconds += $restValue;
                                }

                                $totalCalories += $exercise->exercise_calories;
                            }
                        }

                        $days['day_' . $i] = [
                            'exercises' => $exercises,
                        ];

                        // Adding total rest time and total calories to each day
                        $days['day_' . $i]['total_rest'] = $totalRestInSeconds;
                        $days['day_' . $i]['total_calories'] = $totalCalories;
                    }

                    $workout->days = $days;
                    $response[] = $workout;
                }

                return response()->json(['workout_Data' => $response, 'mindset_workout_data' => $mindsetworkout]);
            } else {
                return response()->json(['error' => 'User not found or gender not specified']);
            }
        } elseif ($versions_middle) {
         
            if ($user && $user->gender) {
                $mindsetworkout = DB::table('workout_mindset')->select('*')->get();
                
                
                $mindsetworkout = $mindsetworkout->map(function ($workout) {
                            $workout->workout_mindset_image_link =  $this->base_url . '/images/'.$workout->workout_mindset_image;
                            return $workout;
                        });
                
                
                // dd($mindsetworkout);

                $workoutdetails = DB::table('workouts')
                    ->select('workouts.workout_id', 'workouts.workout_level', 'workouts.workout_bodypart', 'workouts.workout_goal', 'goals.goal_title',  'workouts.workout_title', 'workouts.workout_duration', 'workouts.workout_image', 'workouts.workout_gender', 'levels.level_title', 'workout_image_link', 'workouts.workout_description', 'workouts.workout_price', 'workouts.total_workout_like', 'workouts.total_workout_views')
                    ->where('workout_gender', $user->gender)
                    ->join('levels', 'workouts.workout_level', '=', 'levels.level_id')
                    ->join('goals', 'workouts.workout_goal', '=', 'goals.goal_id')
                    ->get();



                $response = [];

                foreach ($workoutdetails as $workout) {
                    $workoutId = $workout->workout_id;
                    $days = [];

                    for ($i = 1; $i <= 7; $i++) {
                        $userdetails = DB::table('we_day' . $i)
                            ->select('exercise_id', 'workout_id', 'day_' . $i)
                            ->where('workout_id', $workoutId)
                            ->get();

                        $exercises = [];
                        $totalRestInSeconds = 0; // Initialize total rest in seconds
                        $totalCalories = 0;

                        foreach ($userdetails as $userdetail) {
                            $exercise_id = $userdetail->exercise_id;

                            $exercise = DB::table('exercises')
                                ->select('exercise_id', 'exercise_rest', 'exercise_calories')
                                ->where('exercise_id', $exercise_id)
                                ->first();

                            if ($exercise) {
                                $exercises[] = $exercise;

                                // Extract the numeric value from the string (assuming format like '20 Sec')
                                $restValue = (int) filter_var($exercise->exercise_rest, FILTER_SANITIZE_NUMBER_INT);

                                // Check if the unit is 'Sec', then add directly
                                if (strpos($exercise->exercise_rest, 'sec') !== false) {
                                    $totalRestInSeconds += $restValue;
                                }

                                $totalCalories += $exercise->exercise_calories;
                            }
                        }

                        $days['day_' . $i] = [
                            'exercises' => $exercises,
                        ];

                        // Adding total rest time and total calories to each day
                        $days['day_' . $i]['total_rest'] = $totalRestInSeconds;
                        $days['day_' . $i]['total_calories'] = $totalCalories;
                    }

                    $workout->days = $days;
                    $response[] = $workout;
                }

                return response()->json(['workout_Data' => $response, 'mindset_workout_data' => $mindsetworkout]);
            } else {
                return response()->json(['error' => 'User not found or gender not specified'], 404);
            }
        } elseif ($versions_past) {
            if ($user && $user->gender) {
                $mindsetworkout = DB::table('workout_mindset')->select('*')->get();
                $mindsetworkout = $mindsetworkout->map(function ($workout) {
                            $workout->workout_mindset_image_link =  $this->base_url . '/images/'.$workout->workout_mindset_image;
                            return $workout;
                        });

                $workoutdetails = DB::table('workouts')
                    ->select('workouts.workout_id', 'workouts.workout_level', 'workouts.workout_bodypart', 'workouts.workout_goal', 'goals.goal_title',  'workouts.workout_title', 'workouts.workout_duration', 'workouts.workout_image', 'workouts.workout_gender', 'levels.level_title', 'workout_image_link', 'workouts.workout_description', 'workouts.workout_price', 'workouts.total_workout_like', 'workouts.total_workout_views')
                    ->where('workout_gender', $user->gender)
                    ->join('levels', 'workouts.workout_level', '=', 'levels.level_id')
                    ->join('goals', 'workouts.workout_goal', '=', 'goals.goal_id')
                    ->get();



                $response = [];

                foreach ($workoutdetails as $workout) {
                    $workoutId = $workout->workout_id;
                    $days = [];

                    for ($i = 1; $i <= 7; $i++) {
                        $userdetails = DB::table('we_day' . $i)
                            ->select('exercise_id', 'workout_id', 'day_' . $i)
                            ->where('workout_id', $workoutId)
                            ->get();

                        $exercises = [];
                        $totalRestInSeconds = 0; // Initialize total rest in seconds
                        $totalCalories = 0;

                        foreach ($userdetails as $userdetail) {
                            $exercise_id = $userdetail->exercise_id;

                            $exercise = DB::table('exercises')
                                ->select('exercise_id', 'exercise_rest', 'exercise_calories')
                                ->where('exercise_id', $exercise_id)
                                ->first();

                            if ($exercise) {
                                $exercises[] = $exercise;

                                // Extract the numeric value from the string (assuming format like '20 Sec')
                                $restValue = (int) filter_var($exercise->exercise_rest, FILTER_SANITIZE_NUMBER_INT);

                                // Check if the unit is 'Sec', then add directly
                                if (strpos($exercise->exercise_rest, 'sec') !== false) {
                                    $totalRestInSeconds += $restValue;
                                }

                                $totalCalories += $exercise->exercise_calories;
                            }
                        }

                        $days['day_' . $i] = [
                            'exercises' => $exercises,
                        ];

                        // Adding total rest time and total calories to each day
                        $days['day_' . $i]['total_rest'] = $totalRestInSeconds;
                        $days['day_' . $i]['total_calories'] = $totalCalories;
                    }

                    $workout->days = $days;
                    $response[] = $workout;
                }

                return response()->json(['workout_Data' => $response, 'mindset_workout_data' => $mindsetworkout]);
            } else {
                return response()->json(['error' => 'User not found or gender not specified'], 404);
            }
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }



    public function resendotp(Request $request)
    {
        $email = $request->input('email');

        $user = DB::table('users')->where('email', $email)->first();

        if ($user) {
            $otpTime = now();
            $status = 0;
            $randomNumber = rand(1000, 9999);
            $newtime = time();

            DB::table('users')->where('email', $email)->update([
                'otp_token' => $randomNumber,
                'otp_time' => $otpTime,
            ]);

            $data = ['email' => $email, 'randomNumber' => $randomNumber];

            Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
                $message->to($email)->subject('Verification Code');
            });

            return response()->json(['msg' => 'OTP resent']);
        } else {
            return response()->json(['msg' => 'Email not registered']);
        }
    }


    public function userprofile(Request $request)
    {
        $id = $request->id;
        $name = $request->name;
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        $userData = DB::table('users')->select('id', 'goal', 'age', 'height', 'weight', 'fitness_level', 'workout_plans', 'experience', 'injury', 'target_weight', 'workoutarea', 'equipment', 'focus_area', 'gender', 'name', 'email', 'image', 'login_token', 'profile_compl_status', 'signup_type', 'social_type')
            ->where('id', $id)->first();
        if ($versions_current) {
            if ($userData) {
                // Assuming the images are stored in the public/profile_img directory
                $baseUrl =  $this->base_url . '/adserver/public/profile_image/'; // Base URL for images
                // Check if the user has an image
                if ($userData->image) {
                    $imagePath = $baseUrl . $userData->image; // Creating the full image URL
                    // Add the image path to the user data
                    $userData->image_path = $imagePath;
                } else {
                    // If no image exists for the user, you can set a default image path or leave it empty
                    $userData->image_path = null; // Set a default or empty image path
                }

                $goal_title = DB::table('goals')->select('goal_title')->where('goal_id', $userData->goal)->first();
                $level_title = DB::table('levels')->select('level_title')->where('level_id', $userData->fitness_level)->first();
                $focusarea_title = DB::table('bodyparts')->select('bodypart_title')->where('bodypart_id', $userData->focus_area)->first();


                // Add goal_title directly to $userData
                $userData->goal_title = $goal_title->goal_title ?? null;
                $userData->level_title = $level_title->level_title ?? null;
                $userData->focusarea_title = $focusarea_title->bodypart_title ?? null;

                return response()->json(['profile' => $userData]);
            } else {
                return response()->json(['error' => 'User not found']);
            }
        } elseif ($versions_middle) {
            if ($userData) {
                // Assuming the images are stored in the public/profile_img directory
                $baseUrl =  $this->base_url . '/adserver/public/profile_image/'; // Base URL for images
                // Check if the user has an image
                if ($userData->image) {
                    $imagePath = $baseUrl . $userData->image; // Creating the full image URL
                    // Add the image path to the user data
                    $userData->image_path = $imagePath;
                } else {
                    // If no image exists for the user, you can set a default image path or leave it empty
                    $userData->image_path = null; // Set a default or empty image path
                }

                $goal_title = DB::table('goals')->select('goal_title')->where('goal_id', $userData->goal)->first();
                $level_title = DB::table('levels')->select('level_title')->where('level_id', $userData->fitness_level)->first();
                $focusarea_title = DB::table('bodyparts')->select('bodypart_title')->where('bodypart_id', $userData->focus_area)->first();


                // Add goal_title directly to $userData
                $userData->goal_title = $goal_title->goal_title ?? null;
                $userData->level_title = $level_title->level_title ?? null;
                $userData->focusarea_title = $focusarea_title->bodypart_title ?? null;

                return response()->json(['profile' => $userData]);
            } else {
                return response()->json(['error' => 'User not found']);
            }
        } elseif ($versions_past) {
            if ($userData) {
                // Assuming the images are stored in the public/profile_img directory
                $baseUrl = $this->base_url . '/adserver/public/profile_image/'; // Base URL for images
                // Check if the user has an image
                if ($userData->image) {
                    $imagePath = $baseUrl . $userData->image; // Creating the full image URL
                    // Add the image path to the user data
                    $userData->image_path = $imagePath;
                } else {
                    // If no image exists for the user, you can set a default image path or leave it empty
                    $userData->image_path = null; // Set a default or empty image path
                }

                $goal_title = DB::table('goals')->select('goal_title')->where('goal_id', $userData->goal)->first();
                $level_title = DB::table('levels')->select('level_title')->where('level_id', $userData->fitness_level)->first();
                $focusarea_title = DB::table('bodyparts')->select('bodypart_title')->where('bodypart_id', $userData->focus_area)->first();


                // Add goal_title directly to $userData
                $userData->goal_title = $goal_title->goal_title ?? null;
                $userData->level_title = $level_title->level_title ?? null;
                $userData->focusarea_title = $focusarea_title->bodypart_title ?? null;

                return response()->json(['profile' => $userData]);
            } else {
                return response()->json(['error' => 'User not found']);
            }
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }


    public function usercustomworkout(Request $request)
    {
        $id = $request->id;
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        $userdetails = DB::table('users')->select('*')->where('id', $id)->first();
        if ($versions_current) {
            if ($userdetails && $userdetails->gender && $userdetails->goal && $userdetails->age && $userdetails->focus_area && $userdetails->fitness_level && $userdetails->workoutarea) {
                $focusAreas = explode(',', $userdetails->focus_area);
                // $workoutarea = explode(',', $userdetails->workoutarea);

                $workoutQuery = DB::table('workouts')->select('*')
                    ->where('workout_gender', $userdetails->gender)
                    // ->where('workout_level', $userdetails->fitness_level)
                    // ->whereIn('workout_area', $userdetails->workoutarea)
                    // ->where('workout_goal', $userdetails->goal)
                    ->whereRaw('? BETWEEN workout_minage AND workout_maxage', [$userdetails->age]);
                // ->whereIn('workout_bodypart', $focusAreas);

                // Check if 'injury' is not null, then include it in the query
                // if ($userdetails->injury !== null) {
                //     if ($userdetails->injury === 'Shoulder') {
                //         $workoutQuery->whereNotIn('workout_injury', ['Shoulder', 'Elbow']);
                //     } elseif ($userdetails->injury === 'Elbow') {
                //         $workoutQuery->where('workout_injury', '!=', 'Shoulder');
                //     } elseif ($userdetails->injury === 'Knee') {
                //         $workoutQuery->where('workout_injury', '!=', 'Ankle');
                //     } elseif ($userdetails->injury === 'Ankle') {
                //         $workoutQuery->where('workout_injury', '!=', 'Knee');
                //     }
                // }
                $mindsetworkout = DB::table('workout_mindset')->select('*')
                    // ->where('workout_mindset_level', $userdetails->mind_state)
                    // ->whereRaw('? BETWEEN workout_mindset_minage AND workout_mindset_maxage', [$userdetails->age])
                    // ->whereRaw('? BETWEEN workout_mindset_minsleep AND workout_mindset_maxsleep', [$userdetails->sleep_duration])
                    ->get();

                $workoutdetails = $workoutQuery->get();


                $response = [];

                foreach ($workoutdetails as $workout) {
                    $workoutId = $workout->workout_id;
                    $workout_check = DB::table('workout_history')->select('workout_id', 'user_id')->where('user_id', $id)->where('workout_id', $workoutId)->first();
                    if (empty($workout_check)) {
                        DB::table('workout_history')->insert([
                            'user_id' => $id,
                            'workout_id' => $workoutId,
                            'workout_type' => 'custome_workout'
                        ]);
                    }
                    $days = [];

                    for ($i = 1; $i <= 7; $i++) {
                        $userdetails = DB::table('we_day' . $i)
                            ->select('exercise_id', 'workout_id', 'day_' . $i)
                            ->where('workout_id', $workoutId)
                            ->get();

                        $exercises = [];
                        $totalRestInSeconds = 0; // Initialize total rest in seconds
                        $totalCalories = 0;

                        foreach ($userdetails as $userdetail) {
                            $exercise_id = $userdetail->exercise_id;

                            $exercise = DB::table('exercises')
                                ->select('exercise_id', 'exercise_rest', 'exercise_calories')
                                ->where('exercise_id', $exercise_id)
                                ->first();

                            if ($exercise) {
                                $exercises[] = $exercise;

                                // Extract the numeric value from the string (assuming format like '20 Sec')
                                $restValue = (int) filter_var($exercise->exercise_rest, FILTER_SANITIZE_NUMBER_INT);

                                // Check if the unit is 'Sec', then add directly
                                if (strpos($exercise->exercise_rest, 'sec') !== false) {
                                    $totalRestInSeconds += $restValue;
                                }

                                $totalCalories += $exercise->exercise_calories;
                            }
                        }

                        $days['day_' . $i] = [
                            'exercises' => $exercises,
                        ];

                        // Adding total rest time and total calories to each day
                        $days['day_' . $i]['total_rest'] = $totalRestInSeconds;
                        $days['day_' . $i]['total_calories'] = $totalCalories;
                    }

                    $workout->days = $days;
                    $response[] = $workout;
                }
                // Insert new user details


                return response()->json(['workout' => $response, 'minset_workout' => $mindsetworkout]);
            } else {
                return response()->json(['error' => 'User not found or gender not specified']);
            }
        } elseif ($versions_middle) {
            if ($userdetails && $userdetails->gender && $userdetails->goal && $userdetails->age && $userdetails->focus_area && $userdetails->fitness_level && $userdetails->workoutarea) {
                $focusAreas = explode(',', $userdetails->focus_area);
                // $workoutarea = explode(',', $userdetails->workoutarea);

                $workoutQuery = DB::table('workouts')->select('*')
                    ->where('workout_gender', $userdetails->gender)
                    // ->where('workout_level', $userdetails->fitness_level)
                    // ->whereIn('workout_area', $userdetails->workoutarea)
                    // ->where('workout_goal', $userdetails->goal)
                    ->whereRaw('? BETWEEN workout_minage AND workout_maxage', [$userdetails->age]);
                // ->whereIn('workout_bodypart', $focusAreas);

                // // Check if 'injury' is not null, then include it in the query
                // if ($userdetails->injury !== null) {
                //     if ($userdetails->injury === 'Shoulder') {
                //         $workoutQuery->whereNotIn('workout_injury', ['Shoulder', 'Elbow']);
                //     } elseif ($userdetails->injury === 'Elbow') {
                //         $workoutQuery->where('workout_injury', '!=', 'Shoulder');
                //     } elseif ($userdetails->injury === 'Knee') {
                //         $workoutQuery->where('workout_injury', '!=', 'Ankle');
                //     } elseif ($userdetails->injury === 'Ankle') {
                //         $workoutQuery->where('workout_injury', '!=', 'Knee');
                //     }
                // }
                $mindsetworkout = DB::table('workout_mindset')->select('*')
                    // ->where('workout_mindset_level', $userdetails->mind_state)
                    // ->whereRaw('? BETWEEN workout_mindset_minage AND workout_mindset_maxage', [$userdetails->age])
                    // ->whereRaw('? BETWEEN workout_mindset_minsleep AND workout_mindset_maxsleep', [$userdetails->sleep_duration])
                    ->get();

                $workoutdetails = $workoutQuery->get();


                $response = [];

                foreach ($workoutdetails as $workout) {
                    $workoutId = $workout->workout_id;
                    $workout_check = DB::table('workout_history')->select('workout_id', 'user_id')->where('user_id', $id)->where('workout_id', $workoutId)->first();
                    if (empty($workout_check)) {
                        DB::table('workout_history')->insert([
                            'user_id' => $id,
                            'workout_id' => $workoutId,
                            'workout_type' => 'custome_workout'
                        ]);
                    }
                    $days = [];

                    for ($i = 1; $i <= 7; $i++) {
                        $userdetails = DB::table('we_day' . $i)
                            ->select('exercise_id', 'workout_id', 'day_' . $i)
                            ->where('workout_id', $workoutId)
                            ->get();

                        $exercises = [];
                        $totalRestInSeconds = 0; // Initialize total rest in seconds
                        $totalCalories = 0;

                        foreach ($userdetails as $userdetail) {
                            $exercise_id = $userdetail->exercise_id;

                            $exercise = DB::table('exercises')
                                ->select('exercise_id', 'exercise_rest', 'exercise_calories')
                                ->where('exercise_id', $exercise_id)
                                ->first();

                            if ($exercise) {
                                $exercises[] = $exercise;

                                // Extract the numeric value from the string (assuming format like '20 Sec')
                                $restValue = (int) filter_var($exercise->exercise_rest, FILTER_SANITIZE_NUMBER_INT);

                                // Check if the unit is 'Sec', then add directly
                                if (strpos($exercise->exercise_rest, 'sec') !== false) {
                                    $totalRestInSeconds += $restValue;
                                }

                                $totalCalories += $exercise->exercise_calories;
                            }
                        }

                        $days['day_' . $i] = [
                            'exercises' => $exercises,
                        ];

                        // Adding total rest time and total calories to each day
                        $days['day_' . $i]['total_rest'] = $totalRestInSeconds;
                        $days['day_' . $i]['total_calories'] = $totalCalories;
                    }

                    $workout->days = $days;
                    $response[] = $workout;
                    $jsonData = [
                        'workout_details' => $response,
                        'mindset_workout' => $mindsetworkout,
                    ];

                    // Return the combined data as JSON response
                    return response()->json($jsonData);
                }
                // Insert new user details


                return response()->json(['workout' => $response, 'minset_workout' => $mindsetworkout]);
            } else {
                return response()->json(['error' => 'User not found or gender not specified'], 404);
            }
        } elseif ($versions_past) {
            if ($userdetails && $userdetails->gender && $userdetails->goal && $userdetails->age && $userdetails->focus_area && $userdetails->fitness_level && $userdetails->workoutarea) {
                $focusAreas = explode(',', $userdetails->focus_area);
                // $workoutarea = explode(',', $userdetails->workoutarea);

                $workoutQuery = DB::table('workouts')->select('*')
                    ->where('workout_gender', $userdetails->gender)
                    // ->where('workout_level', $userdetails->fitness_level)
                    // ->whereIn('workout_area', $userdetails->workoutarea)
                    // ->where('workout_goal', $userdetails->goal)
                    ->whereRaw('? BETWEEN workout_minage AND workout_maxage', [$userdetails->age]);
                // ->whereIn('workout_bodypart', $focusAreas);

                // // Check if 'injury' is not null, then include it in the query
                // if ($userdetails->injury !== null) {
                //     if ($userdetails->injury === 'Shoulder') {
                //         $workoutQuery->whereNotIn('workout_injury', ['Shoulder', 'Elbow']);
                //     } elseif ($userdetails->injury === 'Elbow') {
                //         $workoutQuery->where('workout_injury', '!=', 'Shoulder');
                //     } elseif ($userdetails->injury === 'Knee') {
                //         $workoutQuery->where('workout_injury', '!=', 'Ankle');
                //     } elseif ($userdetails->injury === 'Ankle') {
                //         $workoutQuery->where('workout_injury', '!=', 'Knee');
                //     }
                // }
                $mindsetworkout = DB::table('workout_mindset')->select('*')
                    ->where('workout_mindset_level', $userdetails->mind_state)
                    ->whereRaw('? BETWEEN workout_mindset_minage AND workout_mindset_maxage', [$userdetails->age])
                    ->whereRaw('? BETWEEN workout_mindset_minsleep AND workout_mindset_maxsleep', [$userdetails->sleep_duration])
                    ->get();

                $workoutdetails = $workoutQuery->get();


                $response = [];

                foreach ($workoutdetails as $workout) {
                    $workoutId = $workout->workout_id;
                    $workout_check = DB::table('workout_history')->select('workout_id', 'user_id')->where('user_id', $id)->where('workout_id', $workoutId)->first();
                    if (empty($workout_check)) {
                        DB::table('workout_history')->insert([
                            'user_id' => $id,
                            'workout_id' => $workoutId,
                            'workout_type' => 'custome_workout'
                        ]);
                    }
                    $days = [];

                    for ($i = 1; $i <= 7; $i++) {
                        $userdetails = DB::table('we_day' . $i)
                            ->select('exercise_id', 'workout_id', 'day_' . $i)
                            ->where('workout_id', $workoutId)
                            ->get();

                        $exercises = [];
                        $totalRestInSeconds = 0; // Initialize total rest in seconds
                        $totalCalories = 0;

                        foreach ($userdetails as $userdetail) {
                            $exercise_id = $userdetail->exercise_id;

                            $exercise = DB::table('exercises')
                                ->select('exercise_id', 'exercise_rest', 'exercise_calories')
                                ->where('exercise_id', $exercise_id)
                                ->first();

                            if ($exercise) {
                                $exercises[] = $exercise;

                                // Extract the numeric value from the string (assuming format like '20 Sec')
                                $restValue = (int) filter_var($exercise->exercise_rest, FILTER_SANITIZE_NUMBER_INT);

                                // Check if the unit is 'Sec', then add directly
                                if (strpos($exercise->exercise_rest, 'sec') !== false) {
                                    $totalRestInSeconds += $restValue;
                                }

                                $totalCalories += $exercise->exercise_calories;
                            }
                        }

                        $days['day_' . $i] = [
                            'exercises' => $exercises,
                        ];

                        // Adding total rest time and total calories to each day
                        $days['day_' . $i]['total_rest'] = $totalRestInSeconds;
                        $days['day_' . $i]['total_calories'] = $totalCalories;
                    }

                    $workout->days = $days;
                    $response[] = $workout;
                }
                // Insert new user details


                return response()->json(['workout' => $response, 'minset_workout' => $mindsetworkout]);
            } else {
                return response()->json(['error' => 'User not found or gender not specified'], 404);
            }
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }

    public function injury()
    {
        $userData = DB::table('injurys')->select('id', 'injury_title', 'injury_image')->get();

        if ($userData->isNotEmpty()) {
            // Assuming the images are stored in the public/profile_img directory
            $baseImagePath = config('custom.base_image_path');

            // Loop through each user data to add image path
            foreach ($userData as $user) {
                // Check if the user has an image
                if ($user->injury_image) {
                    $user->image_path = $baseImagePath . $user->injury_image; // Creating the full image URL
                } else {
                    // If no image exists for the user, set a default or leave it empty
                    $user->image_path = null; // Set a default or empty image path
                }
            }

            return response()->json(['injuries' => $userData]);
        } else {
            return response()->json(['error' => 'No injuries found'], 404);
        }
    }


    public function goals_levels_focusarea_data()
    {
        // Fetch goals
        $goals = DB::table('goals')->select('*')->get();
        $goals_data = [];

        foreach ($goals as $goal) {
            $goal_id = $goal->goal_id;
            $goal_gender = $goal->gender;
            $goal_title = $goal->goal_title;
            $goal_image = $goal->goal_image_link;

            $goals_data[] = [
                'goal_id' => $goal_id,
                'goal_gender' => $goal_gender,
                'goal_title' => $goal_title,
                'goal_image' => $goal_image
            ];
        }

        // Fetch levels
        $levels = DB::table('levels')->get();
        $levels_data = [];

        foreach ($levels as $level) {
            $level_id = $level->level_id;
            $level_title = $level->level_title;
            $level_rate = $level->level_rate;
            $level_image = $level->level_image_link;
            $level_gender = $level->level_gender;

            $levels_data[] = [
                'level_id' => $level_id,
                'level_title' => $level_title,
                'level_rate' => $level_rate,
                'level_gender' => $level_gender,
                'level_image' => $level_image
            ];
        }

        // Fetch bodyparts
        $bodyparts = DB::table('bodyparts')->get();
        $bodyparts_data = [];

        foreach ($bodyparts as $bodypart) {
            $bodypart_id = $bodypart->bodypart_id;
            $bodypart_title = $bodypart->bodypart_title;
            $bodypart_image = $bodypart->bodypart_image;

            $bodyparts_data[] = [
                'bodypart_id' => $bodypart_id,
                'bodypart_title' => $bodypart_title,
                'bodypart_image' =>  $this->base_url . "/images/" . $bodypart_image,

            ];
        }

        $injurys = DB::table('injurys')->get();
        $injurys_data = [];

        foreach ($injurys as $injury) {
            $injury_id = $injury->id;
            $injury_title = $injury->injury_title;
            $injury_image = $injury->injurys_image_link;

            $injurys_data[] = [
                'injury_id' => $injury_id,
                'injury_title' => $injury_title,
                'injury_image' => $injury_image
            ];
        }

        $workoutareas = DB::table('workoutareas')->get();
        $workoutareas_data = [];

        foreach ($workoutareas as $workoutarea) {
            $workoutarea_id = $workoutarea->id;
            $workoutarea_title = $workoutarea->workoutarea_title;
            $workoutarea_image = $workoutarea->workoutarea_image_link;

            $workoutareas_data[] = [
                'workoutarea_id' => $workoutarea_id,
                'workoutarea_title' => $workoutarea_title,
                'workoutarea_image' => $workoutarea_image
            ];
        }

        // Return the response
        return response()->json([
            'goal' => $goals_data,
            'level' => $levels_data,
            'focusarea' => $bodyparts_data,
            'injury' => $injurys_data,
            'workoutarea' => $workoutareas_data
        ]);
    }


    public function userfreecustomworkout(Request $request)
    {
        $deviceid = $request->deviceid;
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        $userdetails = DB::table('users')->select('*')
            ->where('device_id', $deviceid)
            ->first();
        if ($versions_current) {
            if ($userdetails->gender && $userdetails->goal && $userdetails->age && $userdetails->focus_area && $userdetails->fitness_level && $userdetails->workoutarea) {
                $workoutQuery = DB::table('workouts')->select('*')
                    ->where('workout_gender', $userdetails->gender)
                    ->where('workout_level', $userdetails->fitness_level)
                    ->where('workout_area', $userdetails->workoutarea)
                    ->where('workout_goal', $userdetails->goal)
                    ->whereRaw('? BETWEEN workout_minage AND workout_maxage', [$userdetails->age])
                    ->where('workout_bodypart', $userdetails->focus_area);

                // Check if 'injury' is not null, then include it in the query
                if ($userdetails->injury !== null) {
                    if ($userdetails->injury === 'Shoulder') {
                        $workoutQuery->whereNotIn('workout_injury', ['Shoulder', 'Elbow']);
                    } elseif ($userdetails->injury === 'Elbow') {
                        $workoutQuery->where('workout_injury', '!=', 'Shoulder');
                    } elseif ($userdetails->injury === 'Knee') {
                        $workoutQuery->where('workout_injury', '!=', 'Ankle');
                    } elseif ($userdetails->injury === 'Ankle') {
                        $workoutQuery->where('workout_injury', '!=', 'Knee');
                    }
                }

                $mindsetworkout = DB::table('workout_mindset')->select('*')
                    ->where('workout_mindset_level', $userdetails->mind_state)
                    ->whereRaw('? BETWEEN workout_mindset_minage AND workout_mindset_maxage', [$userdetails->age])
                    ->whereRaw('? BETWEEN workout_mindset_minsleep AND workout_mindset_maxsleep', [$userdetails->sleep_duration])
                    ->get();

                $workoutdetails = $workoutQuery->get();

                $response = [];

                foreach ($workoutdetails as $workout) {
                    $workoutId = $workout->workout_id;
                    $days = [];

                    for ($i = 1; $i <= 7; $i++) {
                        $userdetails = DB::table('we_day' . $i)
                            ->select('exercise_id', 'workout_id', 'day_' . $i)
                            ->where('workout_id', $workoutId)
                            ->get();

                        $exercises = [];
                        $totalRestInSeconds = 0; // Initialize total rest in seconds
                        $totalCalories = 0;

                        foreach ($userdetails as $userdetail) {
                            $exercise_id = $userdetail->exercise_id;

                            $exercise = DB::table('exercises')
                                ->select('exercise_id', 'exercise_rest', 'exercise_calories')
                                ->where('exercise_id', $exercise_id)
                                ->first();

                            if ($exercise) {
                                $exercises[] = $exercise;

                                // Extract the numeric value from the string (assuming format like '20 Sec')
                                $restValue = (int) filter_var($exercise->exercise_rest, FILTER_SANITIZE_NUMBER_INT);

                                // Check if the unit is 'Sec', then add directly
                                if (strpos($exercise->exercise_rest, 'sec') !== false) {
                                    $totalRestInSeconds += $restValue;
                                }

                                $totalCalories += $exercise->exercise_calories;
                            }
                        }

                        $days['day_' . $i] = [
                            'exercises' => $exercises,
                        ];

                        // Adding total rest time and total calories to each day
                        $days['day_' . $i]['total_rest'] = $totalRestInSeconds;
                        $days['day_' . $i]['total_calories'] = $totalCalories;
                    }

                    $workout->days = $days;
                    $response[] = $workout;
                }

                return response()->json(['workout' => $response, 'minset_workout' => $mindsetworkout]);
            } else {
                // Handle a case where some user details are missing
                return response()->json(['error' => 'Incomplete user details']);
            }
        } elseif ($versions_middle) {
            if ($userdetails->gender && $userdetails->goal && $userdetails->age && $userdetails->focus_area && $userdetails->fitness_level && $userdetails->workoutarea) {
                $workoutQuery = DB::table('workouts')->select('*')
                    ->where('workout_gender', $userdetails->gender)
                    ->where('workout_level', $userdetails->fitness_level)
                    ->where('workout_area', $userdetails->workoutarea)
                    ->where('workout_goal', $userdetails->goal)
                    ->whereRaw('? BETWEEN workout_minage AND workout_maxage', [$userdetails->age])
                    ->where('workout_bodypart', $userdetails->focus_area);

                // Check if 'injury' is not null, then include it in the query
                if ($userdetails->injury !== null) {
                    if ($userdetails->injury === 'Shoulder') {
                        $workoutQuery->whereNotIn('workout_injury', ['Shoulder', 'Elbow']);
                    } elseif ($userdetails->injury === 'Elbow') {
                        $workoutQuery->where('workout_injury', '!=', 'Shoulder');
                    } elseif ($userdetails->injury === 'Knee') {
                        $workoutQuery->where('workout_injury', '!=', 'Ankle');
                    } elseif ($userdetails->injury === 'Ankle') {
                        $workoutQuery->where('workout_injury', '!=', 'Knee');
                    }
                }

                $mindsetworkout = DB::table('workout_mindset')->select('*')
                    ->where('workout_mindset_level', $userdetails->mind_state)
                    ->whereRaw('? BETWEEN workout_mindset_minage AND workout_mindset_maxage', [$userdetails->age])
                    ->whereRaw('? BETWEEN workout_mindset_minsleep AND workout_mindset_maxsleep', [$userdetails->sleep_duration])
                    ->get();

                $workoutdetails = $workoutQuery->get();

                $response = [];

                foreach ($workoutdetails as $workout) {
                    $workoutId = $workout->workout_id;
                    $days = [];

                    for ($i = 1; $i <= 7; $i++) {
                        $userdetails = DB::table('we_day' . $i)
                            ->select('exercise_id', 'workout_id', 'day_' . $i)
                            ->where('workout_id', $workoutId)
                            ->get();

                        $exercises = [];
                        $totalRestInSeconds = 0; // Initialize total rest in seconds
                        $totalCalories = 0;

                        foreach ($userdetails as $userdetail) {
                            $exercise_id = $userdetail->exercise_id;

                            $exercise = DB::table('exercises')
                                ->select('exercise_id', 'exercise_rest', 'exercise_calories')
                                ->where('exercise_id', $exercise_id)
                                ->first();

                            if ($exercise) {
                                $exercises[] = $exercise;

                                // Extract the numeric value from the string (assuming format like '20 Sec')
                                $restValue = (int) filter_var($exercise->exercise_rest, FILTER_SANITIZE_NUMBER_INT);

                                // Check if the unit is 'Sec', then add directly
                                if (strpos($exercise->exercise_rest, 'sec') !== false) {
                                    $totalRestInSeconds += $restValue;
                                }

                                $totalCalories += $exercise->exercise_calories;
                            }
                        }

                        $days['day_' . $i] = [
                            'exercises' => $exercises,
                        ];

                        // Adding total rest time and total calories to each day
                        $days['day_' . $i]['total_rest'] = $totalRestInSeconds;
                        $days['day_' . $i]['total_calories'] = $totalCalories;
                    }

                    $workout->days = $days;
                    $response[] = $workout;
                }

                return response()->json(['workout' => $response, 'minset_workout' => $mindsetworkout]);
            } else {
                // Handle a case where some user details are missing
                return response()->json(['error' => 'Incomplete user details']);
            }
        } elseif ($versions_past) {
            if ($userdetails->gender && $userdetails->goal && $userdetails->age && $userdetails->focus_area && $userdetails->fitness_level && $userdetails->workoutarea) {
                $workoutQuery = DB::table('workouts')->select('*')
                    ->where('workout_gender', $userdetails->gender)
                    ->where('workout_level', $userdetails->fitness_level)
                    ->where('workout_area', $userdetails->workoutarea)
                    ->where('workout_goal', $userdetails->goal)
                    ->whereRaw('? BETWEEN workout_minage AND workout_maxage', [$userdetails->age])
                    ->where('workout_bodypart', $userdetails->focus_area);

                // Check if 'injury' is not null, then include it in the query
                if ($userdetails->injury !== null) {
                    if ($userdetails->injury === 'Shoulder') {
                        $workoutQuery->whereNotIn('workout_injury', ['Shoulder', 'Elbow']);
                    } elseif ($userdetails->injury === 'Elbow') {
                        $workoutQuery->where('workout_injury', '!=', 'Shoulder');
                    } elseif ($userdetails->injury === 'Knee') {
                        $workoutQuery->where('workout_injury', '!=', 'Ankle');
                    } elseif ($userdetails->injury === 'Ankle') {
                        $workoutQuery->where('workout_injury', '!=', 'Knee');
                    }
                }

                $mindsetworkout = DB::table('workout_mindset')->select('*')
                    ->where('workout_mindset_level', $userdetails->mind_state)
                    ->whereRaw('? BETWEEN workout_mindset_minage AND workout_mindset_maxage', [$userdetails->age])
                    ->whereRaw('? BETWEEN workout_mindset_minsleep AND workout_mindset_maxsleep', [$userdetails->sleep_duration])
                    ->get();

                $workoutdetails = $workoutQuery->get();

                $response = [];

                foreach ($workoutdetails as $workout) {
                    $workoutId = $workout->workout_id;
                    $days = [];

                    for ($i = 1; $i <= 7; $i++) {
                        $userdetails = DB::table('we_day' . $i)
                            ->select('exercise_id', 'workout_id', 'day_' . $i)
                            ->where('workout_id', $workoutId)
                            ->get();

                        $exercises = [];
                        $totalRestInSeconds = 0; // Initialize total rest in seconds
                        $totalCalories = 0;

                        foreach ($userdetails as $userdetail) {
                            $exercise_id = $userdetail->exercise_id;

                            $exercise = DB::table('exercises')
                                ->select('exercise_id', 'exercise_rest', 'exercise_calories')
                                ->where('exercise_id', $exercise_id)
                                ->first();

                            if ($exercise) {
                                $exercises[] = $exercise;

                                // Extract the numeric value from the string (assuming format like '20 Sec')
                                $restValue = (int) filter_var($exercise->exercise_rest, FILTER_SANITIZE_NUMBER_INT);

                                // Check if the unit is 'Sec', then add directly
                                if (strpos($exercise->exercise_rest, 'sec') !== false) {
                                    $totalRestInSeconds += $restValue;
                                }

                                $totalCalories += $exercise->exercise_calories;
                            }
                        }

                        $days['day_' . $i] = [
                            'exercises' => $exercises,
                        ];

                        // Adding total rest time and total calories to each day
                        $days['day_' . $i]['total_rest'] = $totalRestInSeconds;
                        $days['day_' . $i]['total_calories'] = $totalCalories;
                    }

                    $workout->days = $days;
                    $response[] = $workout;
                }

                return response()->json(['workout' => $response, 'minset_workout' => $mindsetworkout]);
            } else {
                // Handle a case where some user details are missing
                return response()->json(['error' => 'Incomplete user details']);
            }
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }

    public function userfreecustomexercise(Request $request)
    {
        $workoutid = $request->workoutid;
        $workoutgender = $request->workoutgender;
        $workoutgoal = $request->workoutgoal;
        $workoutlevel = $request->workoutlevel;
        $workoutarea = $request->workoutarea;
        $workoutinjury = $request->workoutinjury;
        $workoutage = $request->workoutage;
        $workoutequipment = $request->workoutequipment;

        $goals = DB::table('goals')->select('goal_title')->where('goal_id', $workoutgoal)->first();

        $days = [];
        for ($i = 1; $i <= 7; $i++) {
            $userdetails = DB::table('we_day' . $i)
                ->select('exercise_id', 'workout_id', 'day_' . $i)
                ->where('workout_id', $workoutid)
                ->first();

            if ($userdetails) {
                $exercise_id = $userdetails->exercise_id;

                $exercise = DB::table('exercises')->select('*')
                    ->where('exercise_id', $exercise_id)
                    ->where('exercise_level', $workoutlevel)
                    ->where('exercise_workoutarea', $workoutarea)
                    ->where('exercise_goal', $goals->goal_title)
                    ->whereRaw('? BETWEEN exercise_minage AND exercise_maxage', [$workoutage])
                    ->where('exercise_gender', $workoutgender)
                    ->where('exercise_equipment', $workoutequipment)
                    ->get();

                $days['day_' . $i] = ($exercise->isNotEmpty()) ? $exercise : 'Rest';
            } else {
                $days['day_' . $i] = 'Rest';
            }
        }

        return response()->json(['days' => $days]);
    }

    public function popularWorkout($version)
    {

        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        if ($versions_current) {
            $popularWorkouts = DB::table('favorite')
                ->select('workout_id', DB::raw('count(workout_id) as count'))
                ->groupBy('workout_id')
                ->orderByDesc('count')
                ->limit(2)
                ->get();

            $workoutIds = $popularWorkouts->pluck('workout_id'); // Extract workout IDs from the result

            // Fetch workout details for the top two popular workout IDs
            $workouts = DB::table('workouts')
                ->whereIn('workout_id', $workoutIds)
                ->get();

            if ($workouts->isNotEmpty()) {
                $response = [];

                foreach ($workouts as $workout) {
                    $workoutId = $workout->workout_id;
                    $days = [];

                    for ($i = 1; $i <= 7; $i++) {
                        $userDetails = DB::table('we_day' . $i)
                            ->select('exercise_id', 'workout_id', 'day_' . $i)
                            ->where('workout_id', $workoutId)
                            ->get();

                        $exercises = [];
                        $totalRestInSeconds = 0; // Initialize total rest in seconds
                        $totalCalories = 0;

                        foreach ($userDetails as $userDetail) {
                            $exercise_id = $userDetail->exercise_id;

                            $exercise = DB::table('exercises')
                                ->select('exercise_id', 'exercise_rest', 'exercise_calories')
                                ->where('exercise_id', $exercise_id)
                                ->first();

                            if ($exercise) {
                                $exercises[] = $exercise;

                                // Extract the numeric value from the string (assuming format like '20 Sec')
                                $restValue = (int) filter_var($exercise->exercise_rest, FILTER_SANITIZE_NUMBER_INT);

                                // Check if the unit is 'Sec', then add directly
                                if (strpos($exercise->exercise_rest, 'sec') !== false) {
                                    $totalRestInSeconds += $restValue;
                                }

                                $totalCalories += $exercise->exercise_calories;
                            }
                        }

                        $days['day_' . $i] = [
                            'exercises' => $exercises,
                            'total_rest' => $totalRestInSeconds,
                            'total_calories' => $totalCalories,
                        ];
                    }

                    $workout->days = $days;
                    $response[] = $workout;
                }
                return response()->json($response);
            } else {
                return response()->json(['error' => 'Workouts not found'], 404);
            }
        } elseif ($versions_middle) {
            $popularWorkouts = DB::table('favorite')
                ->select('workout_id', DB::raw('count(workout_id) as count'))
                ->groupBy('workout_id')
                ->orderByDesc('count')
                ->limit(2)
                ->get();

            $workoutIds = $popularWorkouts->pluck('workout_id'); // Extract workout IDs from the result

            // Fetch workout details for the top two popular workout IDs
            $workouts = DB::table('workouts')
                ->whereIn('workout_id', $workoutIds)
                ->get();

            if ($workouts->isNotEmpty()) {
                $response = [];

                foreach ($workouts as $workout) {
                    $workoutId = $workout->workout_id;
                    $days = [];

                    for ($i = 1; $i <= 7; $i++) {
                        $userDetails = DB::table('we_day' . $i)
                            ->select('exercise_id', 'workout_id', 'day_' . $i)
                            ->where('workout_id', $workoutId)
                            ->get();

                        $exercises = [];
                        $totalRestInSeconds = 0; // Initialize total rest in seconds
                        $totalCalories = 0;

                        foreach ($userDetails as $userDetail) {
                            $exercise_id = $userDetail->exercise_id;

                            $exercise = DB::table('exercises')
                                ->select('exercise_id', 'exercise_rest', 'exercise_calories')
                                ->where('exercise_id', $exercise_id)
                                ->first();

                            if ($exercise) {
                                $exercises[] = $exercise;

                                // Extract the numeric value from the string (assuming format like '20 Sec')
                                $restValue = (int) filter_var($exercise->exercise_rest, FILTER_SANITIZE_NUMBER_INT);

                                // Check if the unit is 'Sec', then add directly
                                if (strpos($exercise->exercise_rest, 'sec') !== false) {
                                    $totalRestInSeconds += $restValue;
                                }

                                $totalCalories += $exercise->exercise_calories;
                            }
                        }

                        $days['day_' . $i] = [
                            'exercises' => $exercises,
                            'total_rest' => $totalRestInSeconds,
                            'total_calories' => $totalCalories,
                        ];
                    }

                    $workout->days = $days;
                    $response[] = $workout;
                }
                return response()->json($response);
            } else {
                return response()->json(['error' => 'Workouts not found'], 404);
            }
        } elseif ($versions_past) {
            $popularWorkouts = DB::table('favorite')
                ->select('workout_id', DB::raw('count(workout_id) as count'))
                ->groupBy('workout_id')
                ->orderByDesc('count')
                ->limit(2)
                ->get();

            $workoutIds = $popularWorkouts->pluck('workout_id'); // Extract workout IDs from the result

            // Fetch workout details for the top two popular workout IDs
            $workouts = DB::table('workouts')
                ->whereIn('workout_id', $workoutIds)
                ->get();

            if ($workouts->isNotEmpty()) {
                $response = [];

                foreach ($workouts as $workout) {
                    $workoutId = $workout->workout_id;
                    $days = [];

                    for ($i = 1; $i <= 7; $i++) {
                        $userDetails = DB::table('we_day' . $i)
                            ->select('exercise_id', 'workout_id', 'day_' . $i)
                            ->where('workout_id', $workoutId)
                            ->get();

                        $exercises = [];
                        $totalRestInSeconds = 0; // Initialize total rest in seconds
                        $totalCalories = 0;

                        foreach ($userDetails as $userDetail) {
                            $exercise_id = $userDetail->exercise_id;

                            $exercise = DB::table('exercises')
                                ->select('exercise_id', 'exercise_rest', 'exercise_calories')
                                ->where('exercise_id', $exercise_id)
                                ->first();

                            if ($exercise) {
                                $exercises[] = $exercise;

                                // Extract the numeric value from the string (assuming format like '20 Sec')
                                $restValue = (int) filter_var($exercise->exercise_rest, FILTER_SANITIZE_NUMBER_INT);

                                // Check if the unit is 'Sec', then add directly
                                if (strpos($exercise->exercise_rest, 'sec') !== false) {
                                    $totalRestInSeconds += $restValue;
                                }

                                $totalCalories += $exercise->exercise_calories;
                            }
                        }

                        $days['day_' . $i] = [
                            'exercises' => $exercises,
                            'total_rest' => $totalRestInSeconds,
                            'total_calories' => $totalCalories,
                        ];
                    }

                    $workout->days = $days;
                    $response[] = $workout;
                }
                return response()->json($response);
            } else {
                return response()->json(['error' => 'Workouts not found'], 404);
            }
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }

    public function get_categorie(Request $request)
    {


        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

        if ($versions_current) {
            $dietdata = DB::table('diets')
                ->get();

            foreach ($dietdata as $val) {
                $diet_id = $val->diet_id;
                $diet_title = $val->diet_title;
                $diet_description = $val->diet_description;
                $diet_ingredients = $val->diet_ingredients;
                $diet_category = $val->diet_category;
                $diet_directions = $val->diet_directions;
                $diet_calories = $val->diet_calories;
                $diet_carbs = $val->diet_carbs;
                $diet_protein = $val->diet_protein;
                $diet_fat = $val->diet_fat;
                $diet_time = $val->diet_time;
                $diet_servings = $val->diet_servings;
                $diet_featured = $val->diet_featured;
                $diet_image_link = $val->diet_image_link;
                $diet_status = $val->diet_status;
                $diet_price = $val->diet_price;
                $diet_image = $val->diet_image;

                $diets[] = [
                    'diet_id' => $diet_id,
                    'diet_title' => $diet_title,
                    'diet_description' => $diet_description,
                    'diet_ingredients' => $diet_ingredients,
                    'diet_category' => $diet_category,
                    'diet_direction' => $diet_directions,
                    'diet_calories' => $diet_calories,
                    'diet_carbs' => $diet_carbs,
                    'diet_protein' => $diet_protein,
                    'diet_fat' => $diet_fat,
                    'diet_time' => $diet_time,
                    'diet_servings' => $diet_servings,
                    'diet_featured' => $diet_featured,
                    'diet_image_link' => $diet_image_link,
                    'diet_status' => $diet_status,
                    'diet_price' => $diet_price,
                    'diet_image' =>  $this->base_url . "/images/" . $diet_image,
                ];
            }
        } elseif ($versions_middle) {
            $dietdata = DB::table('diets')
                ->get();

            foreach ($dietdata as $val) {
                $diet_id = $val->diet_id;
                $diet_title = $val->diet_title;
                $diet_description = $val->diet_description;
                $diet_ingredients = $val->diet_ingredients;
                $diet_category = $val->diet_category;
                $diet_directions = $val->diet_directions;
                $diet_calories = $val->diet_calories;
                $diet_carbs = $val->diet_carbs;
                $diet_protein = $val->diet_protein;
                $diet_fat = $val->diet_fat;
                $diet_time = $val->diet_time;
                $diet_servings = $val->diet_servings;
                $diet_featured = $val->diet_featured;
                $diet_image_link = $val->diet_image_link;
                $diet_status = $val->diet_status;
                $diet_price = $val->diet_price;
                $diet_image = $val->diet_image;

                $diets[] = [
                    'diet_id' => $diet_id,
                    'diet_title' => $diet_title,
                    'diet_description' => $diet_description,
                    'diet_ingredients' => $diet_ingredients,
                    'diet_category' => $diet_category,
                    'diet_direction' => $diet_directions,
                    'diet_calories' => $diet_calories,
                    'diet_carbs' => $diet_carbs,
                    'diet_protein' => $diet_protein,
                    'diet_fat' => $diet_fat,
                    'diet_time' => $diet_time,
                    'diet_servings' => $diet_servings,
                    'diet_featured' => $diet_featured,
                    'diet_image_link' => $diet_image_link,
                    'diet_status' => $diet_status,
                    'diet_price' => $diet_price,
                    'diet_image' =>  $this->base_url . "/images/" . $diet_image,
                ];
            }
        } elseif ($versions_past) {
            $dietdata = DB::table('diets')
                ->get();

            foreach ($dietdata as $val) {
                $diet_id = $val->diet_id;
                $diet_title = $val->diet_title;
                $diet_description = $val->diet_description;
                $diet_ingredients = $val->diet_ingredients;
                $diet_category = $val->diet_category;
                $diet_directions = $val->diet_directions;
                $diet_calories = $val->diet_calories;
                $diet_carbs = $val->diet_carbs;
                $diet_protein = $val->diet_protein;
                $diet_fat = $val->diet_fat;
                $diet_time = $val->diet_time;
                $diet_servings = $val->diet_servings;
                $diet_featured = $val->diet_featured;
                $diet_image_link = $val->diet_image_link;
                $diet_status = $val->diet_status;
                $diet_price = $val->diet_price;
                $diet_image = $val->diet_image;

                $diets[] = [
                    'diet_id' => $diet_id,
                    'diet_title' => $diet_title,
                    'diet_description' => $diet_description,
                    'diet_ingredients' => $diet_ingredients,
                    'diet_category' => $diet_category,
                    'diet_direction' => $diet_directions,
                    'diet_calories' => $diet_calories,
                    'diet_carbs' => $diet_carbs,
                    'diet_protein' => $diet_protein,
                    'diet_fat' => $diet_fat,
                    'diet_time' => $diet_time,
                    'diet_servings' => $diet_servings,
                    'diet_featured' => $diet_featured,
                    'diet_image_link' => $diet_image_link,
                    'diet_status' => $diet_status,
                    'diet_price' => $diet_price,
                    'diet_image' =>  $this->base_url . "/images/" . $diet_image,
                ];
            }
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
        return response()->json(['diets' => $diets]);
    }

    public function user_exercise_status(Request $request)
    {
        $user_details_list = $request->input('user_details');
        $type = $request->input('type');
        $insertedData = [];
        $msg = "";
        $datago = 0;
        if(!$type){
            $type=null;
        }
        foreach ($user_details_list as $user_details) {
            // Check if all required keys exist in $user_details
            if (
                isset($user_details['user_id']) &&
                isset($user_details['workout_id']) &&
                isset($user_details['user_exercise_id']) &&
                isset($user_details['user_day'])
            ) {
                // Check if the data already exists
                $existingRecord = DB::table('user_exercise_complete_status')
                    ->where('user_id', $user_details['user_id'])
                    ->where('workout_id', $user_details['workout_id'])
                    ->where('user_exercise_id', $user_details['user_exercise_id'])
                    ->where('user_day', $user_details['user_day'])
                    ->first();

                // $exist = DB::table('user_exercise_complete_status')
                // ->where('user_id', $user_details['user_id'])
                // ->where('workout_id', $user_details['workout_id'])
                // ->where('user_exercise_id', $user_details['user_exercise_id'])
                //   ->first();

                //   if($exist){


                //   }else{

                //   }

                if ($existingRecord) {
                    $check_exercises = DB::table('user_exercise_complete_status')
                        ->where('user_id', $user_details['user_id'])
                        ->where('workout_id', $user_details['workout_id'])
                        ->where('user_exercise_id', $user_details['user_exercise_id'])
                        ->where('user_day', $user_details['user_day'])
                        ->first();
                    if (!$check_exercises) {
                        if ($user_details['user_day'] == -10) {
                            // Perform insert with 'undone' status
                            $insertedRecord = DB::table('user_exercise_complete_status')->insertGetId([
                                'user_id' => $user_details['user_id'],
                                'workout_id' => $user_details['workout_id'],
                                'user_exercise_id' => $user_details['user_exercise_id'],
                                'user_day' => $user_details['user_day'],
                                'exercise_status' => 'undone',
                                'type'         =>$type
                            ]);
                            // Fetch the inserted record and add it to the result array
                            $insertedData[] = DB::table('user_exercise_complete_status')
                                ->where('id', $insertedRecord)
                                ->first();
                        }
                    }
                    $datago = 1;
                    $msg = "User exercise allready exist";
                    // return response()->json(['msg' => 'User exercise allready exist']);
                } else {
                    // Perform insert with 'undone' status
                    $insertedRecord = DB::table('user_exercise_complete_status')->insertGetId([
                        'user_id' => $user_details['user_id'],
                        'workout_id' => $user_details['workout_id'],
                        'user_exercise_id' => $user_details['user_exercise_id'],
                        'user_day' => $user_details['user_day'],
                        'exercise_status' => 'undone',
                        'type'  =>  $type        // Always set the status to 'undone'
                    ]);

                    // Fetch the inserted record and add it to the result array
                    $insertedData[] = DB::table('user_exercise_complete_status')
                        ->where('id', $insertedRecord)
                        ->first();
                    $datago = 2;
                    $msg = "Exercise Status for All Users Inserted Successfully";
                }
            } else {
                $exercise_data = DB::table('user_exercise_complete_status')
                    ->select('*')
                    ->where('user_id', isset($user_details['user_id']))
                    ->get();
                $msg = "Required keys are missing in user_details";
                //return response()->json(['msg' => 'Required keys are missing in user_details',]);
            }
        }
        if ($datago == 2) {
            return response()->json(['msg' => $msg, 'inserted_data' => $insertedData]);
        } elseif ($datago == 1) {
            return response()->json(['msg' => $msg]);
        }
    }



    public function user_details(Request $request)
    {
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        $user_id = $request->user_id;
        $workout_id = $request->workout_id;
        $user_day = $request->user_day;
        if ($versions_current) {
            $user_details = DB::table('user_exercise_complete_status')
                ->select('*')
                ->where('user_id', $user_id)
                ->where('workout_id', $workout_id)
                ->where('user_day', $user_day)
                ->get();
            return response()->json([
                'user_details' => $user_details
            ]);
        } elseif ($versions_middle) {
            $user_details = DB::table('user_exercise_complete_status')
                ->select('*')
                ->where('user_id', $user_id)
                ->where('workout_id', $workout_id)
                ->where('user_day', $user_day)
                ->get();
            return response()->json([
                'user_details' => $user_details
            ]);
        } elseif ($versions_past) {
            $user_details = DB::table('user_exercise_complete_status')
                ->select('*')
                ->where('user_id', $user_id)
                ->where('workout_id', $workout_id)
                ->where('user_day', $user_day)
                ->get();
            return response()->json([
                'user_details' => $user_details
            ]);
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }


    public function user_status(Request $request)
    {
        $day = $request->day;
        $type = $request->type;
        $id = $request->id;
        $workout_id = $request->workout_id;
        $user_id = $request->user_id;
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

        $status = DB::table('user_exercise_complete_status')
            ->select('exercise_status')
            ->where('id', $id)
            ->first();


        if ($versions_current) {
            $status = DB::table('user_exercise_complete_status')
                ->select('exercise_status')
                ->where('id', $id)
                ->where('type',$type)
                ->first();
                
            if ($status) {
                if ($status->exercise_status == 'undone') {
                    // dd('undone');
                    DB::table('user_exercise_complete_status')
                        ->where('id', $id)
                        ->where('type',$type)
                        ->update(['exercise_status' => 'completed']);
                    $start_date = Carbon::now()->startOfWeek();  // Start of the current week
                    $end_date = Carbon::now();
                    // Check for all exercises completed for a particular day

                    $day_statuses = DB::table('user_exercise_complete_status')
                        ->select('user_day', 'exercise_status')
                        ->where('user_day', $day)
                        ->where('workout_id', $workout_id)
                        ->where('type',$type)
                        ->where('user_id', $user_id)
                        ->get();

                    $all_completed = $day_statuses->every(function ($day_status) {
                        return $day_status->exercise_status == 'completed';
                    });

                    if ($all_completed) {
                        DB::table('user_exercise_complete_status')
                            ->where('user_day', $day)
                            ->where('workout_id', $workout_id)
                            ->where('user_id', $user_id)
                            ->where('type',$type)
                            ->update(['final_status' => 'allcompleted']);
                        // Add a response here if needed for successful update of final_status
                    }
                    // $day_statuses = DB::table('user_exercise_complete_status')
                    //     ->select('user_day', 'exercise_status')
                    //     ->where('user_day', $day)
                    //     ->where('workout_id', $workout_id)
                    //     ->where('user_id', $user_id)
                    //     ->whereBetween('created_at', [$start_date, $end_date])
                    //     ->get();



                    // $all_completed = $day_statuses->every(function ($day_status) {
                    //     return $day_status->exercise_status = 'completed';
                    // });



                    // if ($all_completed) {
                    //     DB::table('user_exercise_complete_status')
                    //         ->where('user_day', $day)
                    //         ->where('workout_id', $workout_id)
                    //         ->where('user_id', $user_id)
                    //         ->update(['final_status' => 'allcompleted']);
                    //     // Add a response here if needed for successful update of final_status
                    // }

                    return response()->json(['msg' => 'Exercise Status Updated to Completed']);
                } elseif ($status->exercise_status == 'completed') {
                    //   if ($day == -11 || $day == -12) {
                    //         DB::table('user_exercise_complete_status')
                    //             ->insert([
                    //                 'workout_id' => $workout_id,
                    //                 'user_day' => $day,
                    //                 'exercise_status' => 'completed'
                    //             ]);
                    //         }


                    return response()->json(['msg' => 'Exercise Status is Already Completed']);
                } else {
                    return response()->json(['msg' => 'Invalid Exercise Status']);
                }
            } else {
                return response()->json(['msg' => 'No Exercise Status Found for the given ID']);
            }
        } elseif ($versions_middle) {
            if ($status) {
                if ($status->exercise_status == 'undone') {
                    DB::table('user_exercise_complete_status')
                        ->where('id', $id)
                        ->update(['exercise_status' => 'completed']);

                    // Check for all exercises completed for a particular day
                    $day_statuses = DB::table('user_exercise_complete_status')
                        ->select('user_day', 'exercise_status')
                        ->where('user_day', $day)
                        ->where('workout_id', $workout_id)
                        ->where('user_id', $user_id)
                        ->get();

                    $all_completed = $day_statuses->every(function ($day_status) {
                        return $day_status->exercise_status == 'completed';
                    });

                    if ($all_completed) {
                        DB::table('user_exercise_complete_status')
                            ->where('user_day', $day)
                            ->where('workout_id', $workout_id)
                            ->where('user_id', $user_id)
                            ->update(['final_status' => 'allcompleted']);
                        // Add a response here if needed for successful update of final_status
                    }

                    return response()->json(['msg' => 'Exercise Status Updated to Completed']);
                } elseif ($status->exercise_status == 'completed') {
                    return response()->json(['msg' => 'Exercise Status is Already Completed']);
                } else {
                    return response()->json(['msg' => 'Invalid Exercise Status']);
                }
            } else {
                return response()->json(['msg' => 'No Exercise Status Found for the given ID']);
            }
        } elseif ($versions_past) {
            if ($status) {
                if ($status->exercise_status == 'undone') {
                    DB::table('user_exercise_complete_status')
                        ->where('id', $id)
                        ->update(['exercise_status' => 'completed']);

                    // Check for all exercises completed for a particular day
                    $day_statuses = DB::table('user_exercise_complete_status')
                        ->select('user_day', 'exercise_status')
                        ->where('user_day', $day)
                        ->where('workout_id', $workout_id)
                        ->where('user_id', $user_id)
                        ->get();

                    $all_completed = $day_statuses->every(function ($day_status) {
                        return $day_status->exercise_status == 'completed';
                    });

                    if ($all_completed) {
                        DB::table('user_exercise_complete_status')
                            ->where('user_day', $day)
                            ->where('workout_id', $workout_id)
                            ->where('user_id', $user_id)
                            ->update(['final_status' => 'allcompleted']);
                        // Add a response here if needed for successful update of final_status
                    }

                    return response()->json(['msg' => 'Exercise Status Updated to Completed']);
                } elseif ($status->exercise_status == 'completed') {
                    return response()->json(['msg' => 'Exercise Status is Already Completed']);
                } else {
                    return response()->json(['msg' => 'Invalid Exercise Status']);
                }
            } else {
                return response()->json(['msg' => 'No Exercise Status Found for the given ID']);
            }
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }

    public function user_exercise_details(Request $request)
    {
        $id = $request->id;
        $workout_id = $request->workout_id;
        $user_details = DB::table('user_exercise_complete_status')
            ->select('id', 'user_day', 'exercise_status', 'final_status')
            ->where('user_id', $id)
            ->where('workout_id', $workout_id)
            ->get();

        if ($user_details->isEmpty()) {
            return response()->json(['msg' => 'No data found']);
        }

        return response()->json([
            'user_details' => $user_details

        ]);
    }


    public function user_exercise_record(Request $request)
    {
        $id = $request->id;
        $workout_id = $request->workout_id;
        $user_records = DB::table('user_exercise_complete_status')
            ->select('id', 'user_id', 'user_day', 'final_status')
            ->where('user_id', $id)
            ->where('workout_id', $workout_id)
            ->get();

        if ($user_records->isNotEmpty()) {
            return response()->json(['status' => $user_records]);
        } else {
            return response()->json(['msg' => 'No data found']);
        }
    }

    public function workout_status(Request $request)
    {
        $token = $request->token;
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        $user = DB::table('users')->where('login_token', $token)->first();
        if ($versions_current) {
            if ($user) {
                $user_id = $user->id; // Get the user ID from the authenticated user
                $workout_ids = DB::table('user_exercise_complete_status')
                    ->select('workout_id')
                    ->where('user_id', $user_id)
                    ->where('user_day', 7)
                    ->where('final_status', 'allcompleted')
                    ->pluck('workout_id');

                if ($workout_ids->isNotEmpty()) {
                    // Return all workout_ids where user_day is 7 and final_status is allcompleted
                    return response()->json(['workout_ids' => $workout_ids]);
                } else {
                    return response()->json(['msg' => 'No Completed Workouts Found']);
                }
            } else {
                return response()->json(['msg' => 'Invalid token']);
            }
        } elseif ($versions_middle) {
            if ($user) {
                $user_id = $user->id; // Get the user ID from the authenticated user
                $workout_ids = DB::table('user_exercise_complete_status')
                    ->select('workout_id')
                    ->where('user_id', $user_id)
                    ->where('user_day', 7)
                    ->where('final_status', 'allcompleted')
                    ->pluck('workout_id');

                if ($workout_ids->isNotEmpty()) {
                    // Return all workout_ids where user_day is 7 and final_status is allcompleted
                    return response()->json(['workout_ids' => $workout_ids]);
                } else {
                    return response()->json(['msg' => 'No Completed Workouts Found']);
                }
            } else {
                return response()->json(['msg' => 'Invalid token']);
            }
        } elseif ($versions_past) {
            if ($user) {
                $user_id = $user->id; // Get the user ID from the authenticated user
                $workout_ids = DB::table('user_exercise_complete_status')
                    ->select('workout_id')
                    ->where('user_id', $user_id)
                    ->where('user_day', 7)
                    ->where('final_status', 'allcompleted')
                    ->pluck('workout_id');

                if ($workout_ids->isNotEmpty()) {
                    // Return all workout_ids where user_day is 7 and final_status is allcompleted
                    return response()->json(['workout_ids' => $workout_ids]);
                } else {
                    return response()->json(['msg' => 'No Completed Workouts Found']);
                }
            } else {
                return response()->json(['msg' => 'Invalid token']);
            }
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }

    public function steps_details(Request $request)
    {
        $user_id = $request->user_id;
        $steps = $request->steps;
        $calories = $request->calories;
        $distance = $request->distance;
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

        // Insert new user details
        if ($versions_current) {
            $checkdata = DB::table('user_stepcounts_details')
                ->where('user_id', $user_id)
                ->whereDate('created_at', '=', Carbon::today()->toDateString())
                ->get();

            if ($checkdata->isEmpty()) {
                DB::table('user_stepcounts_details')->insert([
                    'user_id' => $user_id,
                    'steps' => $steps,
                    'calories' => $calories,
                    'distance' => $distance
                ]);
            } else {
                DB::table('user_stepcounts_details')
                    ->where('user_id', $user_id)
                    ->whereDate('created_at', '=', Carbon::today()->toDateString())
                    ->update([
                        'user_id' => $user_id,
                        'steps' => $steps,
                        'calories' => $calories,
                        'distance' => $distance
                    ]);
            }
            return response()->json(['msg' => 'New User Inserted Successfully']);
        } elseif ($versions_middle) {
            $checkdata = DB::table('user_stepcounts_details')
                ->where('user_id', $user_id)
                ->whereDate('created_at', '=', Carbon::today()->toDateString())
                ->get();

            if ($checkdata->isEmpty()) {
                DB::table('user_stepcounts_details')->insert([
                    'user_id' => $user_id,
                    'steps' => $steps,
                    'calories' => $calories,
                    'distance' => $distance
                ]);
            } else {
                DB::table('user_stepcounts_details')
                    ->where('user_id', $user_id)
                    ->whereDate('created_at', '=', Carbon::today()->toDateString())
                    ->update([
                        'user_id' => $user_id,
                        'steps' => $steps,
                        'calories' => $calories,
                        'distance' => $distance
                    ]);
            }
            // DB::table('user_stepcounts_details')->insert([
            //     'user_id' => $user_id,
            //     'steps' => $steps,
            //     'calories' => $calories,
            //     'distance' => $distance
            // ]);

            return response()->json(['msg' => 'New User Inserted Successfully']);
        } elseif ($versions_past) {
            // DB::table('user_stepcounts_details')->insert([
            //     'user_id' => $user_id,
            //     'steps' => $steps,
            //     'calories' => $calories,
            //     'distance' => $distance
            // ]);
            $checkdata = DB::table('user_stepcounts_details')
                ->where('user_id', $user_id)
                ->whereDate('created_at', '=', Carbon::today()->toDateString())
                ->get();

            if ($checkdata->isEmpty()) {
                DB::table('user_stepcounts_details')->insert([
                    'user_id' => $user_id,
                    'steps' => $steps,
                    'calories' => $calories,
                    'distance' => $distance
                ]);
            } else {
                DB::table('user_stepcounts_details')
                    ->where('user_id', $user_id)
                    ->whereDate('created_at', '=', Carbon::today()->toDateString())
                    ->update([
                        'user_id' => $user_id,
                        'steps' => $steps,
                        'calories' => $calories,
                        'distance' => $distance
                    ]);
            }

            return response()->json(['msg' => 'New User Inserted Successfully']);
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }


    public function history(Request $request)
    {
        $user_id = $request->user_id;
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        $endDate = now();

        // Weekly data
        if ($versions_current) {
            $weeklyStartDate = now()->subDays(7);
            $weeklyData = DB::table('weight_burn_status')
                ->select('*')
                ->where('user_id', $user_id)
                ->whereBetween('created_at', [$weeklyStartDate, $endDate])
                ->get();

            // Monthly data
            $monthlyStartDate = now()->subDays(31);
            $monthlyData = DB::table('weight_burn_status')
                ->select('*')
                ->where('user_id', $user_id)
                ->whereBetween('created_at', [$monthlyStartDate, $endDate])
                ->get();

            // Format data
            $formattedWeeklyData = $weeklyData->map(function ($item) {
                $item->created_at = Carbon::parse($item->created_at)->toDateString();
                return $item;
            });

            $formattedMonthlyData = $monthlyData->map(function ($item) {
                $item->created_at = Carbon::parse($item->created_at)->toDateString();
                return $item;
            });

            // Return response
            return response()->json([
                'weekly_data' => $formattedWeeklyData,
                'monthly_data' => $formattedMonthlyData,
            ]);
        } elseif ($versions_middle) {
            $weeklyStartDate = now()->subDays(7);
            $weeklyData = DB::table('weight_burn_status')
                ->select('*')
                ->where('user_id', $user_id)
                ->whereBetween('created_at', [$weeklyStartDate, $endDate])
                ->get();

            // Monthly data
            $monthlyStartDate = now()->subDays(31);
            $monthlyData = DB::table('weight_burn_status')
                ->select('*')
                ->where('user_id', $user_id)
                ->whereBetween('created_at', [$monthlyStartDate, $endDate])
                ->get();

            // Format data
            $formattedWeeklyData = $weeklyData->map(function ($item) {
                $item->created_at = Carbon::parse($item->created_at)->toDateString();
                return $item;
            });

            $formattedMonthlyData = $monthlyData->map(function ($item) {
                $item->created_at = Carbon::parse($item->created_at)->toDateString();
                return $item;
            });

            // Return response
            return response()->json([
                'weekly_data' => $formattedWeeklyData,
                'monthly_data' => $formattedMonthlyData,
            ]);
        } elseif ($versions_past) {
            $weeklyStartDate = now()->subDays(7);
            $weeklyData = DB::table('weight_burn_status')
                ->select('*')
                ->where('user_id', $user_id)
                ->whereBetween('created_at', [$weeklyStartDate, $endDate])
                ->get();

            // Monthly data
            $monthlyStartDate = now()->subDays(31);
            $monthlyData = DB::table('weight_burn_status')
                ->select('*')
                ->where('user_id', $user_id)
                ->whereBetween('created_at', [$monthlyStartDate, $endDate])
                ->get();

            // Format data
            $formattedWeeklyData = $weeklyData->map(function ($item) {
                $item->created_at = Carbon::parse($item->created_at)->toDateString();
                return $item;
            });

            $formattedMonthlyData = $monthlyData->map(function ($item) {
                $item->created_at = Carbon::parse($item->created_at)->toDateString();
                return $item;
            });

            // Return response
            return response()->json([
                'weekly_data' => $formattedWeeklyData,
                'monthly_data' => $formattedMonthlyData,
            ]);
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }


    public function selectDate_exercise(Request $request)
    {
        $user_id = $request->user_id;
        $date = $request->date;
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        if ($versions_current) {
            $completedExercises = DB::table('user_exercise_complete_status')
                ->join('exercises', 'user_exercise_complete_status.user_exercise_id', '=', 'exercises.exercise_id')
                ->where('user_id', $user_id)
                ->where('exercise_status', 'completed')
                ->where(DB::raw('DATE(user_exercise_complete_status.created_at)'), $date)
                ->get();

            $stepsdata = DB::table('user_stepcounts_details')
                ->select('*')
                ->where('user_id', $user_id)
                ->where(DB::raw('DATE(created_at)'), $date)
                ->get();

            if (empty($completedExercises) && empty($stepsdata)) {
                return response()->json(['message' => 'No data found for the given date']);
            }

            return response()->json(['data' => $completedExercises, 'steps' => $stepsdata]);
        } elseif ($versions_middle) {
            $completedExercises = DB::table('user_exercise_complete_status')
                ->join('exercises', 'user_exercise_complete_status.user_exercise_id', '=', 'exercises.exercise_id')
                ->where('user_id', $user_id)
                ->where('exercise_status', 'completed')
                ->where(DB::raw('DATE(user_exercise_complete_status.created_at)'), $date)
                ->get();

            $stepsdata = DB::table('user_stepcounts_details')
                ->select('*')
                ->where('user_id', $user_id)
                ->where(DB::raw('DATE(created_at)'), $date)
                ->get();

            if (empty($completedExercises) && empty($stepsdata)) {
                return response()->json(['message' => 'No data found for the given date']);
            }

            return response()->json(['data' => $completedExercises, 'steps' => $stepsdata]);
        } elseif ($versions_past) {
            $completedExercises = DB::table('user_exercise_complete_status')
                ->join('exercises', 'user_exercise_complete_status.user_exercise_id', '=', 'exercises.exercise_id')
                ->where('user_id', $user_id)
                ->where('exercise_status', 'completed')
                ->where(DB::raw('DATE(user_exercise_complete_status.created_at)'), $date)
                ->get();

            $stepsdata = DB::table('user_stepcounts_details')
                ->select('*')
                ->where('user_id', $user_id)
                ->where(DB::raw('DATE(created_at)'), $date)
                ->get();

            if (empty($completedExercises) && empty($stepsdata)) {
                return response()->json(['message' => 'No data found for the given date']);
            }

            return response()->json(['data' => $completedExercises, 'steps' => $stepsdata]);
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }

    public function deleteAccount(Request $request)
    {
   
        $email_id = $request->input('email');
        $id = $request->input('id');
        
       if ($id) {
            $userData = DB::table("users")->where('id', $id)->first();
        
            if ($userData) {
                DB::table('deleted_account')->insert([
                    'employee_id' => $id,
                    'email' => $userData->email,
                    'name' =>$userData->name,
                ]);
            }
       }
        

        if($email_id){
           $password = $request->input('password');
          
        $check_email =  DB::table('users')->where('email',$email_id)->first();
        if($check_email){
             $check_password =  DB::table('users')->where('email',$email_id)->where('password',$password)->first();
             if(!$check_password){
                   return response()->json(['data' => 'Wrong Password']);
             }
            
            
             $deleteAccount = DB::table('users')
            ->where('email', $email_id)
            ->delete();

       return response()->json(['data' => 'Account Successfully deleted']);
        }
    
      return response()->json(['data' => "your email id doesn't exist"]);
            
        }
        $deleteAccount = DB::table('users')
            ->where('id', $id)
            ->delete();

        return response()->json(['data' => 'Account Successfully deleted']);
    }
    
    public function like_dislike(Request $request)
    {
        $user_id = $request->input('user_id');
        $workout_id = $request->input('workout_id');
        $workout = DB::table('workouts')
            ->where('workout_id', $workout_id)
            ->first();

        if (!$workout) {
            return response()->json(['msg' => 'Workout does not exist']);
        }
        $existing_like = DB::table('workout_like_view')
            ->where('user_id', $user_id)
            ->first();
        if ($existing_like) {
            $existing_workout_ids = $existing_like->workout_id;
            $existing_workout_ids_array = explode(',', $existing_workout_ids);

            if (in_array($workout_id, $existing_workout_ids_array)) {
                $key = array_search($workout_id, $existing_workout_ids_array);
                unset($existing_workout_ids_array[$key]);
                $updated_workout_ids = implode(',', $existing_workout_ids_array);
                DB::table('workout_like_view')
                    ->where('user_id', $user_id)
                    ->update(['workout_id' => $updated_workout_ids]);

                DB::table('workouts')
                    ->where('workout_id', $workout_id)
                    ->update(['total_workout_like' => DB::raw('total_workout_like - 1')]);

                return response()->json(['msg' => 'Workout removed from like']);
            } else {
                $updated_workout_ids = $existing_workout_ids . ',' . $workout_id;
                DB::table('workout_like_view')
                    ->where('user_id', $user_id)
                    ->update(['workout_id' => $updated_workout_ids]);

                DB::table('workouts')
                    ->where('workout_id', $workout_id)
                    ->update(['total_workout_like' => DB::raw('total_workout_like + 1')]);

                return response()->json(['msg' => 'Workout added to like']);
            }
        } else {
            DB::table('workout_like_view')->insert([
                'workout_id' => $workout_id,
                'user_id' => $user_id,
            ]);

            DB::table('workouts')
                ->where('workout_id', $workout_id)
                ->update(['total_workout_like' => DB::raw('total_workout_like + 1')]);


            return response()->json(['msg' => 'Workout added to like']);
        }
    }



    public function total_like_view(Request $request)
    {
        $user_id = $request->input('user_id');
        $user_like = DB::table('workout_like_view')
            ->select('workout_id', 'user_id')
            ->where('user_id', $user_id)
            ->get();

        return response()->json(['user_like' => $user_like]);
    }


    public function workout_view_count(Request $request)
    {
        $workout_id = $request->input('workout_id');

        $workout = DB::table('workouts')
            ->where('workout_id', $workout_id)
            ->first();

        if (!$workout) {
            return response()->json(['msg' => 'Workout does not exist']);
        } else {
            DB::table('workouts')
                ->where('workout_id', $workout_id)
                ->update(['total_workout_views' => DB::raw('total_workout_views + 1')]);

            return response()->json(['msg' => 'Workout views ']);
        }
    }



    public function single_exercise_status(Request $request)
    {
        $day = $request->day;
        $workout_id = $request->workout_id;
        $exercise_id = $request->exercise_id;
        $user_id = $request->user_id;
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

        if ($versions_current) {
            
            // -11 or -12 used for focus area or categories

            if ($day == -11 || $day == -12) {
                $currentDate = Carbon::now()->toDateString();
                $check_data = DB::table('user_exercise_complete_status')
                    ->where('user_id', $user_id)
                    ->where('workout_id', $workout_id)
                    ->where('user_exercise_id', $exercise_id)
                    ->where('user_day', $day)
                    ->whereDate('created_at', $currentDate)
                    ->first();

                if ($check_data) {
                    return response()->json([
                        'msg' => 'data allrady exist'
                    ]);
                }

                DB::table('user_exercise_complete_status')
                    ->insert([
                        'workout_id' => $workout_id,
                        'user_day' => $day,
                        'user_id' => $user_id,
                        'user_exercise_id' => $exercise_id,
                        'exercise_status' => 'completed'
                    ]);
                return response()->json([
                    'msg' => 'data inserted'
                ]);
            }
            return response()->json([
                'msg' => 'data  not inserted'

            ]);
        } elseif ($versions_middle) {
            
              if ($day == -11 || $day == -12) {
                $currentDate = Carbon::now()->toDateString();
                $check_data = DB::table('user_exercise_complete_status')
                    ->where('user_id', $user_id)
                    ->where('workout_id', $workout_id)
                    ->where('user_exercise_id', $exercise_id)
                    ->where('user_day', $day)
                    ->whereDate('created_at', $currentDate)
                    ->first();

                if ($check_data) {
                    return response()->json([
                        'msg' => 'data allrady exist'
                    ]);
                }

                DB::table('user_exercise_complete_status')
                    ->insert([
                        'workout_id' => $workout_id,
                        'user_day' => $day,
                        'user_id' => $user_id,
                        'user_exercise_id' => $exercise_id,
                        'exercise_status' => 'completed'
                    ]);
                return response()->json([
                    'msg' => 'data inserted'
                ]);
            }
            return response()->json([
                'msg' => 'data  not inserted'

            ]);
        } elseif ($versions_past) {
              if ($day == -11 || $day == -12) {
                $currentDate = Carbon::now()->toDateString();
                $check_data = DB::table('user_exercise_complete_status')
                    ->where('user_id', $user_id)
                    ->where('workout_id', $workout_id)
                    ->where('user_exercise_id', $exercise_id)
                    ->where('user_day', $day)
                    ->whereDate('created_at', $currentDate)
                    ->first();

                if ($check_data) {
                    return response()->json([
                        'msg' => 'data allrady exist'
                    ]);
                }

                DB::table('user_exercise_complete_status')
                    ->insert([
                        'workout_id' => $workout_id,
                        'user_day' => $day,
                        'user_id' => $user_id,
                        'user_exercise_id' => $exercise_id,
                        'exercise_status' => 'completed'
                    ]);
                return response()->json([
                    'msg' => 'data inserted'
                ]);
            }
            return response()->json([
                'msg' => 'data  not inserted'

            ]);
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }
    public function well_known_assetlinks_json()
    {
        $data[] = [
            "relation" => ["delegate_permission/common.handle_all_urls"],
            "target"   => [
                "namespace"                => "android_app",
                "package_name"             => "fitme.health.fitness.homeworkouts.equipment",
                "sha256_cert_fingerprints" => [
                    "D2:6E:2A:67:A5:54:8E:39:6B:DF:8F:A4:BA:39:C3:6F:59:0C:DF:EC:B1:ED:E0:20:20:9D:76:02:D7:4F:AE:67"
                ]
            ]
        ];

        return response()->json($data);
    }

    public function apple_app_site_association()
    {
        return '{"applinks":{"apps":[],"details":[{"appID":"277GPQ33HC.fitme.health.fitness.homeworkouts.equipment","paths":["NOT /_/*","/*"]}]}}';
        // return '{"applinks":{"apps":[],"details":[{"appID":"277GPQ33HC.fitme.health.fitness.homeworkouts.equipment","paths":["NOT /_/*","/*"]}]}}';
    }


    public function delete_wallet_data()
    {
        dd('asdfh');
    }

    public function delete_completed_exercises(Request $request)
    {

        $currentDate = Carbon::today();
        $oneWeekAgo = $currentDate->copy()->subDays(7);

        // dd($oneWeekAgo);

        $days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        $deletedRowsForDay = DB::table('user_exercise_complete_status')
            // ->whereDate('created_at', '>=', $oneWeekAgo) // Get records from one week ago or later
            // ->whereDate('created_at', '<=', $currentDate)
            ->whereIn('user_day', $days) // Use the array of days directly
            // ->where('exercise_status', 'completed')
            ->delete();

        print_r($deletedRowsForDay);

        // foreach($deletedRowsForDay as $data){
        //     print_r($data->user_day);

        // }

    }
    
    public function music_details()
    {
        
      $music = DB::table('music')
            ->select('title','music_file','type')
            ->get();
            
        return response()->json($music);
        
    }
    public function deleteGoogleAccount(Request $request)
    {
        
       
          $email_id = $request->input('email');

            $user = DB::table('users')->where('email', $email_id)->first();

            if (!$user) {
                return response()->json([
                    'data' => "your email id doesn't exist"
                ]);
            }
      
            if ($user->social_type != 'google') {
                return response()->json([
                    // 'success' => false,
                    'data' => 'Your account was not created using Google login'
                ]);
            }

            DB::table('users')
                ->where('email', $email_id)
                ->where('social_type', 'google')
                ->delete();
        
            return response()->json([
                // 'success' => true,
                'data' => 'Account successfully deleted'
            ]);
    
    }
    
    
    public function loginnew(Request $request)
    {
            $email = $request->input('email');
            $name = $request->input('name');
            $device_id = $request->input('device_id');
            $platform = $request->input('platform');
            $loginToken = rand(100000000000, 999999999999999);
            
            $version = $request->input('version');
            
            if (empty($email) || empty($name) || empty($platform) || empty($version)) {
                return response()->json([
                    'status' => false,
                    'message' => 'All fields (email, name, platform, version) are required.'
                ]);
            }
            
            if ($email == "undefined" || $email === '0' || $email === 0 || empty($email)) {
                      return response()->json([
                        'status' => false,
                        'message' => 'All fields (email, name, platform, version) are required.'
                    ]);
                    }

        
            $check_user = DB::table('users')
                ->where('email', $email)
                ->where('device_id', $device_id)
                ->first();
               
        
            $check_registration_status = DB::table('users')
                ->where('email', $email)
                ->where('device_id', $device_id)
                ->where('profile_compl_status', 1)
                ->first();
              
            $term =  false;
            
        
            $userWithDevice = DB::table('users')->where('device_id', $device_id)->first();
            $userWithEmail = DB::table('users')->where('email', $email)->first();
            
            // $devicecount = DB::table('users')->where('device_id', $device_id)->count();
            // $emailcount = DB::table('users')->where('email', $email)->count();
            // dd($devicecount);
            
                $diff_user_id = DB::table("users")
                    ->where("device_id", $device_id)
                    ->where("email", "!=", $email)
                    ->first();
                
                if ($diff_user_id) {
                     return response()->json([
                        'status' => true,
                        'allcompleted' => false,
                         'email' => $diff_user_id->email,
                        'message' => 'this email is allrady register with another device ',
                        'term'=>$term
                    ]);
                    
                } 
            
        
            // Check for version information
            $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
            $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
            $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        
            // Fix incorrect assignments
            if ($name === 'undefined' || $name == 0 || $name == '0') {
                $name = null;
            }
        
            if ($versions_current) {
              
                $check_data = DB::table('users')
                    ->where(function ($query) use ($email, $device_id) {
                        $query->where('email', $email)
                              ->orWhere('device_id', $device_id);
                    })
                    ->first();
                   
                    
                    
                if(!$check_data){
                    $data = DB::table('users')->insertGetId([
                        'name'      => $name,
                        'email'     => $email,
                        'login_token' => $loginToken,
                        'device_id' => $device_id,
                        'platform'  => $platform,
                    ]);
                         
                      return response()->json([
                        'status' => true,
                        'allcompleted' => false,
                        'user_id'=>$data,
                        'message' => 'new user created',
                        'term'=>$term
                    ]);
                    
                }
                
                $check_user = DB::table('users')
                ->where('email', $email)
                ->where('device_id', $device_id)
                ->first();
                
                $check_registration_status = DB::table('users')
                    ->where('email', $email)
                    ->where('device_id', $device_id)
                    ->where('profile_compl_status', 1)
                    ->first();
                
                    $term =  true;
                if ($check_registration_status) {
                    if($check_registration_status->term_and_conditions=='Accepted'){
                        
                         DB::table('users')
                        ->where('email', $email)
                        ->where('device_id', $device_id)
                        ->update([
                              'login_token' => $loginToken,
                           ]);
                           
                          return response()->json([
                            'status' => true,
                            'allcompleted' => true,
                            'user_id'=>$check_registration_status->id,
                            'message' => 'You are logged in',
                            'term'=>$term
                        ]);
                        
                    }
                  
                } elseif ($check_user) {
                    return response()->json([
                        'status' => true,
                        'allcompleted' => false,
                        'user_id'=>$check_user->id,
                        'message' => 'Profile completion status is 0',
                        'term'=>$term
                    ]);
                } elseif ($userWithDevice || $userWithEmail) {
                   
                    if ($check_data) {
                        
                        if (!$userWithDevice) {
                            
                           $data = DB::table('users')->where('email', $email)->update([
                                'device_id' => $device_id
                            ]);
                        }
                        if (!$userWithEmail) {
                        
                           $data = DB::table('users')->where('device_id', $device_id)->update([
                                'email' => $email
                            ]);
                        }
                    
                          
                    $check_status= DB::table('users')
                        ->where('email', $email)
                        ->where('device_id', $device_id)
                        ->first();
                       
                        
                        if($check_status->profile_compl_status==0){
                            return response()->json([
                            'status' => true,
                            'allcompleted' => false,
                            'user_id'=>$check_status->id,
                            'message' => 'Profile completion status is 0',
                            'term'=>$term
                        ]);
                        
                    }    
                    
                          DB::table('users')
                                ->where('email', $email)
                                ->where('device_id', $device_id)
                                ->update([
                                      'login_token' => $loginToken,
                           ]);
                        
                          return response()->json([
                            'status' => true,
                            'allcompleted' => true,
                            'user_id'=>$check_status->id,
                            'message' => 'You are logged in',
                            'term'=>$term
                            
                          ]);
                    }
                }
        
             if($versions_middle) {
                 return response()->json([
                'status' => false,
                'allcompleted' => false,
                'message' => 'Invalid request or version not found'
                ]);
             }
            
             if ($versions_past) {
                 return response()->json([
                'status' => false,
                'allcompleted' => false,
                'message' => 'Invalid request or version not found'
                 ]);
            }
        
            return response()->json([
                'status' => false,
                'message' => 'Invalid request or version not found'
            ]);
                }
    }
    
    public function update_email(Request $request)
    {
        
        $old_email = $request->input('old_email');
        $new_email = $request->input('new_email');
        $device_id = $request->input('device_id');
        $name = $request->input('name');
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        
        
        if($versions_current){
            
            $email_exist = DB::table("users") ->where('email', $new_email)->first();
           
           if($email_exist){
              return response()->json([
                'status' => true,
                'message' => 'This id already exist',
                'success' =>false
            ]);
               
           }
           
            DB::table("users")->where('email', $old_email)->where('device_id', $device_id)->update([
                'email' => $new_email,
                'name' =>$name,
              ]);
                    
                return response()->json([
                    'status' => true,
                    'message' =>'email updated',
                    'success' =>true
                ]);
            
        }
        
          if($versions_middle) {
                 return response()->json([
                'status' => false,
                'allcompleted' => false,
                'message' => 'Invalid request or version not found'
                ]);
             }
            
             if ($versions_past) {
                 return response()->json([
                'status' => false,
                'allcompleted' => false,
                'message' => 'Invalid request or version not found'
                 ]);
            }
        
            
          return response()->json([
                    'message' => 'Please update the app to the latest version.'
                ]);
           
    }
    
    public function withoutevent_cardio_status(Request $request)
    {
      
            $user_id = $request->input('user_id');
            $type = $request->input('type');
            if(empty($user_id)){
                return response()->json([
                        'message' => 'user id is required'
                     ]);
            }
            if(empty($type)){
                return response()->json([
                        'message' => 'type is required',
                        'status'=>false,
                     ]);
            }
            $data = DB::table('user_exercise_complete_status')
                       ->where('user_id', $user_id)
                       ->where('type',$type)
                       ->first();
            if(!$data){
                 return response()->json([
                    'message' => 'data not found',
                    'status'=>false,
                ]);
            }
                
            $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
            $currentDay = $indiaTime->format('l');
            $check_status = DB::table('user_exercise_complete_status')
                ->where('user_id', $user_id)
                ->where('user_day', $currentDay)
                ->where('exercise_status','undone')
                ->where('type',$type)
                ->first();
                
            if(!$check_status){
                $status = true;
                $message = "all exercise are completed";
            }else{
                 $status = false;
                 $message = "exercise are not completed";
            }
           
            return response()->json([
                'status' => $status,
                'message'=>$message,
            ]);
    
        }
}
