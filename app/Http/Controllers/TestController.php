<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Image\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Carbon\Carbon;
use App\Services\MailchimpTransactionalService;
use Illuminate\Support\Facades\View;
use PHPMailer\PHPMailer\Exception;
use App\Mail\SampleMail;
use Newsletter;
use Streaming\FFMpeg;
use Streaming\Format\X264;
use Illuminate\Support\Facades\Log;
class TestController extends Controller
{

     protected $mailchimp;
     protected $base_url;

    public function __construct(MailchimpTransactionalService $mailchimp)
    {
        $this->base_url = url('/'); // or config('app.url')
        $this->mailchimp = $mailchimp;
    }
     public function test_user_registration(Request $request)
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

        $userWithDevice = DB::table('test_users')->where('device_id', $deviceid)->first();
        $check_user = DB::table('test_users')
        ->where('email',$email)
        ->where('device_id',$deviceid)
        ->where('status',1)
        ->first();
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        // if($name === 'undefined' || $name = 0 || $name = '0') {
        //         $name = null;
        //     }
        if ($versions_current) {
            if($check_user){
                
                return response()->json([ 'id' => $check_user->id ?? null,'msg' => 'User already exists', 'profile_compl_status' => $check_user->profile_compl_status ?? null]);
                    }
                 $user = DB::table('test_users')->where('device_id', $deviceid)->first();
                 if($user){
                        return response()->json(['id' => $user->id ?? null,'msg' => 'registered with given these details','email'=> $user->email, 'device_id' => $user->device_id,'social_type'=>$user->social_type,'platform'=>$user->platform, 'profile_compl_status' => $user->profile_compl_status ?? null
                        ]);
                    }
                    DB::table('test_users')
                    ->insert([
                        'name' => $name,
                        'email' => $email??null,
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
                DB::table('test_users')->where('device_id', $deviceid)->insert([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'otp_token' => $randomNumber,
                    'otp_time' => $otpTime,
                    'login_token' => $loginToken,
                    'platform' => $platform,
                    'device_id'=> $deviceid,
                    'profile_compl_status' => 0
                ]);


                $data = ['name' => $name, 'email' => $email, 'randomNumber' => $randomNumber];

                    // Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
                    //     $message->to($email)->subject('Verification Code');
                    // });
                    
                      //php mailer start
                         $data['data'] = ['name' => $name, 'email' => $email, 'randomNumber' => $randomNumber];
                
                    Mail::to('')->send(new SampleMail($data));
                
                    return 'Email sent successfully!';
                     
                     die();           
                

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
                    $user = DB::table('test_users')->where('email', $email)->orWhere('device_id', $deviceid)->first();
                   
                    
                 
                    if ($user) {
                        if ($user->status === 0) {
                          
                            $randomNumber = rand(1000, 9999);
                            $loginToken = rand(100000000000, 999999999999999);
                            $otpTime = now();

                            DB::table('test_users')->where('email', $email)->update(['name' => $name, 'password' => $password, 'otp_token' => $randomNumber, 'otp_time' => $otpTime, 'login_token' => $loginToken, 'platform' => $platform]);

                            $data = ['name' => $name, 'email' => $email, 'randomNumber' => $randomNumber,];

                            // Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
                            //     $message->to($email)->subject('Verification Code');
                            // });
                                   //php mailer start
                                $data = [
                                        'title' => 'Hello from Postmark',
                                        'body' => 'This is a test email using Postmark in Laravel.'
                                    ];
                                
                                    Mail::to('recipient@example.com')->send(new SampleMail($data));
                                
                                    return 'Email sent successfully!';
                                                                     
                                     die();       
                            $socialuser = DB::table('test_users')->where('email', $email)->orWhere('device_id', $deviceid)->first();

                            $id = $socialuser->id;
                            // Insert the user into the database
                                DB::table('test_users')->where('id',$id)
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
                            ->json(['msg' => 'User already registered with deviceID and active', 'status' => 1,'email' => $user->email]);
                    }

                    // Register a new user via traditional signup
                    $randomNumber = rand(1000, 9999);
                    $loginToken = rand(100000000000, 999999999999999);
                    $otptime = now();

                    $data = ['name' => $name, 'email' => $email, 'randomNumber' => $randomNumber,];

                    // Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
                    //     $message->to($email)->subject('Verification Code');
                    // });
                           //php mailer start
                      $data = [
                                'title' => 'Hello from Postmark',
                                'body' => 'This is a test email using Postmark in Laravel.'
                            ];
                        
                            Mail::to('recipient@example.com')->send(new SampleMail($data));
                        
                            return 'Email sent successfully!';
                             
                             die();       

                    // Insert the user into the database
                    DB::table('test_users')
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

                    $socialuser = DB::table('test_users')->where('email', $email)->first();

                    return response()->json(['id' => $socialuser->id ?? null, 'msg' => 'OTP sent to your email', 'status' => 0, 'email' => $request->input('email'), 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                } else {
                   
                    // Register via social login when device id or socialid exist
                    $socialuser = DB::table('test_users')->where('social_id', $socialID)->orWhere('device_id', $deviceid)->first();
                    $socialuseremail = DB::table('test_users')->where('social_id', $socialID)->orWhere('email', $email)->orWhere('device_id', $deviceid)->first();
                    

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
                        return response()->json([ 'id' => $socialuser->id ?? null,'msg' => 'User already exists', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                    }

                    if ($socialuseremail) {
                        return response()->json(['id' => $socialuser->id ?? null, 'msg' => 'User already exists via other authentication', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                    }
                    // Register a new user via social login
                    DB::table('test_users')
                        ->insert([
                            'name' => $name, // Add name from social data if available
                            'email' => $email??null, // Add email from social data if available
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
                    $socialuser = DB::table('test_users')->where('social_id', $socialID)->first();

                    return response()
                        ->json(['id' => $socialuser->id ?? null, 'msg' => 'User registered via social login', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                }
            }
            
        } elseif ($versions_middle) {
            if($check_user){
                 return response()
                            ->json(['msg' => 'you are already exist']);
                    }
                     $user = DB::table('test_users')->where('email', $email)->orWhere('device_id', $deviceid)->first();
                 if($user){
                        return response()->json(['id' => $user->id ?? null,'msg' => 'registered with given these details','email'=> $user->email, 'device_id' => $user->device_id,'social_type'=>$user->social_type,'platform'=>$user->platform, 'profile_compl_status' => $user->profile_compl_status ?? null
                        ]);
                    }
                    DB::table('test_users')
                    ->insert([
                        'name' => $name,
                        'email' => $email??null,
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
                DB::table('test_users')->where('device_id', $deviceid)->insert([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'otp_token' => $randomNumber,
                    'otp_time' => $otpTime,
                    'login_token' => $loginToken,
                    'platform' => $platform,
                    'device_id'=> $deviceid,
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
                    $user = DB::table('test_users')->where('email', $email)->orWhere('device_id', $deviceid)->first();
                   
                    
                 
                    if ($user) {
                        if ($user->status === 0) {
                          
                            $randomNumber = rand(1000, 9999);
                            $loginToken = rand(100000000000, 999999999999999);
                            $otpTime = now();

                            DB::table('test_users')->where('email', $email)->update(['name' => $name, 'password' => $password, 'otp_token' => $randomNumber, 'otp_time' => $otpTime, 'login_token' => $loginToken, 'platform' => $platform]);

                            $data = ['name' => $name, 'email' => $email, 'randomNumber' => $randomNumber,];

                            Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
                                $message->to($email)->subject('Verification Code');
                            });
                            $socialuser = DB::table('test_users')->where('email', $email)->orWhere('device_id', $deviceid)->first();

                            $id = $socialuser->id;
                            // Insert the user into the database
                                DB::table('test_users')->where('id',$id)
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
                            ->json(['msg' => 'User already registered with deviceID and active', 'status' => 1,'email' => $user->email]);
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
                    DB::table('test_users')
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

                    $socialuser = DB::table('test_users')->where('email', $email)->first();

                    return response()->json(['id' => $socialuser->id ?? null, 'msg' => 'OTP sent to your email', 'status' => 0, 'email' => $request->input('email'), 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                } else {
                   
                    // Register via social login when device id or socialid exist
                    $socialuser = DB::table('test_users')->where('social_id', $socialID)->orWhere('device_id', $deviceid)->first();
                    $socialuseremail = DB::table('test_users')->where('social_id', $socialID)->orWhere('email', $email)->orWhere('device_id', $deviceid)->first();
                    

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
                        return response()->json([ 'id' => $socialuser->id ?? null,'msg' => 'User already exists', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                    }

                    if ($socialuseremail) {
                        return response()->json(['id' => $socialuser->id ?? null, 'msg' => 'User already exists via other authentication', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                    }
                    // Register a new user via social login
                    DB::table('test_users')
                        ->insert([
                            'name' => $name, // Add name from social data if available
                            'email' => $email??null, // Add email from social data if available
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
                    $socialuser = DB::table('test_users')->where('social_id', $socialID)->first();

                    return response()
                        ->json(['id' => $socialuser->id ?? null, 'msg' => 'User registered via social login', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                }
            }
            
        } 
        elseif ($versions_past){
             $user = DB::table('test_users')->where('email', $email)->orWhere('device_id', $deviceid)->first();
                 if($user){
                        return response()->json(['id' => $user->id ?? null,'msg' => 'registered with given these details','email'=> $user->email, 'device_id' => $user->device_id,'social_type'=>$user->social_type,'platform'=>$user->platform, 'profile_compl_status' => $user->profile_compl_status ?? null
                        ]);
                    }
            if($check_user){
                 return response()
                            ->json(['msg' => 'you are already exist']);
                    }
                    DB::table('test_users')
                    ->insert([
                        'name' => $name,
                        'email' => $email??null,
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
                DB::table('test_users')->where('device_id', $deviceid)->insert([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'otp_token' => $randomNumber,
                    'otp_time' => $otpTime,
                    'login_token' => $loginToken,
                    'platform' => $platform,
                    'device_id'=> $deviceid,
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
                    $user = DB::table('test_users')->where('email', $email)->orWhere('device_id', $deviceid)->first();
                   
                    
                 
                    if ($user) {
                        if ($user->status === 0) {
                          
                            $randomNumber = rand(1000, 9999);
                            $loginToken = rand(100000000000, 999999999999999);
                            $otpTime = now();

                            DB::table('test_users')->where('email', $email)->update(['name' => $name, 'password' => $password, 'otp_token' => $randomNumber, 'otp_time' => $otpTime, 'login_token' => $loginToken, 'platform' => $platform]);

                            $data = ['name' => $name, 'email' => $email, 'randomNumber' => $randomNumber,];

                            Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
                                $message->to($email)->subject('Verification Code');
                            });
                            $socialuser = DB::table('test_users')->where('email', $email)->orWhere('device_id', $deviceid)->first();

                            $id = $socialuser->id;
                            // Insert the user into the database
                                DB::table('test_users')->where('id',$id)
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
                    DB::table('test_users')
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

                    $socialuser = DB::table('test_users')->where('email', $email)->first();

                    return response()->json(['id' => $socialuser->id ?? null, 'msg' => 'OTP sent to your email', 'status' => 0, 'email' => $request->input('email'), 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                } else {
                   
                    // Register via social login when device id or socialid exist
                    $socialuser = DB::table('test_users')->where('social_id', $socialID)->orWhere('device_id', $deviceid)->first();
                    $socialuseremail = DB::table('test_users')->where('social_id', $socialID)->orWhere('email', $email)->orWhere('device_id', $deviceid)->first();
                    

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
                        return response()->json([ 'id' => $socialuser->id ?? null,'msg' => 'User already exists', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                    }

                    if ($socialuseremail) {
                        return response()->json(['id' => $socialuser->id ?? null, 'msg' => 'User already exists via other authentication', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                    }
                    // Register a new user via social login
                    DB::table('test_users')
                        ->insert([
                            'name' => $name, // Add name from social data if available
                            'email' => $email??null, // Add email from social data if available
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
                    $socialuser = DB::table('test_users')->where('social_id', $socialID)->first();

                    return response()
                        ->json(['id' => $socialuser->id ?? null, 'msg' => 'User registered via social login', 'profile_compl_status' => $socialuser->profile_compl_status ?? null]);
                }
            }
            
        } 
        else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }
    public function test_user_login(Request $request)
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
                }else{
                    // $check_email = DB::table('users')->where('email',$email)->first();
                    // if($check_email){
                    //      return response()
                    //     ->json(['msg' => 'email alrady registerd','social_type' =>$check_email->social_type]);
                        
                    // }
                }
                 $check_email = DB::table('users')->where('email',$email)->first();
                    if($check_email){
                         return response()
                        ->json(['msg' => 'email alrady registerd','social_type' =>$check_email->social_type,'user_id' =>$check_email->id]);
                        
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

                    $data = ['msg' => 'Login successful', 'id' => $userDetails->id, 'email' => $userDetails->email, 'name' => $userDetails->name, 'image' =>$this->base_url . "/json/profile_img/{$userDetails->image}", 'profile_status' => $userDetails->profile_compl_status ?? null, 'goal' => $userDetails->goal ?? null, 'age' => $userDetails->age ?? null, 'height' => $userDetails->height ?? null, 'weight' => $userDetails->weight ?? null, 'fitness_level' => $userDetails->fitness_level ?? null, 'focus_area' => $userDetails->focus_area ?? null, 'gender' => $userDetails->gender ?? null, 'login_token' => $loginToken, 'device_token' => $deviceToken,];

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

                    $data = ['msg' => 'Login successful', 'id' => $userDetails->id, 'email' => $userDetails->email, 'name' => $userDetails->name, 'image' => $this->base_url . "/json/profile_img/{$userDetails->image}", 'profile_status' => $userDetails->profile_compl_status ?? null, 'goal' => $userDetails->goal ?? null, 'age' => $userDetails->age ?? null, 'height' => $userDetails->height ?? null, 'weight' => $userDetails->weight ?? null, 'fitness_level' => $userDetails->fitness_level ?? null, 'focus_area' => $userDetails->focus_area ?? null, 'gender' => $userDetails->gender ?? null, 'login_token' => $loginToken, 'device_token' => $deviceToken,];

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

                    $data = ['msg' => 'Login successful', 'id' => $userDetails->id, 'email' => $userDetails->email, 'name' => $userDetails->name, 'image' =>$this->base_url . "/adserver/public/profile_image/{$userDetails->image}", 'profile_status' => $userDetails->profile_compl_status ?? null, 'goal' => $userDetails->goal ?? null, 'age' => $userDetails->age ?? null, 'height' => $userDetails->height ?? null, 'weight' => $userDetails->weight ?? null, 'fitness_level' => $userDetails->fitness_level ?? null, 'focus_area' => $userDetails->focus_area ?? null, 'gender' => $userDetails->gender ?? null, 'login_token' => $loginToken, 'device_token' => $deviceToken,];

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
//  public function leader_board(Request $request)
// {
//     $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
//     $currentDay = $indiaTime->format('l');
//     $currentTime = $indiaTime->format('H:i');

//     $winner_announced = ($currentDay == 'Saturday') || ($currentDay == 'Sunday' && $currentTime < '23:59');

//     $version = $request->input('version');
//     $user_id = $request->input('user_id');

//     // Get the version data
//     $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
//     $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
//     $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

//     // Define the base URL for profile images
//     $baseUrl = $this->base_url . '/adserver/public/profile_image/';

//     // Get top 5 users by fit_coins
//     $topUsers = DB::table('users')
//         ->select('name', 'fit_coins', 'id', 'image')
//         ->where('country', 'india')
//         ->whereNotNull('country')
//         ->orderBy('fit_coins', 'desc')
//         ->limit(5)
//         ->get()
//         ->map(function ($item, $index) use ($baseUrl) {
//             $item->rank = $index + 1;
//             $item->image_path = $item->image ? $baseUrl . $item->image : null;
//             return $item;
//         });

//     // Get the specified user
//     $specifiedUser = DB::table('users')
//         ->select('name', 'fit_coins', 'id', 'image')
//         ->where('id', $user_id)
//         ->first();

//     if ($specifiedUser) {
//         // Calculate the specified user's rank
//         $specifiedUserRank = DB::table('users')
//             ->where('fit_coins', '>', $specifiedUser->fit_coins)
//             ->count() + 1;

//         $specifiedUser->rank = $specifiedUserRank;
//         $specifiedUser->image_path = $specifiedUser->image ? $baseUrl . $specifiedUser->image : null;

//         // Get the user before the specified user based on fit_coins
//         $beforeUser = DB::table('users')
//             ->select('name', 'fit_coins', 'id', 'image')
//             ->where('fit_coins', '<', $specifiedUser->fit_coins)
//             ->orderBy('fit_coins', 'desc')
//             ->first();

//         // Get the user after the specified user based on fit_coins
//         $afterUser = DB::table('users')
//             ->select('name', 'fit_coins', 'id', 'image')
//             ->where('fit_coins', '>', $specifiedUser->fit_coins)
//             ->orderBy('fit_coins', 'asc')
//             ->first();

//         // Only include before and after users if they are not in the top 5
//         $surroundingUsers = collect([$beforeUser, $specifiedUser, $afterUser])->filter()->map(function ($user) use ($baseUrl) {
//             if ($user) {
//                 $user->image_path = $user->image ? $baseUrl . $user->image : null;
//             }
//             return $user;
//         });

//         // Remove duplicates between topUsers and surroundingUsers
//         $topUsersIds = $topUsers->pluck('id')->toArray();
//         $surroundingUsers = $surroundingUsers->filter(function ($user) use ($topUsersIds) {
//             return !in_array($user->id, $topUsersIds);
//         });

//         // Merge topUsers and surroundingUsers
//         $finalUsers = $topUsers->merge($surroundingUsers)->sortBy('rank')->values();
//     } else {
//         $finalUsers = $topUsers;
//     }

//     // Determine which version data to return
//     if ($versions_current || $versions_middle || $versions_past) {
//         return response()->json([
//             'winner_announced' => $winner_announced,
//             'data' => $finalUsers,
//         ]);
//     } else {
//         return response()->json([
//             'msg' => 'Please update the app to the latest version.'
//         ]);
//     }
// }
// public function test_leader_board(Request $request)
// {
//     $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
//     $currentDay = $indiaTime->format('l');
//     $currentTime = $indiaTime->format('H:i');
    
//     $winner_announced = $currentDay == 'Saturday' || ($currentDay == 'Sunday' && $currentTime < '23:59');

//     $version = $request->input('version');
//     $user_id = $request->input('user_id');
    
//     // Get the version data
//     $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
//     $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
//     $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
    
//     // Define the base URL for profile images
//     $baseUrl = $this->base_url . '/adserver/public/profile_image/';
    
//     // Get all users sorted by fit_coins
//     $allUsers = DB::table('users')
//         ->select('name', 'fit_coins', 'id', 'image')
//         ->where('country', 'india')
//         ->whereNotNull('country')
//         ->orderBy('fit_coins', 'desc')
//         ->get()
//         ->map(function ($item, $index) use ($baseUrl) {
//             $item->rank = $index + 1;
//             $item->image_path = $item->image ? $baseUrl . $item->image : null;
//             return $item;
//         });

//     // Get the specified user
//     $specifiedUser = $allUsers->firstWhere('id', $user_id);

//     $surroundingUsers = collect();
//     if ($specifiedUser) {
//         // Get users before and after the specified user
//         $beforeUser = $allUsers->firstWhere('rank', $specifiedUser->rank - 1);
//         $afterUser = $allUsers->firstWhere('rank', $specifiedUser->rank + 1);

//         $surroundingUsers = collect([$beforeUser, $specifiedUser, $afterUser])->filter();
//     }

//     // Get top 5 users
//     $topUsers = $allUsers->take(5);

//     // Merge and filter out duplicate users
//     $finalUsers = $topUsers->merge($surroundingUsers)->unique('id');

//     // Determine which version data to return
//     if ($versions_current || $versions_middle || $versions_past) {
//         return response()->json([
//             'winner_announced' => $winner_announced,
//             'data' => $finalUsers,
//         ]);
//     } else {
//         return response()->json([
//             'msg' => 'Please update the app to the latest version.'
//         ]);
//     }
// }
    public function test_leader_board(Request $request)
   {
        $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        $currentDay = $indiaTime->format('l');
        $currentTime = $indiaTime->format('H:i');
        
        
        
        if($currentDay=='Saturday'){
           $winner_announced = true;
        }elseif($currentDay == 'Sunday' && $currentTime < '23:59'){
            $winner_announced =true;
        }else{
            $winner_announced = false;
        }
    
        $version = $request->input('version');
        $user_id = $request->input('user_id');
        
        // Get the version data
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        
        // Define the base URL for profile images
        $baseUrl = $this->base_url . '/adserver/public/profile_image/';
        
        // Get top 5 users by fit_coins
        $topUsers = DB::table('users')
          ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
            ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
            ->where('fitme_event.current_day_status',1)
            // ->where('users.country','india')
             ->where(function($query) {
                $query->where('users.country', 'United States')
                      ->orWhere('users.country', 'India');
            })
                    
            ->whereNotNull('users.country')
            ->orderBy('users.fit_coins', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item, $index) use ($baseUrl) {
                $item->rank = $index + 1;
                $item->image_path = $item->image ? $baseUrl . $item->image : null;
                return $item;
            });
            // dd($topUsers);
    
        // Get the specified user
        $specifiedUser = DB::table('users')
            ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
            ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
            ->where('users.id', $user_id)
            // ->where('users.country','india')
              ->where(function($query) {
                    $query->where('users.country', 'United States')
                          ->orWhere('users.country', 'India');
                })
            ->where('fitme_event.current_day_status',1)
            ->whereNotNull('users.country')
            //  ->where('fitme_event.current_day_status',1)
            ->first();
         
    
        if ($specifiedUser) {
            // Calculate the specified user's rank
            $specifiedUserRank = DB::table('users')
             ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
                ->where('users.fit_coins', '>', $specifiedUser->fit_coins)
                ->where('fitme_event.current_day_status',1)
                ->count() + 1;
                
                // dd($specifiedUserRank);
    
            $specifiedUser->rank = $specifiedUserRank;
            $specifiedUser->image_path = $specifiedUser->image ? $baseUrl . $specifiedUser->image : null;
    
            // Get the user before the specified user based on fit_coins
            // $beforeUser = DB::table('users')
            //  ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
            //     ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
            //     ->where('users.fit_coins', '<', $specifiedUser->fit_coins)
            //     ->orderBy('users.fit_coins', 'desc')
            //     ->where('users.country','india')
            //     ->whereNotNull('users.country')
            //     ->first();
                
                // dd($specifiedUser->fit_coins);
                 $beforeUser = DB::table('fitme_event')
                    ->join('users', 'users.id', '=', 'fitme_event.user_id')
                    ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
                    ->where('users.fit_coins', '<', $specifiedUser->fit_coins)
                    ->orderBy('users.fit_coins', 'desc')
                    // ->where('users.country','india')
                    ->where(function($query) {
                        $query->where('users.country', 'United States')
                              ->orWhere('users.country', 'India');
                    })
                    ->where('fitme_event.current_day_status',1)
                    ->whereNotNull('users.country')
                    ->first();
                //   dd($beforeUser);
                
    
            // Get the user after the specified user based on fit_coins
            // $afterUser = DB::table('users')
            //   ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
            //     ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
            //     ->where('users.fit_coins', '>', $specifiedUser->fit_coins)
            //     ->orderBy('users.fit_coins', 'asc')
            //     ->where('users.country','india')
            //     ->whereNotNull('users.country')
            //     ->first();
                 $afterUser = DB::table('fitme_event')
                 ->join('users', 'users.id', '=', 'fitme_event.user_id')
                    ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
                    ->where('users.fit_coins', '>', $specifiedUser->fit_coins)
                    ->orderBy('users.fit_coins', 'asc')
                    // ->where('users.country','india')
                    ->where(function($query) {
                    $query->where('users.country', 'United States')
                                  ->orWhere('users.country', 'India');
                        })
                    ->where('fitme_event.current_day_status',1)
                    ->whereNotNull('users.country')
                ->first();
                // dd($afterUser);
    
            $surroundingUsers = collect([$beforeUser, $specifiedUser, $afterUser])->filter();
            // dd($surroundingUsers);
    
            // Recalculate the rank for the surrounding users
            $ranks = DB::table('fitme_event')
              ->join('users', 'users.id', '=', 'fitme_event.user_id')
                ->select('users.id', 'users.fit_coins')
                ->orderBy('users.fit_coins', 'desc')
                // ->where('users.country','india')
                ->where(function($query) {
                    $query->where('users.country', 'United States')
                          ->orWhere('users.country', 'India');
                })
                ->whereNotNull('country')
                ->where('fitme_event.current_day_status',1)
                ->get()
                ->pluck('fit_coins', 'id');
                
                // dd($ranks);
    
            $rankedUsers = [];
            $currentRank = 1;
            foreach ($ranks as $id => $fit_coins) {
                $rankedUsers[$id] = $currentRank++;
                // echo $id;
            }
            // print_r($rankedUsers);
            // dd($rankedUsers);
            // die();
    
            $surroundingUsers = $surroundingUsers->map(function ($user) use ($rankedUsers, $baseUrl) {
                $user->rank = $rankedUsers[$user->id];
                $user->image_path = $user->image;
                return $user;
            });
            
        //     $surroundingUsers = $surroundingUsers->map(function ($user) use ($rankedUsers, $baseUrl) {
        // // Check if the user's ID exists in the rankedUsers array
        //      $user->rank = isset($rankedUsers[$user->id]) ? $rankedUsers[$user->id] : null;
        //      $user->image_path = $user->image ? $baseUrl . $user->image : null;
        //      return $user;
        //   });
            
            // dd($surroundingUsers);
        } else {
            $surroundingUsers = collect();
        }
    
        // Remove duplicates between topUsers and surroundingUsers
        $topUsersIds = $topUsers->pluck('name')->toArray();
        // dd($topUsersIds);
        $surroundingUsers = $surroundingUsers->filter(function ($user) use ($topUsersIds) {
            return !in_array($user->id, $topUsersIds);
        });
        // dd($surroundingUsers);
    
        // Merge topUsers and surroundingUsers
        $finalUsers = $topUsers->merge($surroundingUsers)->unique('id')->sortByDesc('fit_coins')->values();
        
        // dd($finalUsers);
         $total_winner_announced = DB::table('total_winner_announced')->first();
         $total_winner_announced->winner_announced;
        // Determine which version data to return
        if ($versions_current || $versions_middle || $versions_past) {
            return response()->json([
                'winner_announced' =>$winner_announced,
                'data' => $finalUsers,
                'total_winner_announced'=>$total_winner_announced->winner_announced
                 
                 
            ]);
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'
            ]);
        }
}



      public function test_all_in_one(Request $request)
        {
            $version = $request->input('version');
            if ($version == null) {
                return response()->json([
                    'msg' => 'version is required'
                ]);
            }
        
            $version_types = ['current', 'middle', 'past'];
            $version_valid = false;
        
            foreach ($version_types as $type) {
                if (DB::table('versions')->where('versions', $version)->where('type', $type)->exists()) {
                    $version_valid = true;
                    break;
                }
            }
        
            if (!$version_valid) {
                return response()->json([
                    'msg' => 'Please update the app to the latest version.'
                ]);
            }
        
            $banners = DB::table('banners')->get();
            $terms = DB::table('term_of_condition')->get();
            $dietdata = DB::table('diets')->get();
            $breakfast = DB::table('diets')->where('meal_time','breakfast')->get();
            $lunch = DB::table('diets')->where('meal_time','lunch')->get();
            $dinner = DB::table('diets')->where('meal_time','dinner')->get();
            $typesdata = DB::table('types')->get();
            $goals = DB::table('goals')->select('*')->get();
            $levels = DB::table('levels')->get();
            $bodyparts = DB::table('bodyparts')->get();
            $injurys = DB::table('injurys')->get();
            $workoutareas = DB::table('workoutareas')->get();
             $typesdata = DB::table('types')->get();
        
            $diets['breakfast'] = $breakfast->map(function ($val) {
                return [
                    'diet_id' => $val->diet_id,
                    'diet_title' => $val->diet_title,
                    'diet_description' => $val->diet_description,
                    'diet_ingredients' => $val->diet_ingredients,
                    'diet_category' => $val->diet_category,
                    'diet_direction' => $val->diet_directions,
                    'diet_calories' => $val->diet_calories,
                    'diet_carbs' => $val->diet_carbs,
                    'diet_protein' => $val->diet_protein,
                    'diet_fat' => $val->diet_fat,
                    'diet_time' => $val->diet_time,
                    'diet_servings' => $val->diet_servings,
                    'diet_featured' => $val->diet_featured,
                    'diet_image_link' => $val->diet_image_link,
                    'diet_status' => $val->diet_status,
                    'diet_price' => $val->diet_price,
                    'meal_type' => $val->meal_type,
                    'diet_image' => $this->base_url . "/images/" . $val->diet_image,
                ];
            });
             $diets['lunch'] = $lunch->map(function ($val) {
                return [
                    'diet_id' => $val->diet_id,
                    'diet_title' => $val->diet_title,
                    'diet_description' => $val->diet_description,
                    'diet_ingredients' => $val->diet_ingredients,
                    'diet_category' => $val->diet_category,
                    'diet_direction' => $val->diet_directions,
                    'diet_calories' => $val->diet_calories,
                    'diet_carbs' => $val->diet_carbs,
                    'diet_protein' => $val->diet_protein,
                    'diet_fat' => $val->diet_fat,
                    'diet_time' => $val->diet_time,
                    'diet_servings' => $val->diet_servings,
                    'diet_featured' => $val->diet_featured,
                    'diet_image_link' => $val->diet_image_link,
                    'diet_status' => $val->diet_status,
                    'diet_price' => $val->diet_price,
                    'meal_type' => $val->meal_type,
                    'diet_image' => $this->base_url . "/images/" . $val->diet_image,
                ];
            });
            $diets['dinner'] = $dinner->map(function ($val) {
                return [
                    'diet_id' => $val->diet_id,
                    'diet_title' => $val->diet_title,
                    'diet_description' => $val->diet_description,
                    'diet_ingredients' => $val->diet_ingredients,
                    'diet_category' => $val->diet_category,
                    'diet_direction' => $val->diet_directions,
                    'diet_calories' => $val->diet_calories,
                    'diet_carbs' => $val->diet_carbs,
                    'diet_protein' => $val->diet_protein,
                    'diet_fat' => $val->diet_fat,
                    'diet_time' => $val->diet_time,
                    'diet_servings' => $val->diet_servings,
                    'diet_featured' => $val->diet_featured,
                    'diet_image_link' => $val->diet_image_link,
                    'diet_status' => $val->diet_status,
                    'diet_price' => $val->diet_price,
                    'meal_type' => $val->meal_type,
                    'diet_image' => $this->base_url . "/images/" . $val->diet_image,
                ];
            });
        
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
                        'bodypart_image' =>$this->base_url . "/images/" . $bodypart_image,
        
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
                
                $custom_dialog = DB::table('custom_dialog')->get();
        
            $responseData = [
                'custom_dailog_data' =>$custom_dialog,        
                'data' => $banners,
                'terms' => $terms,
                'diets' => $diets,
                'types' => $typesdata,
                'status' => 'Invalid token',
                'additional_data' => [
                    'goal' => $goals_data,
                    'level' => $levels_data,
                    'focusarea' => $bodyparts_data,
                    'injury' => $injurys_data,
                    'workoutarea' => $workoutareas_data,
                ]
        ];
        
        return response()->json($responseData);
        
            
        }
public function test_all_user_data(Request $request)
    {
        $version = $request->input('version');
        $user_id = $request->input('user_id');
    
        // Validate version
        $version_check = DB::table('versions')
            ->where('versions', $version)
            ->whereIn('type', ['current', 'middle', 'past'])
            ->first();
    
        if (!$version_check) {
            return response()->json(['msg' => 'Please update the app to the latest version.']);
        }
    
        // Fetch user data
        $check_data = DB::table('users')->where('id', $user_id)->first();
        if (!$check_data) {
            return response()->json(['msg' => 'User not found']);
        }
    
        // Check terms and conditions and location
        $term_condition = $check_data->term_and_conditions;
        $location = $check_data->country;
    
        // Fetch current day
        // $currentDay = Carbon::now()->dayOfWeek;
        
        $currentDay = Carbon::now('Asia/Kolkata')->dayOfWeek;
    // dd($currentDay);
        // Fetch user event details
        // $details = DB::table('fitme_event')
        //     ->where('user_id', $user_id)
        //     ->orderByDesc('id')
        //     ->first();
            
    $details = DB::table('fitme_event')
        ->where('user_id', $user_id)
        ->orderByDesc('id')
        ->first();
    
        $detailsArray = (array) $details;
        // $currentDay = date('w');
        $detailsArray['currentDay'] = $currentDay;
        $details = (object) $detailsArray;
    
            
            
    
        // Fetch user profile data
        $userData = DB::table('users')->select(
            'id', 'goal', 'age', 'height', 'weight', 'fitness_level', 'workout_plans', 'experience', 'injury', 
            'target_weight', 'workoutarea', 'equipment', 'focus_area', 'gender', 'name', 'email', 'image', 
            'login_token', 'profile_compl_status', 'signup_type', 'social_type'
        )->where('id', $user_id)->first();
    
        if ($userData) {
            $baseUrl =$this->base_url . '/adserver/public/profile_image/';
            $userData->image_path = $userData->image ? $baseUrl . $userData->image : null;
    
            $goal_title = DB::table('goals')->select('goal_title')->where('goal_id', $userData->goal)->first();
            $level_title = DB::table('levels')->select('level_title')->where('level_id', $userData->fitness_level)->first();
            $focusarea_title = DB::table('bodyparts')->select('bodypart_title')->where('bodypart_id', $userData->focus_area)->first();
    
            $userData->goal_title = $goal_title->goal_title ?? null;
            $userData->level_title = $level_title->level_title ?? null;
            $userData->focusarea_title = $focusarea_title->bodypart_title ?? null;
        } else {
            return response()->json(['error' => 'User not found']);
        }
        
          $get_custom_diet = DB::table('custom_diets')->where('user_id', $user_id)->first();
           if($get_custom_diet){
                $diet_arr_list = json_decode($get_custom_diet->diet_id, true);
    
               $diet_data_arr = [];
                foreach ($diet_arr_list as $val) {
                    
                    $diet_data = DB::table('diets')->where('diet_id', $val)->first();
                    if ($diet_data) {
                        $diet_data_arr[] = [
                            'diet_id' => $diet_data->diet_id,
                            'diet_title' => $diet_data->diet_title,
                            'diet_description' => $diet_data->diet_description,
                            'diet_ingredients' => $diet_data->diet_ingredients,
                            'diet_category' => $diet_data->diet_category,
                            'diet_goal' => $diet_data->diet_goal,
                            'diet_directions' => $diet_data->diet_directions,
                            'diet_calories' => $diet_data->diet_calories,
                            'diet_carbs' => $diet_data->diet_carbs,
                            'diet_protein' => $diet_data->diet_protein,
                            'diet_fat' => $diet_data->diet_fat,
                            'diet_time' => $diet_data->diet_time,
                            'diet_servings' => $diet_data->diet_servings,
                            'diet_featured' => $diet_data->diet_featured,
                            'diet_image_link' => $diet_data->diet_image_link,
                            'diet_status' => $diet_data->diet_status,
                            'diet_price' => $diet_data->diet_price,
                            'diet_image' => $diet_data->diet_image,
                            'diet_image_link' => $diet_data->diet_image_link,
                            'meal_time' => $diet_data->meal_time,
                            'meal_type' => $diet_data->meal_type
                        ];
                    }
                }
           }else{
                $diet_data_arr = [];
           }

        $week_day_exercise = DB::table('user_custom_workouts')->where('user_id', $user_id)->get();
    
        $workout_data = [];
        foreach ($week_day_exercise as $list) {
            $jsornarr_list = $list->exercise_id;
            $workout_name = $list->workout_name;
            $image = $list->image;
            $id = $list->id;
    
            $exercise_json = [];
            $arr_list = json_decode($jsornarr_list, true);
            foreach ($arr_list as $val) {
                $exercise_data = DB::table('exercises')->where('exercise_id', $val)->first();
                if ($exercise_data) {
                    $exercise_json[] = [
                        'exercise_id' => $exercise_data->exercise_id,
                        'exercise_title' => $exercise_data->exercise_title,
                        'exercise_gender' => $exercise_data->exercise_gender,
                        'exercise_goal' => $exercise_data->exercise_goal,
                        'exercise_workoutarea' => $exercise_data->exercise_workoutarea,
                        'exercise_minage' => $exercise_data->exercise_minage,
                        'exercise_maxage' => $exercise_data->exercise_maxage,
                        'exercise_calories' => $exercise_data->exercise_calories,
                        'exercise_injury' => $exercise_data->exercise_injury,
                        'week_day' => $exercise_data->week_day,
                        'exercise_image' => $exercise_data->exercise_image,
                        'exercise_tips' => $exercise_data->exercise_tips,
                        'exercise_instructions' => $exercise_data->exercise_instructions,
                        'exercise_reps' => $exercise_data->exercise_reps,
                        'exercise_sets' => $exercise_data->exercise_sets,
                        'exercise_rest' => $exercise_data->exercise_rest,
                        'exercise_equipment' => $exercise_data->exercise_equipment,
                        'exercise_level' => $exercise_data->exercise_level,
                        'exercise_image_link' => $exercise_data->exercise_image_link,
                        'exercise_video' => $exercise_data->exercise_video,
                        'video' => $exercise_data->video
                    ];
                }
            }
    
            $workout_data[] = [
                'total_exercises' => count($exercise_json),
                'workout_name' => $workout_name,
                'image' => $image,
                'custom_workout_id' => $id,
                'exercise_data' => $exercise_json
            ];
        }
    
        // Construct the final response
        $response = [
            'event_details' => $details ? $details : 'Not any subscription',
            'profile' => $userData,
            'diet_data' => $diet_data_arr,
            'workout_data' => $workout_data,
            'additional_data' => ['term_condition' => $term_condition,'location' => $location,
          
        ]
        ];
    
        return response()->json($response);
        // $response = [
        //             'event_details' => $details ? $details : 'Not any subscription',
                  
        //             'profile' => [is_array($userData) ? $userData : (array) $userData],
        //             'workout_data' => is_array($workout_data) ? $workout_data : (array) $workout_data,
        //             'additional_data' => [
        //             'term_condition' => is_array($term_condition) ? $term_condition : (array) $term_condition,
        //             'location' => is_array($location) ? $location : (array) $location,
        //         ]
            // ];
            
            return response()->json($response);
    }
    
   public function user_rank(Request $request){
    $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
    $currentDay = $indiaTime->format('l');
    $currentTime = $indiaTime->format('H:i');
    $version = $request->input('version');
    $user_id = $request->input('user_id');
    $type = $request->input('type');
    
    $baseUrl = $this->base_url . '/adserver/public/profile_image/';
    
    // Fetch all users and their ranks
    // $allUsers_rank = DB::table('users')
    //     ->select('name', 'fit_coins', 'id', 'image')
    //     ->where('country', 'india')
    //     ->whereNotNull('country')
    //     ->orderBy('fit_coins', 'desc')
    //     ->get()
    //     ->map(function ($item, $index) use ($baseUrl) {
    //         $item->rank = $index + 1; // Adding rank property
    //         $item->image_path = $item->image ? $baseUrl . $item->image : null;
    //         return $item;
    //     });
    
        //  $current_Users_rank =DB::table('users')
        //     ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
        //     ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
        //     ->where('fitme_event.current_day_status',1)
        //     ->where('users.country','india')
        //     ->whereNotNull('users.country')
        //     ->orderBy('users.fit_coins', 'desc')
        //     ->get()
        //     ->map(function ($item, $index) use ($baseUrl) {
        //         $item->rank = $index + 1; // Adding rank property
        //         $item->image_path = $item->image ? $baseUrl . $item->image : null;
        //         return $item;
        //     });
        
        $allUsers_rank =DB::table('users')
            ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
            ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
            ->where('fitme_event.current_day_status',1)
            ->whereIn('users.country', ['india', 'united states'])
            ->whereNotNull('users.country')
            ->orderBy('users.fit_coins', 'desc')
            ->get()
            ->map(function ($item, $index) use ($baseUrl) {
                $item->rank = $index + 1; // Adding rank property
                $item->image_path = $item->image ? $baseUrl . $item->image : null;
                return $item;
            });
            
            //  ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
            // ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
            // ->where('fitme_event.current_day_status',1)
            // ->where('users.country','india')
            // ->whereNotNull('users.country')
            // ->orderBy('users.fit_coins', 'desc')
            // ->limit(5)
            // ->get()

    
    $user_ranks = [];
    $current_user_rank = [];
    
    foreach ($allUsers_rank as $rank) {
        $user_ranks[] = $rank;
        if($rank->id == $user_id){ // Fix comparison operator
            $current_user_rank = $rank;
        }
    }
  
    $user_current_rank = $current_user_rank;
    
    $current_user_rank =$user_current_rank->rank;
    $current_user_coin = $user_current_rank->fit_coins;
    $currentData = [
    'rank' => $current_user_rank,
    'fit_coins' => $current_user_coin,
        ];
    // dd($current_user_rank);
    
    // Handling the 'register' case
    $add_two = [];

    foreach ($allUsers_rank as $data) {
        if ($data->id == $user_id) {
            $data->fit_coins += 2;
        }
        $add_two[] = $data;
    }
    
    usort($add_two, function($a, $b) {
        return $b->fit_coins <=> $a->fit_coins;
    });
    
    foreach ($add_two as $index => $item) {
        $item->rank = $index + 1; // Re-assign rank property
    }
    
    $new_rank = null;
    foreach ($add_two as $rank) {
        if ($rank->id == $user_id) {
            $new_rank = $rank;
            break;
        }
    }
    // $register_rank =$new_rank;
    $register_rank = $new_rank->rank;
    $register_coin = $new_rank->fit_coins;
    // dd($register_rank);
     $registerData = [
    'rank' => $register_rank,
    'fit_coins' => $register_coin,
        ];
    
    // Handling the 'event' case
    $add_five = [];

    foreach ($user_ranks as $data) {
        if ($data->id == $user_id) {
            $data->fit_coins += 3;
        }
        $add_five[] = $data;
    }
    
    usort($add_five, function($a, $b) {
        return $b->fit_coins <=> $a->fit_coins;
    });
    
    foreach ($add_five as $index => $item) {
        $item->rank = $index + 1; // Re-assign rank property
    }
    
    $event_rank = null;
    foreach ($add_five as $rank) {
        if ($rank->id == $user_id) {
            $event_rank = $rank;
            break;
        }
    }
    
    $eventrank=$event_rank->rank;
    $eventcoin= $event_rank->fit_coins;
      $eventData = [
        'rank' => $eventrank,
        'fit_coins' => $eventcoin,
    ];
    
    // return response()->json([
    //     'current_rank' =>$user_current_rank->rank,
    //     'register_rank' => $register_rank->rank,
    //     'event_register' => $event_rank->rank,
    // ]);
    return response()->json([
        'current_rank' =>$currentData,
        'register_rank' => $registerData,
        'event_register' => $eventData,
    ]);
}
    public function test_create_custom_diet(Request $request){
        $version = $request->input('version');
        $diet_id = $request->input('meal_id');
    
        if(!$diet_id){
            return response()->json([
                'msg' => 'meal id not found'
            ]);
        }
    
        $user_id = $request->input('user_id');
       
    
        // Check if the version exists and if it's one of the required types
        $version_exists = DB::table('versions')
            ->where('versions', $version)
            ->whereIn('type', ['current', 'middle', 'past'])
            ->exists();
    
        if (!$version_exists) {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'
            ]);
        }
    
        // Check if the user exists
        $check_user = DB::table('users')->where('id', $user_id)->exists();
    
        if (!$check_user) {
            return response()->json(['msg' => 'no user exist']);
        }
    
        // Check if the user already has custom data
        $check_custom_data = DB::table('custom_diets')->where('user_id', $user_id)->first();
    
        $jsonlist = json_encode($diet_id);
        if ($check_custom_data) {
           
         $data = json_decode($check_custom_data->diet_id, true); // Decode to associative array

            $mergedArray = array_merge($data, $diet_id); // Merge the arrays
            
            $uniqueArray = array_unique($mergedArray); // Ensure unique values
            
            $uniqueArray = array_values($uniqueArray); // Re-index array to ensure it's sequential
            
            $update_diet = json_encode($uniqueArray);
            
         
             
            // Update existing custom diet
            DB::table('custom_diets')
                ->where('user_id', $user_id)
                ->update([
                    
                    'diet_id' => $update_diet
                ]);
            return response()->json([
                'msg' => 'diet updated successfully.'
            ]);
        } else {
            // Insert new custom diet
            DB::table('custom_diets')->insert([
               
                'diet_id' => $jsonlist,
                'user_id' => $user_id
            ]);
            return response()->json([
                'msg' => 'diet inserted successfully.'
            ]);
        }
    }
    
    public function test_add_referral_coin(Request $request)
    {
        $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        $currentDay = $indiaTime->format('l');
        // $currentDay = 'Saturday';
        if($currentDay == 'Saturday' || $currentDay == 'Sunday' ){
              return response()->json([
                'msg' => 'You are not able to use this referal code'
            ]);
        }
        $code = $request->referral_code;
        $user_id = $request->user_id;
        $check_current_Status = DB::table('referral_code')->where('referral_code',$code)->whereNotNull('user_id')->first();
        $check_current_Status->user_id;
        $message = 'Credited extra fitcoins to you as someone registered using your code.';
        $title = 'Money Credited!';
   
        
        $plan_current_Status = DB::table('fitme_event')->where('user_id',$check_current_Status->user_id)->where('current_day_status',1)->first();
        // dd($plan_current_Status);
        if(!$plan_current_Status){
               return response()->json([
                'msg' => 'you are not in current plan please try later.'
            ]);
            
        }

        $get_user_data = DB::table('users')->where('id',$user_id)->first(); 
        if(!$get_user_data){
         return response()->json([
                    'msg' => 'user not exist'
            ]);
        }
        $device_id = $get_user_data->device_id;
       
         $user_code = DB::table('referral_code')
            ->where('referral_code', $code)
            ->where('user_id',$user_id)
            ->whereNotNull('user_id')
            ->first();
            
            
            if($user_code){
                   return response()->json([
                        'msg' => 'You are not able to use this referal code'
                    ]);
            }
            
        $user_code = DB::table('referral_code')
            ->where('referral_code', $code)
            ->first();
    
        if (!$user_code) {
            return response()->json([
                'msg' => 'Invalid referral code'
            ]);
        }
    
        $referred_by = $user_code->user_id;
     
     $check_deviceid_register = DB::table('referral_code')
            ->where('referral_code', $code)
            ->where('register_status','used')
            ->where('device_id',$device_id)
            ->first();
            
     $check_deviceid_eventregister = DB::table('referral_code')
            ->where('referral_code', $code)
            ->where('event_register_status','used')
            ->where('device_id',$device_id)
            ->first();
            
       if($check_deviceid_register){
            return response()->json([
                'msg' => 'referral used'
            ]);
       }
    
        $check_register_status = DB::table('referral_code')
            ->where('referral_code', $code)
            ->where('used_by',$user_id)
            ->whereNotNull('register_status')
            ->first();
    
        $check_event_register = DB::table('referral_code')
            ->where('referral_code', $code)
            ->where('used_by',$user_id)
            ->whereNotNull('event_register_status')
            ->first();
            
           if ($check_event_register && $check_register_status) {
                return response()->json([
                    'msg' => 'This code has already been used'
                ]);
              }
            
    
       
    
        if (!$check_register_status) {
             $notificationData = [
                'message' => $message,
                'notification_id' => 'FitMe',
                'booking_id' => 'FitMe',
                'title' => $title,
            ];
            $user_data = DB::table('users')->where('id',$referred_by)->first();
            // dd($user_data);
          
          
            if($user_data->device_token){
                 $response = $this->sendFirebasePush([$user_data->device_token], $notificationData);
               
                $responses[$user_data->id] = $response;
            }
                DB::table('users')->where('id', $referred_by)->update([
                    'fit_coins' => DB::raw('fit_coins + 2')
                ]);
                
                 DB::table('fitcoin_history')->insert([
                     
                    'fit_coins' =>'+2',
                    'user_id' =>$referred_by,
                    'info' => 'referral_registerd'
                ]);
        
                DB::table('referral_code')->insert([
                    'register_status' => 'used',
                    'used_by' => $user_id,
                    'referral_code' =>$code,
                    'device_id'=>$device_id,
                ]);
        }
    
        if (!$check_event_register) {
              $event_register_used = DB::table('referral_code')
                ->where('event_register_status','used')
                ->where('used_by',$user_id)
                ->where('referral_code')->first();
            
             
                // DB::table('users')->where('id', $referred_by)->update([
                //     'fit_coins' => DB::raw('fit_coins + 5')
                // ]);
        
                // DB::table('referral_code')->insert([
                //     'event_register_status' => 'used',
                //     'used_by' => $user_id,
                //     'referral_code' =>$code,
                //     'device_id'=>$device_id,
                // ]);
             
        }
      
        // send push notificatoion
        $send_mssg =   DB::table('users')->where('id', $referred_by)->get();
        
        
    
        return response()->json([
            'msg' => 'Referral coin added'
        ]);
    }
    public function test_update_custom_diet(Request $request){

        $version = $request->input('version');
        $diet_id = $request->input('meal_id');
    
        if(!$diet_id){
            return response()->json([
                'msg' => 'meal id not found'
            ]);
        }
    
        $user_id = $request->input('user_id');
        $version_exists = DB::table('versions')
            ->where('versions', $version)
            ->whereIn('type', ['current', 'middle', 'past'])
            ->exists();
    
        if (!$version_exists) {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'
            ]);
        }

        $check_user = DB::table('users')->where('id', $user_id)->exists();
    
        if (!$check_user) {
            return response()->json(['msg' => 'no user exist']);
        }

        $check_custom_data = DB::table('custom_diets')->where('user_id', $user_id)->first();
    
        $jsonlist = json_encode($diet_id);
        if ($check_custom_data) {
        //   dd($jsonlist);
        //  $data = json_decode($check_custom_data->diet_id, true); // Decode to associative array

        //     $mergedArray = array_merge($data, $diet_id); // Merge the arrays
            
        //     $uniqueArray = array_unique($mergedArray); // Ensure unique values
            
        //     $uniqueArray = array_values($uniqueArray); // Re-index array to ensure it's sequential
            
        //     $update_diet = json_encode($uniqueArray);
            
         
             
            // Update existing custom diet
            DB::table('custom_diets')
                ->where('user_id', $user_id)
                ->update([
                    
                    'diet_id' => $jsonlist
                ]);
            return response()->json([
                'msg' => 'diet updated successfully.'
            ]);
        } else {
            // Insert new custom diet
            DB::table('custom_diets')->insert([
               
                'diet_id' => $jsonlist,
                'user_id' => $user_id
            ]);
            return response()->json([
                'msg' => 'diet inserted successfully.'
            ]);
        }
    }
    
    // public function monday_to_friday_notification(Request $request){
    //      // Get the upcoming Sunday date
    //     $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
    //     $currentDay = $indiaTime->format('l');
    
        
    //     $message = 'Aaj ka workout 50 FC ke liye shuru ho gaya hai. Streak miss mat karo aur abhi FC jeeto!';
    //     $title = 'Aaj Ka Workout Shuru Ho Gaya Hai!';
        
    //     // Fetch data for the user with user_id = 111 where event_start_date_current is after upcoming Sunday
    //     $event_data = DB::table('fitme_event')
    //         ->join('users', 'fitme_event.user_id', '=', 'users.id')
    //         // ->where('users.id', 6591)
    //         ->select('users.device_token', 'fitme_event.*')
    //         ->get();
    //         // print_r($event_data);
    //         // die();
           
    //     // Define notification data
    //     $notificationData = [
    //         'message' => $message,
    //         'notification_id' => 'FitMe',
    //         'booking_id' => 'FitMe',
    //         'title' => $title,
    //         'image' =>'https://res.cloudinary.com/drfp9prvm/image/upload/v1721102775/DALL_E_2024-07-16_09.36.00_-_A_vibrant_and_eye-catching_banner_design_for_a_fitness_app_to_collect_50_FC_coins_and_win_a_cash_prize._The_banner_should_include_the_text_in_Hinglish_mbxfq8.webp'
    //     ];
    
    //     $responses = [];
        
    //     // Loop through each user and send notification
    //     foreach ($event_data as $data) {
         
    //         if (!empty($data->device_token)) {
 
    //                 $response = $this->sendFirebasePush([$data->device_token], $notificationData);
    //                 $responses[$data->id] = $response;
    //                 dd('data exist');
    //         }
    //     }
    //     return response()->json(['message' => 'Notifications sent successfully!', 'responses' => $responses]);
    // }




   
   
   public function sendFirebasePush($tokens, $data)
    {
        $serverKey = 'AAAADgoThgU:APA91bHFusexjj2_BQ8hggO6eJgVRojGLLlk4rsWELZMf-49GO9mBW5tGxLNiFsFqC8rG15SCgOTzC8yVkQvYnK0vHFT9kx9N5UuMCf10u08KNZF6HFv9O6szfXADVHucZsVx0mOd_Xb';
    
        // Provide default values if keys are not set
        $msg = [
            'message' => $data['message'],
            'notification_id' => 'Test',
            'type' => $data['type'] ?? 'default_type',
            'booking_id' => 'kwh_unit_100%',
            'title' => $data['title'],
            'image' => $data['image'] ?? null,
            'notification' => [
                'body' => $data['message'],
                'title' => $data['title'],
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'mutable-content' => 1,
                    ],
                ],
                'fcmOptions' => [
                    'imageUrl' => $data['image'] ?? null,
                ],
            ],
        ];
    
        $notifyData = [
            'body' => $data['message'],
            'notification_id' => $data['notification_id'],
            'booking_id' => $data['booking_id'],
            'title' => $data['title'],
            'image' => $data['image'] ?? null,
        ];
    
        $apnsPayload = [
            'payload' => [
                'aps' => [
                    'mutable-content' => 1,
                    'alert' => [
                        'title' => $data['title'],
                        'body' => $data['message']
                    ]
                ]
            ],
            'fcmOptions' => [
                'imageUrl' => $data['image'] ?? null
            ]
        ];
    
        $registrationIds = $tokens;
    
        if (count($tokens) > 1) {
            $fields = [
                'registration_ids' => $registrationIds,
                'notification' => $notifyData,
                'data' => $msg,
                'apns' => $apnsPayload,
                'priority' => 'high'
            ];
        } else {
            $fields = [
                'to' => $registrationIds[0],
                'notification' => $notifyData,
                'data' => $msg,
                'apns' => $apnsPayload,
                'priority' => 'high'
            ];
        }
    
        $headers = [
            'Content-Type: application/json',
            'Authorization: key=' . $serverKey,
        ];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
    
        if ($result === FALSE) {
            // Log error instead of dying
            error_log('FCM Send Error: ' . curl_error($ch));
        }
    
        curl_close($ch);
    
        // Return the result as JSON
        return [
            'result' => $result,
            'ios' => [
                'categoryId' => 'default',
                'foregroundPresentationOptions' => [
                    'badge' => true,
                    'sound' => true,
                    'banner' => true,
                    'list' => true,
                ],
                'attachments' => [
                    [
                        'url' => $data['image'] ?? null,
                    ],
                ],
            ],
        ];
    }
    public function test_event(Request $request)
    {
    // Retrieve input data from the request
    $user_id = $request->input('user_id');
    $plan_amount = $request->input('plan');
    $plan_value = $request->input('plan_value');
    $transaction_id = $request->input('transaction_id');
    $platform = $request->input('platform');
    $product_id = $request->input('product_id');
    $used = 1;

    // Determine the allow_usage based on the plan amount
    $allow_usage = 0;
    if ($plan_amount == 'noob') {
        $allow_usage = 1;
    } elseif ($plan_amount == 'pro') {
        $allow_usage = 2;
    } elseif ($plan_amount == 'premium') {
        $allow_usage = 3;
    }

    $current_date = Carbon::now();
    $start_date = $current_date->copy()->next(Carbon::MONDAY)->toDateString();
    $start_purchase_date = $current_date->toDateString();
    $end_date = $current_date->addDays(30)->toDateString();

    // Check for an existing active subscription
    $existing_subscription = DB::table('fitme_event')
        ->where('user_id', $user_id)
        ->where('end_date', '>', $start_purchase_date)
        ->orderByDesc('id') // Assuming 'id' is the primary key
        ->first();

    if ($existing_subscription) {
        // If the existing plan usage is complete and the new plan is an upgrade
        if ($existing_subscription->used_plan >= $existing_subscription->allow_usage) {
            if ($plan_value > $existing_subscription->plan_value) {
                // Update allow_usage based on the new plan
                if ($existing_subscription->plan == 'noob') {
                    if ($plan_amount == 'premium') {
                        $allow_usage = 3;
                    } elseif ($plan_amount == 'pro') {
                        $allow_usage = 2;
                    }
                } elseif ($existing_subscription->plan == 'pro') {
                    if ($plan_amount == 'premium') {
                        $allow_usage = 3;
                    }
                }

                // Update existing subscription with the upgraded plan
                DB::table('fitme_event')
                    ->where('id', $existing_subscription->id)
                    ->update([
                        'plan' => $plan_amount,
                        'used_plan' => $used,
                        'allow_usage' => $allow_usage,
                        'transaction_id' => $transaction_id,
                        'platform' => $platform,
                        'product_id' => $product_id,
                        'plan_value' => $plan_value,
                        'event_purchase_date' => $start_purchase_date,
                        'event_start_date_upcoming' => $start_date,
                        'end_date' => $end_date,
                        'upcoming_day_status' => 1,
                    ]);

                return response()->json(['message' => 'Plan upgraded and existing subscription updated successfully'], 201);
            } else {
                // Complete the current subscription
                DB::table('fitme_event')
                    ->where('id', $existing_subscription->id)
                    ->update(['plan_status' => 'completed']);
                return response()->json(['message' => 'You have reached the maximum usage for your current subscription. Please upgrade to a higher plan.'], 400);
            }
        } else {
            // Update the used_plan count
            DB::table('fitme_event')
                ->where('id', $existing_subscription->id)
                ->update(['used_plan' => $existing_subscription->used_plan + 1, 'event_start_date_upcoming' => $start_date, 'upcoming_day_status' => 1]);

            return response()->json(['message' => 'Subscription usage updated successfully']);
        }
    } else {
        // Insert data into the fitme_event table for a new subscription
        DB::table('fitme_event')->insert([
            'user_id' => $user_id,
            'plan' => $plan_amount,
            'used_plan' => $used,
            'allow_usage' => $allow_usage,
            'transaction_id' => $transaction_id,
            'platform' => $platform,
            'product_id' => $product_id,
            'plan_value' => $plan_value,
            'event_purchase_date' => $start_purchase_date,
            'event_start_date_current' => $start_date,
            'end_date' => $end_date,
        ]);
        
        $add_coin_to = DB::table('referral_code')->where('used_by',$user_id)->first();
        if($add_coin_to){
        $add_coin_to->referral_code;
        
        $code_gen = DB::table('referral_code')->where('referral_code',$add_coin_to->referral_code)
       ->whereNotNull('user_id')
       ->first();
           $code_gen->user_id; // referral code gen.. id 
           
        
          $check_event_register = DB::table('referral_code')
            ->where('used_by',$user_id)
            ->whereNotNull('event_register_status')
            ->first();
            
            
                $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
                $currentDay = $indiaTime->format('l');
                $currentDay ='Saturday';
                if ($currentDay != 'Saturday' && $currentDay != 'Sunday') {
                    //   return response()->json([
                    //     'msg' => 'you can only use this referral code between mon to friday.'
                    // ]);
                    
                   $check_current_status = DB::table('fitme_event')->where('user_id',$code_gen->user_id)->where('current_day_status',1)->first();
                   if($check_current_status){
                       
                       
                   
                    
                    if (!$check_event_register) {
                          $message = 'Credited extra fitcoins to you as someone registered using your code.';
                            $title = 'Money Credited!';
                        $notificationData = [
                            'message' => $message,
                            'notification_id' => 'FitMe',
                            'booking_id' => 'FitMe',
                            'title' => $title,
                        ];
                        $user_data = DB::table('users')->where('id',$code_gen->user_id)->first();
                      
                      
                        if($user_data->device_token){
                             $response = $this->sendFirebasePush([$user_data->device_token], $notificationData);
                           
                            $responses[$user_data->id] = $response;
                        }
                        
                                //   $event_register_used = DB::table('referral_code')
                                // ->where('event_register_status','used')
                                // ->where('used_by',$user_id)
                                // ->where('referral_code')->first();
                    
                     
                        DB::table('users')->where('id',$code_gen->user_id)->update([
                            'fit_coins' => DB::raw('fit_coins + 3')
                        ]);
                     DB::table('fitcoin_history')->insert([       
                            'fit_coins' =>'+5',
                            'user_id' =>$code_gen->user_id,
                            'info' => 'event registerd'
                        ]);
                
                        DB::table('referral_code')->insert([
                            'event_register_status' => 'used',
                            'used_by' => $user_id,
                            'referral_code' =>$add_coin_to->referral_code,
                            'device_id'=> $add_coin_to->device_id,
                        ]);
                    }
                }    
              }else{
                    // return response()->json(['message' => 'today saturday']);
              }
           }
        return response()->json(['message' => 'Event created successfully'], 201);
    }
}
    public function test_event_details($id){
       $currentDay = Carbon::now('Asia/Kolkata')->dayOfWeek;

        $details = DB::table('fitme_event')
            ->select('*')
            ->where('user_id', $id)
            ->orderByDesc('id') // Assuming 'id' is the primary key
            ->first();
    
        if($details){
            $details->currentDay = $currentDay; // Add current day to details object
            return response()->json(['data' => $details]);
        } else {
            return response()->json(['message' => 'Not any subscription']);
        }
}
  public function test_add_coins(Request $request)
{
    $user_id = $request->user_id;
    $user_day = $request->user_day;

    $check_data = DB::table('event_exercise_completion_status')
        ->where('user_id', $user_id)
        ->where('user_day', $user_day)
        ->where('exercise_status', 'completed')
        ->first();
        
      
        
        

    if ($check_data) {
        
        $coins = $check_data->fit_coins;

        $skip_status = DB::table('event_exercise_completion_status')
            ->where('user_id', $user_id)
            ->where('user_day', $user_day)
            ->where('exercise_status', 'completed')
            ->whereNotNull('skip_status')
            ->sum('skip_status');
            
        if($skip_status>0){
            DB::table('fitcoin_history')->insert([
                'user_id' =>$user_id,
                 'info' => 'exercise skip',
                 'fit_coins' => '-'.$skip_status
                ]);
                dd('if condition');
        }
         
          
     

        $prev_status = DB::table('event_exercise_completion_status')
            ->where('user_id', $user_id)
            ->where('user_day', $user_day)
            ->where('exercise_status', 'completed')
            ->whereNotNull('prev_status')
            ->sum('prev_status');
            
        if($prev_status>0){
            DB::table('fitcoin_history')->insert([
                'user_id' =>$user_id,
                 'info' => 'exercise skip',
                 'fit_coins' => '-'.$prev_status
                ]);
        }
      

        $next_status = DB::table('event_exercise_completion_status')
            ->where('user_id', $user_id)
            ->where('user_day', $user_day)
            ->where('exercise_status', 'completed')
            ->whereNotNull('next_status')
            ->sum('next_status');
            
         if($next_status>0){
            DB::table('fitcoin_history')->insert([
                'user_id' =>$user_id,
                 'info' => 'next skip',
                 'fit_coins' => '-'.$next_status
                ]);
        }
          

        $total_mineus_coins = $next_status + $prev_status + $skip_status;
        $total_coin_add = $coins - $total_mineus_coins;
       

        DB::table('users')->where('id', $user_id)->update([
            'fit_coins' => DB::raw('fit_coins + ' . $total_coin_add),
        ]);

        DB::table('event_exercise_completion_status')
            ->where('user_id', $user_id)
            ->where('user_day', $user_day)
            // ->where('exercise_status', 'completed')
            ->update(['today_earning' => $total_coin_add]);

        return response()->json([
            'msg' => 'coin added successfully'
        ]);
    }

    return response()->json([
        'msg' => 'no data found'
    ]);
}
public function test_exercise_points_day(Request $request)
{
    $user_id = $request->user_id;
    $day = $request->day;
    $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    $today = date('l'); // Get the current day of the week

    // Check if the requested day is valid
    if (!in_array($day, $validDays)) {
        return response()->json(['error' => 'Invalid day provided.']);
    }

    // Calculate the previous day
    $currentDayIndex = array_search($day, $validDays);
    $previousDayIndex = $currentDayIndex - 1;
    $previousDay = ($previousDayIndex >= 0) ? $validDays[$previousDayIndex] : null;

    // Check if the requested day exists in the database for the given user
    $userDayCheck = DB::table('event_exercise_completion_status')
                      ->where('user_id', $user_id)
                      ->where('user_day', $day)
                      ->first();

    // Subquery to get the max(today_earning) for each user_day
    $subQuery = DB::table('event_exercise_completion_status')
                  ->select('user_day', DB::raw('MAX(today_earning) as max_today_earning'))
                  ->where('user_id', $user_id)
                  ->whereIn('user_day', $validDays)
                  ->groupBy('user_day');

    // Main query to get the exercise points and deducted status
    $exercise_points = DB::table('event_exercise_completion_status as eecs')
                        ->joinSub($subQuery, 'sub', function ($join) {
                            $join->on('eecs.user_day', '=', 'sub.user_day')
                                 ->on('eecs.today_earning', '=', 'sub.max_today_earning');
                        })
                        ->select('eecs.user_day', 'sub.max_today_earning as today_earning', 'eecs.deducted')
                        ->where('eecs.user_id', $user_id)
                        ->orderBy('eecs.user_day', 'asc')
                        ->get();

    // Prepare response in the required format
    $response = array_fill_keys($validDays, null);

    // Populate response with actual data
    foreach ($exercise_points as $point) {
        $response[$point->user_day] = $point->today_earning;
    }

    // Check and deduct points for the previous day if not present
    if ($previousDay) {
        $previousDayPoints = $exercise_points->firstWhere('user_day', $previousDay);
        if ($previousDayPoints === null) {
            // Deduct points if previous day's data is missing
            $deduction = -5;
            $response[$previousDay] = $deduction;

            // Mark the deduction as applied in the database
            DB::table('event_exercise_completion_status')
                ->updateOrInsert(
                    ['user_id' => $user_id, 'user_day' => $previousDay],
                    ['deducted' => true, 'today_earning' => $deduction]
                );

            // Deduct fit_coins from the user's balance
            DB::table('users')->where('id', $user_id)->decrement('fit_coins', abs($deduction));
        }
    }

    return response()->json(['responses' => $response]);
}


public function testing_leader_board(Request $request)
{
    // dd('this is test 1 leader board');
    $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
    $currentDay = $indiaTime->format('l');
    $currentTime = $indiaTime->format('H:i');
    
    
    
    if($currentDay=='Saturday'){
      $winner_announced = true;
    }elseif($currentDay == 'Sunday' && $currentTime < '23:59'){
        $winner_announced =true;
    }else{
        $winner_announced = false;
    }
    
    $version = $request->input('version');
    $user_id = $request->input('user_id');
    
    // Get the version data
    $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
    $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
    $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
    
    // Define the base URL for profile images
    $baseUrl = $this->base_url . '/adserver/public/profile_image/';
    
    // Get top 5 users by fit_coins
    $topUsers = DB::table('users')
     ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
        ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
        ->where('users.country','india')
        // ->where('fitme_event.current_day_status',1)
        ->whereNotNull('users.country')
        ->orderBy('users.fit_coins', 'desc')
        ->limit(5)
        ->get()
        ->map(function ($item, $index) use ($baseUrl) {
            $item->rank = $index + 1;
            $item->image_path = $item->image ? $baseUrl . $item->image : null;
            return $item;
        });

    // Get the specified user
    $specifiedUser = DB::table('users')
        ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
        ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
        ->where('users.country','india')
        ->where('fitme_event.current_day_status',1)
        ->where('users.id', $user_id)
        ->whereNotNull('users.country')
        ->first();
        
        // dd($specifiedUser);

    if ($specifiedUser) {
        // Calculate the specified user's rank
        $specifiedUserRank = DB::table('users')
            ->where('fit_coins', '>', $specifiedUser->fit_coins)
            ->count() + 1;

        $specifiedUser->rank = $specifiedUserRank;
        $specifiedUser->image_path = $specifiedUser->image ? $baseUrl . $specifiedUser->image : null;

        // Get the user before the specified user based on fit_coins
        $beforeUser = DB::table('users')
             ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
            ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
            ->where('users.country','india')
            ->where('fitme_event.current_day_status',1)
            ->where('users.fit_coins', '<', $specifiedUser->fit_coins)
            ->orderBy('users.fit_coins', 'desc')
            ->whereNotNull('users.country')
            ->first();

        // Get the user after the specified user based on fit_coins
        $afterUser = DB::table('users')
         ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
            ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
            ->where('users.fit_coins', '>', $specifiedUser->fit_coins)
            ->orderBy('users.fit_coins', 'asc')
            ->where('fitme_event.current_day_status',1)
            ->where('users.country','india')
            ->whereNotNull('users.country')
            ->first();

        $surroundingUsers = collect([$beforeUser, $specifiedUser, $afterUser])->filter();

        // Recalculate the rank for the surrounding users
        $ranks = DB::table('users')
            ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
            ->orderBy('users.fit_coins', 'desc')
            ->where('users.country','india')
            
            ->whereNotNull('users.country')
            ->get()
            ->pluck('users.fit_coins', 'id');

        $rankedUsers = [];
        $currentRank = 1;
        foreach ($ranks as $id => $fit_coins) {
            $rankedUsers[$id] = $currentRank++;
        }

        $surroundingUsers = $surroundingUsers->map(function ($user) use ($rankedUsers, $baseUrl) {
            $user->rank = $rankedUsers[$user->id];
            $user->image_path = $user->image ? $baseUrl . $user->image : null;
            return $user;
        });
    } else {
        $surroundingUsers = collect();
    }

    // Remove duplicates between topUsers and surroundingUsers
    $topUsersIds = $topUsers->pluck('id')->toArray();
    $surroundingUsers = $surroundingUsers->filter(function ($user) use ($topUsersIds) {
        return !in_array($user->id, $topUsersIds);
    });

    // Merge topUsers and surroundingUsers
    $finalUsers = $topUsers->merge($surroundingUsers)->sortBy('rank')->values();
    // Determine which version data to return
     $total_winner_announced = DB::table('total_winner_announced')->first();
     $total_winner_announced->winner_announced;
    if ($versions_current || $versions_middle || $versions_past) {
        return response()->json([
            'winner_announced' =>$winner_announced,
            'data' => $finalUsers,
            'total_winner_announced' =>$total_winner_announced->winner_announced
             
             
        ]);
    } else {
        return response()->json([
            'msg' => 'Please update the app to the latest version.'
        ]);
    }
}
public function test_exercise_week_data(Request $request){
            $user_id = $request->input('user_id');  
            $check_user = DB::table('user_exercise_complete_status')->where('user_id', $user_id)->get(); 
            if ($check_user->isEmpty()) {
            return response()->json(['msg' => 'No data found']);
                }
            $sevenDaysAgo = Carbon::now()->subDays(7);
            // dd($sevenDaysAgo);
            $dataForLastSevenDays = DB::table('user_exercise_complete_status')
            ->whereDate('created_at', '>=', $sevenDaysAgo)
            ->where('user_id', $user_id)
            ->where('workout_id', '<', 0)
             ->where('final_status','allcompleted')
            ->get();
            
           
            $json_data = [];
            foreach ($dataForLastSevenDays as $data) {
                $id = $data->id;
                $user_id = $data->user_id;
                $workout_id = $data->workout_id;
                $user_exercise_id = $data->user_exercise_id;
                $user_day = $data->user_day;
                $exercise_status = $data->exercise_status;
                $final_status = $data->final_status;
                $completed_date = $data->completed_date;
                $created_at = $data->created_at;
                $updated_at = $data->updated_at;
            
                // Push each data item into the array
                $json_data[] = [
                    'id' => $id,
                    'user_id' => $user_id,
                    'workout_id' => $workout_id,
                    'user_exercise_id' => $user_exercise_id,
                    'user_day' => $user_day,
                    'exercise_status' => $exercise_status,
                    'final_status' => $final_status,
                    'completed_date' => $completed_date,
                    'created_at' => $created_at,
                    'updated_at' => $updated_at
                ];
                
            }
           
            
         if (count($json_data) === 0) {
            echo json_encode(['message' => 'data not found']);
        } else {
            $json_data = json_encode($json_data);
            echo $json_data;
        }

   }
     public function test_user_exercise_status(Request $request)
    {
        $user_details_list = $request->input('user_details');
        $insertedData = [];
        $msg="";
        $datago=0;
        foreach ($user_details_list as $user_details) 
        {
            // Check if all required keys exist in $user_details
            if (
                        isset($user_details['user_id']) &&
                        isset($user_details['workout_id']) &&
                        isset($user_details['user_exercise_id']) &&
                        isset($user_details['user_day'])
                    ) 
            {
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
                    
                if ($existingRecord) 
                {
                    $check_exercises = DB::table('user_exercise_complete_status')
                        ->where('user_id', $user_details['user_id'])
                        ->where('workout_id', $user_details['workout_id'])
                        ->where('user_exercise_id', $user_details['user_exercise_id'])
                        ->where('user_day', $user_details['user_day'])
                        ->first();
                    if(!$check_exercises)
                    {
                        if($user_details['user_day'] ==-10)
                        {
                            // Perform insert with 'undone' status
                            $insertedRecord = DB::table('user_exercise_complete_status')->insertGetId([
                                'user_id' => $user_details['user_id'],
                                'workout_id' => $user_details['workout_id'],
                                'user_exercise_id' => $user_details['user_exercise_id'],
                                'user_day' => $user_details['user_day'],
                                'exercise_status' => 'undone' // Always set the status to 'undone'
                            ]);
                            // Fetch the inserted record and add it to the result array
                            $insertedData[] = DB::table('user_exercise_complete_status')
                                ->where('id', $insertedRecord)
                                ->first();
                                
                        }
                       
                    }
                        $datago=1;
                        $msg="User exercise allready exist";
                            // return response()->json(['msg' => 'User exercise allready exist']);
                }
                else
                {
                    // Perform insert with 'undone' status
                    $insertedRecord = DB::table('user_exercise_complete_status')->insertGetId([
                        'user_id' => $user_details['user_id'],
                        'workout_id' => $user_details['workout_id'],
                        'user_exercise_id' => $user_details['user_exercise_id'],
                        'user_day' => $user_details['user_day'],
                        'exercise_status' => 'undone' // Always set the status to 'undone'
                    ]);

                    // Fetch the inserted record and add it to the result array
                    $insertedData[] = DB::table('user_exercise_complete_status')
                        ->where('id', $insertedRecord)
                        ->first();
                    $datago=2;
                    $msg="Exercise Status for All Users Inserted Successfully";
                }
            } 
            else 
            {
                $exercise_data = DB::table('user_exercise_complete_status')
                    ->select('*')
                    ->where('user_id', isset($user_details['user_id']))
                    ->get();
                    $msg="Required keys are missing in user_details";
                //return response()->json(['msg' => 'Required keys are missing in user_details',]);
            }
        }
        if($datago==2)
        {
            return response()->json(['msg' => $msg, 'inserted_data' => $insertedData]);
        }
        elseif($datago==1){
            return response()->json(['msg' => $msg]);
        }
    }
    
    //  public function delete_completed_exercises(Request $request){

    //     $currentDate = Carbon::today();
    //     $oneWeekAgo = $currentDate->copy()->subDays(7);
        
    //     dd($oneWeekAgo);
        
    //         $days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    //           $deletedRowsForDay = DB::table('user_exercise_complete_status')
    //             // ->whereDate('created_at', '>=', $oneWeekAgo) // Get records from one week ago or later
    //             // ->whereDate('created_at', '<=', $currentDate)
    //              ->whereIn('user_day', $days) // Use the array of days directly
    //             // ->where('exercise_status', 'completed')
    //             ->get();
    //             dd($deletedRowsForDay);

    // }
    
       public function testing_insert_coins(Request $request)
        {
            $user_id = $request->user_id;
            $user_day = $request->user_day;
        
            $check_data = DB::table('event_exercise_completion_status')
                ->where('user_id', $user_id)
                ->where('user_day', $user_day)
                ->where('exercise_status', 'completed')
                ->first();
                
                
        
            if ($check_data) {
                
                $coins = $check_data->fit_coins;
                // dd($coins);
        
                $skip_status = DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $user_day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('skip_status')
                    ->sum('skip_status');
             
        
                $prev_status = DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $user_day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('prev_status')
                    ->sum('prev_status');
                
                $next_status = DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $user_day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('next_status')
                    ->sum('next_status');
                    
        
                $total_mineus_coins = $next_status + $prev_status + $skip_status;
                $total_coin_add = $coins - $total_mineus_coins;
               
                DB::table('users')->where('id', $user_id)->update([
                    'fit_coins' => DB::raw('fit_coins + ' . $total_coin_add),
                ]);
        
                DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $user_day)
                    // ->where('exercise_status', 'completed')
                    ->update(['today_earning' => $total_coin_add]);
        
                return response()->json([
                    'msg' => 'coin added successfully'
                ]);
            }
        
            return response()->json([
                'msg' => 'no data found'
            ]);
        }
          
        //   public function testing_exercise_points_day(Request $request)
        // {
        //     $user_id = $request->user_id;
        //     $day = $request->day;
        
        //     $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        //     // Check if the requested day is valid
        //     if (!in_array($day, $validDays)) {
        //         return response()->json(['error' => 'Invalid day provided.']);
        //     }
            
        //   // Check if the requested day exists in the database for the given user
        //     $userDayCheck = DB::table('event_exercise_completion_status')
        //                       ->where('user_id', $user_id)
        //                       ->where('user_day', $day)
        //                       ->first();
                              
        //                       dd($userDayCheck);
                              
        
        //     // Subquery to get the max(today_earning) for each user_day
        //     $subQuery = DB::table('event_exercise_completion_status')
        //                   ->select('user_day', DB::raw('MAX(today_earning) as max_today_earning'))
        //                   ->where('user_id', $user_id)
        //                   ->whereIn('user_day', $validDays)
        //                   ->groupBy('user_day');
                          
                        
        
        //     // Main query to get the exercise points and deducted status
        //     $exercise_points = DB::table('event_exercise_completion_status as eecs')
        //                         ->joinSub($subQuery, 'sub', function ($join) {
        //                             $join->on('eecs.user_day', '=', 'sub.user_day')
        //                                  ->on('eecs.today_earning', '=', 'sub.max_today_earning');
        //                         })
        //                         ->select('eecs.user_day', 'sub.max_today_earning as today_earning', 'eecs.deducted')
        //                         ->where('eecs.user_id', $user_id)
        //                         ->orderBy('eecs.user_day', 'asc')
        //                         ->get();
        //                         // dd($exercise_points);
                            
        
                 
        
        //     if (!$userDayCheck) {
        //         // dd('no data found');
        //         // $errorResponse = ['error' => "{$day} record does not exist for the user.", $day => null];
         
        //         // Additional error messages based on specific days
        //         if ($day === 'Tuesday') {
        //         //   dd('test'.$day);
        //           $mondayPoints = $exercise_points->firstWhere('user_day', 'Monday');
        //             $errorResponse['Monday'] = $mondayPoints ? $mondayPoints->today_earning : -5;
        //         } elseif ($day === 'Wednesday') {
        //             $errorResponse['Monday'] = -5;
        //             $mondayPoints = $exercise_points->firstWhere('user_day', 'Monday');
        //             $errorResponse['Monday'] = $mondayPoints ? $mondayPoints->today_earning : -5;
        //             $tuesdayPoints = $exercise_points->firstWhere('user_day', 'Tuesday');
        //             $errorResponse['Tuesday'] = $tuesdayPoints ? $tuesdayPoints->today_earning : -5;
        //         } elseif ($day === 'Thursday') {
        //             $errorResponse['Monday'] = -5;
        //             $tuesdayPoints = $exercise_points->firstWhere('user_day', 'Tuesday');
        //             $errorResponse['Tuesday'] = $tuesdayPoints ? $tuesdayPoints->today_earning : -5;
        //             $tuesdayPoints = $exercise_points->firstWhere('user_day', 'Wednesday');
        //             $errorResponse['Wednesday'] = $tuesdayPoints ? $tuesdayPoints->today_earning : -5;
        //         }elseif ($day === 'Friday') {
        //             $errorResponse['Monday'] = -5;
        //             $tuesdayPoints = $exercise_points->firstWhere('user_day', 'Monday');
        //             $errorResponse['Thursday'] = -5;
        //             $tuesdayPoints = $exercise_points->firstWhere('user_day', 'Tuesday');
        //             $errorResponse['Tuesday'] = $tuesdayPoints ? $tuesdayPoints->today_earning : -5;
        //             $tuesdayPoints = $exercise_points->firstWhere('user_day', 'Wednesday');
        //             $errorResponse['Wednesday'] = $tuesdayPoints ? $tuesdayPoints->today_earning : -5;
        //             $thrusPoints = $exercise_points->firstWhere('user_day', 'Thursday');
        //             $errorResponse['Thursday'] = $thrusPoints ? $thrusPoints->today_earning : -5;
                    
        //         }
                
        //         dd($errorResponse);
                
        //         return response()->json(['responses' => $errorResponse]);
        //     }
        //     // dd('found');
        
        
        //     // Subquery to find the maximum earnings for each valid day
        //     $subQuery = DB::table('event_exercise_completion_status')
        //         ->select('user_day', DB::raw('MAX(today_earning) as max_today_earning'))
        //         ->where('user_id', $user_id)
        //         ->whereIn('user_day', $validDays)
        //         ->groupBy('user_day');
                
        
        //     // Main query to get the exercise points and deducted status
        //     $exercise_points = DB::table('event_exercise_completion_status as eecs')
        //         ->joinSub($subQuery, 'sub', function ($join) {
        //             $join->on('eecs.user_day', '=', 'sub.user_day')
        //                  ->on('eecs.today_earning', '=', 'sub.max_today_earning');
        //         })
        //         ->select('eecs.user_day', 'sub.max_today_earning as today_earning', 'eecs.deducted')
        //         ->where('eecs.user_id', $user_id)
        //         ->orderBy('eecs.user_day', 'asc')
        //         ->get();
        //         // dd($exercise_points);
              
             
        //     // Prepare response in the required format
        //     $response = array_fill_keys($validDays, null);
        //     // dd($response);
            
        
        //     // Populate response with actual data
        //     foreach ($exercise_points as $point) {
        //         $response[$point->user_day] = $point->today_earning;
        //     }
        
        //     // Calculate fit_coins deduction for missing days
        //     $fit_coins_deduction = 0;
        
        //     // Check if deductions have already been applied for any missing days
        //     $missingDays = array_slice($validDays, 0, array_search($day, $validDays));
        //     // dd($missingDays);
            
        //     $deductionsAlreadyApplied = DB::table('event_exercise_completion_status')
        //         ->where('user_id', $user_id)
        //         ->whereIn('user_day', $missingDays)
        //         ->whereNotNull('deducted')
        //         ->exists();
                
        //         // dd($deductionsAlreadyApplied);
        
        //     if (!$deductionsAlreadyApplied) {
        //         // dd('dfasdfj');
        //         // dd($validDays);
        //       // Deduct fit_coins for all missing days up to the requested day
        //         foreach ($validDays as $index => $weekday) {
        //             // dd($validDays);
        //             if ($index > array_search($day, $validDays)) {
        //                 break; // Stop iterating after reaching the requested day
        //             }
        
        //             // Check if the day is missing from database and not already populated with a positive value
        //             if ($response[$weekday] === null) {
        //                 // dd($response[$weekday]);
        //                 // dd($weekday);
        //                 switch ($weekday) {
                            
        //                     case 'Monday':
        //                         $deduction = -5;
        //                         break;
        //                     case 'Tuesday':
        //                         $deduction = -5;
        //                         break;
        //                     case 'Wednesday':
        //                         $deduction = -5;
        //                         break;
        //                          case 'Thursday':
        //                         $deduction = -5;
        //                         break;
        //                     default:
        //                         $deduction = 0; // No deduction for other weekdays
        //                         break;
        //                 }
        
        //                 $response[$weekday] = $deduction;
        //                 $fit_coins_deduction += abs($deduction); // Ensure deduction is positive for decrementing fit_coins
        // // dd($fit_coins_deduction);
        //                 // Mark the deduction as applied in the database
        //                 DB::table('event_exercise_completion_status')
        //                     ->updateOrInsert(
        //                         ['user_id' => $user_id, 'user_day' => $weekday],
        //                         ['deducted' => true,'today_earning' => $deduction]
        //                     );
        //             }
        //         }
        
        //         // Deduct fit_coins from the user's balance
        //         DB::table('users')->where('id', $user_id)->decrement('fit_coins', $fit_coins_deduction);
        //     }
        
        //     return response()->json(['responses' => $response]);
        // }
    //  public function testing_exercise_points_day(Request $request)
    // {
    //     $user_id = $request->user_id;
    //     $day = $request->day;
    //     $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    
    //     // Get the current day of the week
    //     $currentDay = \Carbon\Carbon::now()->format('l'); // 'l' returns full textual representation of the day
    
    //     // Check if the requested day is valid
    //     if (!in_array($day, $validDays)) {
    //         return response()->json(['error' => 'Invalid day provided.']);
    //     }
        
    //     // Check if the requested day exists in the database for the given user
    //     $userDayCheck = DB::table('event_exercise_completion_status')
    //                       ->where('user_id', $user_id)
    //                       ->where('user_day', $day)
    //                       ->first();
    
    //     // Subquery to get the max(today_earning) for each user_day
    //     $subQuery = DB::table('event_exercise_completion_status')
    //                   ->select('user_day', DB::raw('MAX(today_earning) as max_today_earning'))
    //                   ->where('user_id', $user_id)
    //                   ->whereIn('user_day', $validDays)
    //                   ->groupBy('user_day');
    
    //     // Main query to get the exercise points and deducted status
    //     $exercise_points = DB::table('event_exercise_completion_status as eecs')
    //                         ->joinSub($subQuery, 'sub', function ($join) {
    //                             $join->on('eecs.user_day', '=', 'sub.user_day')
    //                                  ->on('eecs.today_earning', '=', 'sub.max_today_earning');
    //                         })
    //                         ->select('eecs.user_day', 'sub.max_today_earning as today_earning', 'eecs.deducted')
    //                         ->where('eecs.user_id', $user_id)
    //                         ->orderBy('eecs.user_day', 'asc')
    //                         ->get();
    
    //     // Additional error messages based on specific days
    //     $errorResponse = [];
    //     if ($day === 'Tuesday') {
    //         $mondayPoints = $exercise_points->firstWhere('user_day', 'Monday');
    //         $errorResponse['Monday'] = $mondayPoints ? $mondayPoints->today_earning : -5;
    //     } elseif ($day === 'Wednesday') {
    //         $errorResponse['Monday'] = -5;
    //         $mondayPoints = $exercise_points->firstWhere('user_day', 'Monday');
    //         $errorResponse['Monday'] = $mondayPoints ? $mondayPoints->today_earning : -5;
    //         $tuesdayPoints = $exercise_points->firstWhere('user_day', 'Tuesday');
    //         $errorResponse['Tuesday'] = $tuesdayPoints ? $tuesdayPoints->today_earning : -5;
    //     } elseif ($day === 'Thursday') {
    //         $errorResponse['Monday'] = -5;
    //         $tuesdayPoints = $exercise_points->firstWhere('user_day', 'Tuesday');
    //         $errorResponse['Tuesday'] = $tuesdayPoints ? $tuesdayPoints->today_earning : -5;
    //         $wednesdayPoints = $exercise_points->firstWhere('user_day', 'Wednesday');
    //         $errorResponse['Wednesday'] = $wednesdayPoints ? $wednesdayPoints->today_earning : -5;
    //     } elseif ($day === 'Friday') {
    //         $errorResponse['Monday'] = -5;
    //         $tuesdayPoints = $exercise_points->firstWhere('user_day', 'Tuesday');
    //         $errorResponse['Tuesday'] = $tuesdayPoints ? $tuesdayPoints->today_earning : -5;
    //         $wednesdayPoints = $exercise_points->firstWhere('user_day', 'Wednesday');
    //         $errorResponse['Wednesday'] = $wednesdayPoints ? $wednesdayPoints->today_earning : -5;
    //         $thursdayPoints = $exercise_points->firstWhere('user_day', 'Thursday');
    //         $errorResponse['Thursday'] = $thursdayPoints ? $thursdayPoints->today_earning : -5;
    //     }
    
    //     // Subquery to find the maximum earnings for each valid day
    //     $subQuery = DB::table('event_exercise_completion_status')
    //         ->select('user_day', DB::raw('MAX(today_earning) as max_today_earning'))
    //         ->where('user_id', $user_id)
    //         ->whereIn('user_day', $validDays)
    //         ->groupBy('user_day');
    
    //     // Main query to get the exercise points and deducted status
    //     $exercise_points = DB::table('event_exercise_completion_status as eecs')
    //         ->joinSub($subQuery, 'sub', function ($join) {
    //             $join->on('eecs.user_day', '=', 'sub.user_day')
    //                  ->on('eecs.today_earning', '=', 'sub.max_today_earning');
    //         })
    //         ->select('eecs.user_day', 'sub.max_today_earning as today_earning', 'eecs.deducted')
    //         ->where('eecs.user_id', $user_id)
    //         ->orderBy('eecs.user_day', 'asc')
    //         ->get();
    
    //     // Prepare response in the required format
    //     $response = array_fill_keys($validDays, null);
    
    //     // Populate response with actual data
    //     foreach ($exercise_points as $point) {
    //         $response[$point->user_day] = $point->today_earning;
    //     }
    
    //     // Calculate fit_coins deduction for missing days
    //     $fit_coins_deduction = 0;
    
    //     // Check if deductions have already been applied for any missing days
    //     $missingDays = array_slice($validDays, 0, array_search($day, $validDays));
        
    //     $deductionsAlreadyApplied = DB::table('event_exercise_completion_status')
    //         ->where('user_id', $user_id)
    //         ->whereIn('user_day', $missingDays)
    //         ->whereNotNull('deducted')
    //         ->exists();
    
    //     if (!$deductionsAlreadyApplied && $day !== $currentDay) {
    //         // Deduct fit_coins for all missing days up to the requested day
    //         foreach ($validDays as $index => $weekday) {
    //             if ($index > array_search($day, $validDays)) {
    //                 break; // Stop iterating after reaching the requested day
    //             }
    
    //             // Check if the day is missing from database and not already populated with a positive value
    //             if ($response[$weekday] === null) {
    //                 switch ($weekday) {
    //                     case 'Monday':
    //                     case 'Tuesday':
    //                     case 'Wednesday':
    //                     case 'Thursday':
    //                         $deduction = -5;
    //                         break;
    //                     default:
    //                         $deduction = 0; // No deduction for other weekdays
    //                         break;
    //                 }
    
    //                 $response[$weekday] = $deduction;
    //                 $fit_coins_deduction += abs($deduction); // Ensure deduction is positive for decrementing fit_coins
    
    //                 // Mark the deduction as applied in the database
    //                 DB::table('event_exercise_completion_status')
    //                     ->updateOrInsert(
    //                         ['user_id' => $user_id, 'user_day' => $weekday],
    //                         ['deducted' => true, 'today_earning' => $deduction]
    //                     );
    //             }
    //         }
    
    //         // Deduct fit_coins from the user's balance
    //         DB::table('users')->where('id', $user_id)->decrement('fit_coins', $fit_coins_deduction);
    //     }
    
    //     return response()->json(['responses' => $response]);
    // }
    
    public function testing_exercise_points_day(Request $request)  //update into live 19-08-24
    {
        $user_id = $request->user_id;
        $check_active_plan = DB::table('fitme_event')
              ->where('user_id',$user_id)
              ->where('current_day_status',1)
              ->first();
          
          
          
          if(!$check_active_plan){
              return response()->json(['msg' => "you are not in current plan"]);
              
            }
        $day = $request->day;
        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $currentDay = \Carbon\Carbon::now()->format('l');
        
        if (!in_array($day, $validDays)) {
            return response()->json(['error' => 'Invalid day provided.']);
        }
        
        $userDayCheck = DB::table('event_exercise_completion_status')
                          ->where('user_id', $user_id)
                          ->where('user_day', $day)
                          ->first();

        $subQuery = DB::table('event_exercise_completion_status')
                      ->select('user_day', DB::raw('MAX(today_earning) as max_today_earning'))
                      ->where('user_id', $user_id)
                      ->whereIn('user_day', $validDays)
                      ->groupBy('user_day');

        $exercise_points = DB::table('event_exercise_completion_status as eecs')
                            ->joinSub($subQuery, 'sub', function ($join) {
                                $join->on('eecs.user_day', '=', 'sub.user_day')
                                     ->on('eecs.today_earning', '=', 'sub.max_today_earning');
                            })
                            ->select('eecs.user_day', 'sub.max_today_earning as today_earning', 'eecs.deducted')
                            ->where('eecs.user_id', $user_id)
                            ->orderBy('eecs.user_day', 'asc')
                            ->get();
   
        if ($day =="Tuesday") {
      
            $mondayPoints = $exercise_points->firstWhere('user_day', 'Monday');
            $errorResponse['Monday'] = $mondayPoints ? $mondayPoints->today_earning : -5;
        } elseif ($day =="Wednesday") {
            $errorResponse['Monday'] = -5;
            $mondayPoints = $exercise_points->firstWhere('user_day', 'Monday');
            $errorResponse['Monday'] = $mondayPoints ? $mondayPoints->today_earning : -5;
            $tuesdayPoints = $exercise_points->firstWhere('user_day', 'Tuesday');
            $errorResponse['Tuesday'] = $tuesdayPoints ? $tuesdayPoints->today_earning : -5;
        } elseif ($day =="Thursday") {
            $errorResponse['Monday'] = -5;
            $tuesdayPoints = $exercise_points->firstWhere('user_day', 'Tuesday');
            $errorResponse['Tuesday'] = $tuesdayPoints ? $tuesdayPoints->today_earning : -5;
            $tuesdayPoints = $exercise_points->firstWhere('user_day', 'Wednesday');
            $errorResponse['Wednesday'] = $tuesdayPoints ? $tuesdayPoints->today_earning : -5;
        }elseif ($day =="Friday") {
             $errorResponse['Monday'] = -5;
            $mondayPoints = $exercise_points->firstWhere('user_day', 'Monday');
            $errorResponse['Thursday'] = -5;
            $tuesdayPoints = $exercise_points->firstWhere('user_day', 'Tuesday');
            $errorResponse['Tuesday'] = $tuesdayPoints ? $tuesdayPoints->today_earning : -5;
            $tuesdayPoints = $exercise_points->firstWhere('user_day', 'Wednesday');
            $errorResponse['Wednesday'] = $tuesdayPoints ? $tuesdayPoints->today_earning : -5;
            $thrusPoints = $exercise_points->firstWhere('user_day', 'Thursday');
            $errorResponse['Thursday'] = $thrusPoints ? $thrusPoints->today_earning : -5;
            
        }
        
        $subQuery = DB::table('event_exercise_completion_status')
            ->select('user_day', DB::raw('MAX(today_earning) as max_today_earning'))
            ->where('user_id', $user_id)
            ->whereIn('user_day', $validDays)
            ->groupBy('user_day');
            

        $exercise_points = DB::table('event_exercise_completion_status as eecs')
            ->joinSub($subQuery, 'sub', function ($join) {
                $join->on('eecs.user_day', '=', 'sub.user_day')
                     ->on('eecs.today_earning', '=', 'sub.max_today_earning');
            })
            ->select('eecs.user_day', 'sub.max_today_earning as today_earning', 'eecs.deducted')
            ->where('eecs.user_id', $user_id)
            ->orderBy('eecs.user_day', 'asc')
            ->get();
            
        $response = array_fill_keys($validDays, null);
        
        foreach ($exercise_points as $point) {
            $response[$point->user_day] = $point->today_earning;
        }

        $fit_coins_deduction = 0;
        $missingDays = array_slice($validDays, 0, array_search($day, $validDays));
        
    
       foreach($missingDays as $pre_days){
           
            echo($pre_days);
            $deductionsAlreadyApplied = DB::table('event_exercise_completion_status')
                ->where('user_id', $user_id)
                ->where('user_day', $pre_days)
                ->whereNotNull('deducted')
                ->exists();
                
                echo(" checkc ".$deductionsAlreadyApplied);
    
            if (!$deductionsAlreadyApplied) {

                    // if($pre_days!=$pre_days){
                    //     if ($index > array_search($day, $validDays)) {
                    //     break;
                    //      }
        
                        if ($response[$pre_days] === null) {
                            switch ($pre_days) {
                                
                                case 'Monday':
                                    $deduction = -5;
                                    break;
                                case 'Tuesday':
                                    $deduction = -5;
                                    break;
                                case 'Wednesday':
                                    $deduction = -5;
                                    break;
                                 case 'Thursday':
                                    $deduction = -5;
                                    break;
                                // default:
                                //     $deduction = 0;
                                //     break;
                            }
            
                            $response[$pre_days] = $deduction;
                            $fit_coins_deduction = abs($deduction); 
                            DB::table('event_exercise_completion_status')
                                ->updateOrInsert(
                                    ['user_id' => $user_id, 'user_day' => $pre_days],
                                    ['deducted' => true,'today_earning' => $deduction]
                                );
                        }
                      
                    
                }
                  
                  DB::table('users')->where('id', $user_id)->decrement('fit_coins', $fit_coins_deduction);
       }
        return response()->json(['responses' => $response]);
    }


//   public function test_event_exercise_complete_status(Request $request)
//     {
//          $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
//          $current_Day = $indiaTime->format('l');
//         $day = $request->day;
//         $id = $request->id;
//         $workout_id = $request->workout_id;
//         $user_id = $request->user_id;
//         $version = $request->input('version');
       
//         $next_status = $request->next_status;
//         $prev_status = $request->prev_status;
//         $skip_status = $request->skip_status;
//         $current_date = Carbon::now();
//         $current_date_gmt_plus_5_30 = $current_date->setTimezone('Asia/Kolkata'); // GMT+5:30 timezone
    
//         $only_date = $current_date_gmt_plus_5_30->toDateString(); // Get only the date part
//         $only_time = $current_date_gmt_plus_5_30->toTimeString(); // Get only the time part
    
//         $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
//         $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
//         $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
    
//         if ($versions_current || $versions_middle) {
//             $status = DB::table('event_exercise_completion_status')
//                 ->select('exercise_status')
//                 ->where('id', $id)
//                 ->first();
//                 // dd($status);
    
//             if ($status) {
//                 if ($status->exercise_status == 'undone') {
//                     // Update the exercise status to completed
//                     DB::table('event_exercise_completion_status')
//                         ->where('id', $id)
//                         ->update([
//                             'exercise_status' => 'undone',
//                             'completed_date' => $only_date,
//                             'completed_time' => $only_time,
//                             'next_status' => $next_status,
//                             'prev_status' => $prev_status,
//                             'skip_status' => $skip_status
//                         ]);
    
//                       // Get the last completed exercise status
//                     $lastCompletedStatus = DB::table('event_exercise_completion_status')
//                         ->select('completed_date', 'completed_time')
//                         ->where('user_id', $user_id)
//                         ->where('exercise_status', 'completed')
//                         ->orderBy('completed_date', 'desc')
//                         ->orderBy('completed_time', 'desc')
//                         ->skip(1) // Skip the current one
//                         ->first();
//                         // dd($lastCompletedStatus);
    
//                     if ($lastCompletedStatus) {
//                         $lastCompletedDateTime = Carbon::parse($lastCompletedStatus->completed_date . ' ' . $lastCompletedStatus->completed_time);
//                         $currentCompletedDateTime = Carbon::parse($only_date . ' ' . $only_time);
//                          echo "last completed time :". $lastCompletedDateTime;
//                          echo " /n  current completed time :". $currentCompletedDateTime;
//                         $diffInHours = $lastCompletedDateTime->diffInHours($currentCompletedDateTime);
//                         // dd($diffInHours);
                          
    
//                         // Check if the current time is greater than the last completed time
//                          if ($currentCompletedDateTime->greaterThan($lastCompletedDateTime) && $diffInHours > 24) {
//                          $adjustedHours = $diffInHours - 24;
//                             // Check if points have already been deducted for this period
//                             $alreadyDeducted = DB::table('event_exercise_completion_status')
//                                 ->where('user_id', $user_id)
//                                 ->where('completed_date', $lastCompletedStatus->completed_date)
//                                 ->where('deducted', 1)
//                                 ->exists();
    
//                             if (!$alreadyDeducted) {
//                                 // Deduct points based on the number of hours difference
//                                 DB::table('users')
//                                     ->where('id', $user_id)
//                                     ->decrement('fit_coins', $adjustedHours);
                                    
//                                     DB::table('event_exercise_completion_status')
//                                     ->where('id', $user_id)
//                                     ->decrement('fit_coins', $adjustedHours);
    
    
//                                 // Update the record to mark points as deducted
//                                 DB::table('event_exercise_completion_status')
//                                     ->where('id', $id)
//                                     ->update(['deducted' => 1]);
//                             }
//                         }
//                     }
    
    
//                     // Check if all exercises for the day are completed
//                     $dayStatuses = DB::table('event_exercise_completion_status')
//                         ->select('exercise_status')
//                         ->where('user_day', $day)
//                         ->where('workout_id', $workout_id)
//                         ->where('user_id', $user_id)
//                         ->get();
    
//                     $allCompleted = $dayStatuses->every(function ($status) {
//                         return $status->exercise_status == 'completed';
//                     });
    
//                     if ($allCompleted) {
//                         DB::table('event_exercise_completion_status')
//                             ->where('user_day', $day)
//                             ->where('workout_id', $workout_id)
//                             ->where('user_id', $user_id)
//                             ->update(['final_status' => 'allcompleted']);
//                     }
    
//                     return response()->json(['msg' => 'Exercise Status Updated to Completed']);
//                 } elseif ($status->exercise_status == 'completed') {
//                     return response()->json(['msg' => 'Exercise Status is Already Completed']);
//                 } else {
//                     return response()->json(['msg' => 'Invalid Exercise Status']);
//                 }
//             } else {
//                 return response()->json(['msg' => 'No Exercise Status Found for the given ID']);
//             }
//         } else {
//             return response()->json(['msg' => 'Please update the app to the latest version.']);
//         }
//     }
    
    public function test_user_event__exercise_status(Request $request)
    {
        
        $user_details_list = $request->input('user_details');
  
        
        $insertedData = [];
        $existingData = [];
        $msg = "";
        $datago = 0;
        $type = $request->input('type');
       
        if ($type === "cardio") {
            $dbtable = 'cardio_exercise_complete_status';
        } else {
            $dbtable = 'event_exercise_completion_status';
        
        
        }
   
        foreach ($user_details_list as $user_details) {
          
            if (
                isset($user_details['user_id']) &&
                isset($user_details['workout_id']) &&
                isset($user_details['user_exercise_id']) &&
                isset($user_details['user_day']) &&
                isset($user_details['fit_coins'])
            ) {
              
                $existingRecord = DB::table($dbtable)
                    ->where('user_id', $user_details['user_id'])
                    ->where('workout_id', $user_details['workout_id'])
                    ->where('user_exercise_id', $user_details['user_exercise_id'])
                    ->where('user_day', $user_details['user_day'])
                    ->where('fit_coins', $user_details['fit_coins'])
                    ->first();
        
                if ($existingRecord) {
                    $existingData[] = $existingRecord;
                    $datago = 1;
                } else {
                  
                    $insertedRecord = DB::table($dbtable)->insertGetId([
                        'user_id' => $user_details['user_id'],
                        'workout_id' => $user_details['workout_id'],
                        'user_exercise_id' => $user_details['user_exercise_id'],
                        'user_day' => $user_details['user_day'],
                        'fit_coins' => $user_details['fit_coins'],
                        'exercise_status' => 'undone' 
                    ]);
        
                  
                    $insertedData[] = DB::table($dbtable)
                        ->where('id', $insertedRecord)
                        ->first();
                    $datago = 2;
                    $msg = "Exercise Status for All Users Inserted Successfully";
                }
            } else {
                $msg = "Required keys are missing in user_details";
            }
        }
        
        if ($datago == 2) {
            return response()->json(['msg' => $msg, 'inserted_data' => $insertedData]);
        } elseif ($datago == 1) {
            $msg = "All user exercise data already exists";
            return response()->json(['msg' => $msg, 'existing_data' => $existingData]);
        } else {
            return response()->json(['msg' => $msg]);
        }
    }
      public function testing_event_exercise_complete_status(Request $request)
    {
        $day = $request->day;
        $id = $request->id;
        $workout_id = $request->workout_id;
        $user_id = $request->user_id;
        $version = $request->input('version');
        $next_status = $request->next_status;
        $prev_status = $request->prev_status;
        $skip_status = $request->skip_status;
        $current_date = Carbon::now();
        $type = $request->input('type');
            
           
        if ($type == "cardio") {
            $dbtable = 'cardio_exercise_complete_status';
        } else {
            $dbtable = 'event_exercise_completion_status';
        }
        $current_date_gmt_plus_5_30 = $current_date->setTimezone('Asia/Kolkata'); // GMT+5:30 timezone
    
        $only_date = $current_date_gmt_plus_5_30->toDateString(); // Get only the date part
        $only_time = $current_date_gmt_plus_5_30->toTimeString(); // Get only the time part
    
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
    
        if ($versions_current || $versions_middle) {
            $status = DB::table($dbtable)
                ->select('exercise_status')
                ->where('id', $id)
                ->first();
    
            if ($status) {
                if ($status->exercise_status == 'undone') {
                    // Update the exercise status to completed
                    DB::table($dbtable)
                        ->where('id', $id)
                        ->update([
                            'exercise_status' => 'completed',
                            'completed_date' => $only_date,
                            'completed_time' => $only_time,
                            'next_status' => $next_status,
                            'prev_status' => $prev_status,
                            'skip_status' => $skip_status
                        ]);
    
                      // Get the last completed exercise status
                    $lastCompletedStatus = DB::table($dbtable)
                        ->select('completed_date', 'completed_time')
                        ->where('user_id', $user_id)
                        ->where('exercise_status', 'completed')
                        ->orderBy('completed_date', 'desc')
                        ->orderBy('completed_time', 'desc')
                        ->skip(1) // Skip the current one
                        ->first();
    
                    if ($lastCompletedStatus) {
                        $lastCompletedDateTime = Carbon::parse($lastCompletedStatus->completed_date . ' ' . $lastCompletedStatus->completed_time);
                        $currentCompletedDateTime = Carbon::parse($only_date . ' ' . $only_time);
    
                        $diffInHours = $lastCompletedDateTime->diffInHours($currentCompletedDateTime);
                          
    
                        // Check if the current time is greater than the last completed time
                         if ($currentCompletedDateTime->greaterThan($lastCompletedDateTime) && $diffInHours > 24) {
                         $adjustedHours = $diffInHours - 24;
                            // Check if points have already been deducted for this period
                            $alreadyDeducted = DB::table($dbtable)
                                ->where('user_id', $user_id)
                                ->where('completed_date', $lastCompletedStatus->completed_date)
                                ->where('deducted', 1)
                                ->exists();
    
                            if (!$alreadyDeducted) {
                                // Deduct points based on the number of hours difference
                                DB::table('users')
                                    ->where('id', $user_id)
                                    ->decrement('fit_coins', $adjustedHours);
    
                                // Update the record to mark points as deducted
                                DB::table($dbtable)
                                    ->where('id', $id)
                                    ->update(['deducted' => 1]);
                            }
                        }
                    }
    
    
                    // Check if all exercises for the day are completed
                    $dayStatuses = DB::table($dbtable)
                        ->select('exercise_status')
                        ->where('user_day', $day)
                        ->where('workout_id', $workout_id)
                        ->where('user_id', $user_id)
                        ->get();
    
                    $allCompleted = $dayStatuses->every(function ($status) {
                        return $status->exercise_status == 'completed';
                    });
    
                    if ($allCompleted) {
                        DB::table($dbtable)
                            ->where('user_day', $day)
                            ->where('workout_id', $workout_id)
                            ->where('user_id', $user_id)
                            ->update(['final_status' => 'allcompleted']);
                            if ($type === "cardio") {
                                  DB::table($dbtable)
                                    ->where('user_day', $day)
                                    ->where('workout_id', $workout_id)
                                    ->where('user_id', $user_id)
                                    ->update([
                                        'cardio_status' =>'done'
                                        ]);
                                        
                                 $check_cardio_status =  DB::table($dbtable)
                                        ->where('user_day', $day)
                                        ->where('workout_id', $workout_id)
                                        ->where('user_id', $user_id)
                                        ->where('final_status','done')
                                        ->get();
                                if(!$check_cardio_status){
                                    $total_coin_add = 5;
                                    DB::table('users')->where('id', $user_id)->update([
                                    'fit_coins' => DB::raw('fit_coins + ' . $total_coin_add),
                                    ]);
                                    
                                }       
                                        
                                
                            }
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
            return response()->json(['msg' => 'Please update the app to the latest version.']);
        }
    }
  public function app_crash_rec(Request $request){
      
      $error_message = $request->input('crashReport');
      if(empty($error_message)){
            return response()->json([
                'msg' => 'error message is required'
            ]);
          
      }

       Log::channel('crash')->error('APP Crash:', [
            'message' => $error_message,
            'app' => 'fitme',
        ]);

die();
    $ids_arr = ['priyankapatial211@gmail.com','sahilpahat@cvinfotech.com','rajrakesh90@gmail.com'];
    
    
       foreach($ids_arr as $ids){
           $user_data = DB::table('users')->where('email',$ids)
                    ->select('fit_coins', 'id', 'email', 'name', 'device_token')
                    ->first();
                    $notificationData = [
                            'message' => 'App Crashed',
                            'notification_id' => 'FitMe',
                            'booking_id' => 'FitMe',
                            'title' => 'Fitme app crash',
                            'type' => '',
                        ];
         
    
                
                    if (!empty($user_data->device_token)) {
                        $response = $this->sendFirebasePush([$user_data->device_token], $notificationData);
                        $responses[$user_data->id] = $response;
                    }
                    // dd('sent');
                    print_r($user_data);
            
           
       }
      
           
    
    // foreach($ids_arr)
    
    
    
   

    // parent::report($exception);


    }
    public function test1_leader_board(Request $request)
    {
        $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        $currentDay = $indiaTime->format('l');
        $currentTime = $indiaTime->format('H:i');
        
        
        
        if($currentDay=='Saturday'){
           $winner_announced = true;
        }elseif($currentDay == 'Sunday' && $currentTime < '23:59'){
            $winner_announced =true;
        }else{
            $winner_announced = false;
        }
    
        $version = $request->input('version');
        $user_id = $request->input('user_id');
        
        // Get the version data
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        
        // Define the base URL for profile images
        $baseUrl = $this->base_url . '/adserver/public/profile_image/';
        
        // Get top 5 users by fit_coins
        $topUsers = DB::table('users')
          ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
            ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
            ->where('fitme_event.current_day_status',1)
            ->where('users.country','india')
            ->whereNotNull('users.country')
            ->orderBy('users.fit_coins', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item, $index) use ($baseUrl) {
                $item->rank = $index + 1;
                $item->image_path = $item->image ? $baseUrl . $item->image : null;
                return $item;
            });
            // dd($topUsers);
    
        // Get the specified user
        $specifiedUser = DB::table('users')
            ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
            ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
            ->where('users.id', $user_id)
            ->where('users.country','india')
            ->where('fitme_event.current_day_status',1)
            ->whereNotNull('users.country')
            //  ->where('fitme_event.current_day_status',1)
            ->first();
         
    
        if ($specifiedUser) {
            // Calculate the specified user's rank
            $specifiedUserRank = DB::table('users')
             ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
                ->where('users.fit_coins', '>', $specifiedUser->fit_coins)
                ->where('fitme_event.current_day_status',1)
                ->count() + 1;
                
                // dd($specifiedUserRank);
    
            $specifiedUser->rank = $specifiedUserRank;
            $specifiedUser->image_path = $specifiedUser->image ? $baseUrl . $specifiedUser->image : null;
    
            // Get the user before the specified user based on fit_coins
            // $beforeUser = DB::table('users')
            //  ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
            //     ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
            //     ->where('users.fit_coins', '<', $specifiedUser->fit_coins)
            //     ->orderBy('users.fit_coins', 'desc')
            //     ->where('users.country','india')
            //     ->whereNotNull('users.country')
            //     ->first();
                
                // dd($specifiedUser->fit_coins);
                 $beforeUser = DB::table('fitme_event')
                    ->join('users', 'users.id', '=', 'fitme_event.user_id')
                    ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
                    ->where('users.fit_coins', '<', $specifiedUser->fit_coins)
                    ->orderBy('users.fit_coins', 'desc')
                    ->where('users.country','india')
                    ->where('fitme_event.current_day_status',1)
                    ->whereNotNull('users.country')
                    ->first();
                //   dd($beforeUser);
                
    
            // Get the user after the specified user based on fit_coins
            // $afterUser = DB::table('users')
            //   ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
            //     ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
            //     ->where('users.fit_coins', '>', $specifiedUser->fit_coins)
            //     ->orderBy('users.fit_coins', 'asc')
            //     ->where('users.country','india')
            //     ->whereNotNull('users.country')
            //     ->first();
                 $afterUser = DB::table('fitme_event')
                 ->join('users', 'users.id', '=', 'fitme_event.user_id')
                    ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
                    ->where('users.fit_coins', '>', $specifiedUser->fit_coins)
                    ->orderBy('users.fit_coins', 'asc')
                    ->where('users.country','india')
                    ->where('fitme_event.current_day_status',1)
                    ->whereNotNull('users.country')
                ->first();
                // dd($afterUser);
    
            $surroundingUsers = collect([$beforeUser, $specifiedUser, $afterUser])->filter();
            // dd($surroundingUsers);
    
            // Recalculate the rank for the surrounding users
            $ranks = DB::table('fitme_event')
              ->join('users', 'users.id', '=', 'fitme_event.user_id')
                ->select('users.id', 'users.fit_coins')
                ->orderBy('users.fit_coins', 'desc')
                ->where('users.country','india')
                ->whereNotNull('country')
                ->where('fitme_event.current_day_status',1)
                ->get()
                ->pluck('fit_coins', 'id');
                
                // dd($ranks);
    
            $rankedUsers = [];
            $currentRank = 1;
            foreach ($ranks as $id => $fit_coins) {
                $rankedUsers[$id] = $currentRank++;
                // echo $id;
            }
            // print_r($rankedUsers);
            // dd($rankedUsers);
            // die();
    
            $surroundingUsers = $surroundingUsers->map(function ($user) use ($rankedUsers, $baseUrl) {
                $user->rank = $rankedUsers[$user->id];
                $user->image_path = $user->image;
                return $user;
            });
            
        //     $surroundingUsers = $surroundingUsers->map(function ($user) use ($rankedUsers, $baseUrl) {
        // // Check if the user's ID exists in the rankedUsers array
        //      $user->rank = isset($rankedUsers[$user->id]) ? $rankedUsers[$user->id] : null;
        //      $user->image_path = $user->image ? $baseUrl . $user->image : null;
        //      return $user;
        //   });
            
            // dd($surroundingUsers);
        } else {
            $surroundingUsers = collect();
        }
    
        // Remove duplicates between topUsers and surroundingUsers
        $topUsersIds = $topUsers->pluck('name')->toArray();
        // dd($topUsersIds);
        $surroundingUsers = $surroundingUsers->filter(function ($user) use ($topUsersIds) {
            return !in_array($user->id, $topUsersIds);
        });
        // dd($surroundingUsers);
    
        // Merge topUsers and surroundingUsers
        $finalUsers = $topUsers->merge($surroundingUsers)->unique('id')->sortByDesc('fit_coins')->values();
        
        // dd($finalUsers);
         $total_winner_announced = DB::table('total_winner_announced')->first();
         $total_winner_announced->winner_announced;
        // Determine which version data to return
        if ($versions_current || $versions_middle || $versions_past) {
            return response()->json([
                'winner_announced' =>$winner_announced,
                'data' => $finalUsers,
                'total_winner_announced'=>$total_winner_announced->winner_announced
                 
                 
            ]);
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'
            ]);
        }
    }
    
    public function testing_add_coins(Request $request)
        {
            $user_id = $request->user_id;
            $user_day = $request->user_day;
            $type = $request->type;
        
            if ($type == "cardio") {
                $dbtable = 'cardio_exercise_complete_status';
            } else {
                $dbtable = 'event_exercise_completion_status';
            }
            
           $check_status = DB::table($dbtable)
                ->where('user_id', $user_id)
                ->where('user_day', $user_day)
                ->where('add_coin_status', 1)
                ->first();
            
            
        
            $check_data = DB::table($dbtable)
                ->where('user_id', $user_id)
                ->where('user_day', $user_day)
                ->where('exercise_status', 'completed')
                ->first();
        
            if ($check_data) {
                
                $coins = $check_data->fit_coins;
        
                $skip_status = DB::table($dbtable)
                    ->where('user_id', $user_id)
                    ->where('user_day', $user_day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('skip_status')
                    ->sum('skip_status');
             
        
                $prev_status = DB::table($dbtable)
                    ->where('user_id', $user_id)
                    ->where('user_day', $user_day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('prev_status')
                    ->sum('prev_status');
        
                $next_status = DB::table($dbtable)
                    ->where('user_id', $user_id)
                    ->where('user_day', $user_day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('next_status')
                    ->sum('next_status');
                    
                    $total_mineus_coins = $next_status + $prev_status + $skip_status;
                    $total_coin_add = $coins - $total_mineus_coins;
                    
                     if ($type == "cardio") {
                            $dbtable = 'cardio_exercise_complete_status';
                            $cardio_earning =$total_coin_add;
                            $earning =$total_coin_add;
                            
                        } else {
                            $dbtable = 'event_exercise_completion_status';
                            $earning =$total_coin_add;
                            $cardio_earning = 0;
                        }
        
                
               
                // dd($total_coin_add);
                
                if (!$check_status) {
                        DB::table('users')->where('id', $user_id)->update([
                            'fit_coins' => DB::raw('fit_coins + ' . $total_coin_add),
                        ]);
                        DB::table('fitcoin_history')->insert([
                            'user_id' =>$user_id,
                            'info'=> "cardio",
                            ]);
                
                        DB::table($dbtable)
                            ->where('user_id', $user_id)
                            ->where('user_day', $user_day)
                            // ->where('exercise_status', 'completed')
                            ->update([
                                'today_earning' => $total_coin_add,
                                'add_coin_status'=>1
                                ]);
                    }
               
                $completed_exercise = DB::table($dbtable)
                    ->where('user_id', $user_id)
                    ->where('user_day', $user_day)
                    ->where('exercise_status', 'completed')
                    ->count();
                
               return response()->json([
                     'msg' => 'coin added successfully',
                     'coins' =>$total_coin_add,
                     'skip_status' =>$skip_status,
                     'next_status' =>$next_status,
                     'prev_status' =>$prev_status,
                     'completed_exercise' => $completed_exercise,
                     'event_earning'  =>$earning,
                     'cardio_earning' =>$cardio_earning,

                ]);
            }
        
            return response()->json([
                'msg' => 'no data found'
            ]);
        }
        
        
         public function test_single_exercise_status(Request $request)
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
                
               if ($day == -11 || $day == -12) {
                   $currentDate = Carbon::now()->toDateString();
                    $check_data = DB::table('user_exercise_complete_status')
                    ->where('user_id', $user_id)
                    ->where('workout_id', $workout_id)
                    ->where('user_exercise_id', $exercise_id)
                    ->where('user_day', $day)
                    ->whereDate('created_at', $currentDate)
                    ->first();
                    
                    if($check_data){
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
                    
                    $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
                    $currentDay = $indiaTime->format('l');
                
                    
                    DB::table('fitcoin_history')->insert([
                        'user_id' => $user_id,
                        'current_day' =>$currentDay,
                        'info' =>'single_exercise',
                        'fit_coins'=>+1,
                        'exercise_id'=>$exercise_id
                        ]);
                       
                    $event_users = DB::table('fitme_event')
                        ->where('current_day_status',1)
                        ->where('user_id',$user_id)
                        ->first();
                        
                  if(!empty($event_users)){
                      $total_coin_add =1;
                      DB::table('users')->where('id', $user_id)->update([
                            'fit_coins' => DB::raw('fit_coins + ' . $total_coin_add),
                        ]);
                      
                  }
                           
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
                    
                    if($check_data){
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
                    
                    $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
                    $currentDay = $indiaTime->format('l');
                
                    
                   
                       
                    $event_users = DB::table('fitme_event')
                        ->where('current_day_status',1)
                        ->where('user_id',$user_id)
                        ->first();
                        
                  if(!empty($event_users)){
                       DB::table('fitcoin_history')->insert([
                        'user_id' => $user_id,
                        'current_day' =>$currentDay,
                        'info' =>'single_exercise',
                        'fit_coins'=>+1,
                        'exercise_id'=>$exercise_id
                        ]);
                        
                        
                      $total_coin_add =1;
                      DB::table('users')->where('id', $user_id)->update([
                            'fit_coins' => DB::raw('fit_coins + ' . $total_coin_add),
                        ]);
                      
                  }
                           
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
                    
                    if($check_data){
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
                    
                    $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
                    $currentDay = $indiaTime->format('l');
                
                    
                    DB::table('fitcoin_history')->insert([
                        'user_id' => $user_id,
                        'current_day' =>$currentDay,
                        'info' =>'single_exercise',
                        'fit_coins'=>+1,
                        
                        ]);
                       
                    $event_users = DB::table('fitme_event')
                        ->where('current_day_status',1)
                        ->where('user_id',$user_id)
                        ->first();
                        
                  if(!empty($event_users)){
                      $total_coin_add =1;
                      DB::table('users')->where('id', $user_id)->update([
                            'fit_coins' => DB::raw('fit_coins + ' . $total_coin_add),
                        ]);
                      
                  }
                           
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
    public function test_custom_workout(Request $request){

        $user_id = $request->input('user_id');    
        $workout_name = $request->input('workout_name');
        $exercise_list = $request->input('exercises');
        // $image = $request->input('image');
        
        $jsonlist = json_encode($exercise_list);
          if($request->hasfile('image')){
                $image = $request->file('image');
                $ext = $image->extension();
                $myfile = time() . '.' . $ext;
                $image->storeAs('/public/profile_image', $myfile);
                $image->storeAs('/public/image', $myfile);
                $imagefile = $this->base_url . "/adserver/public/storage/image/".$myfile;
          }else{
              $imagefile  = "";
              
          }
        
        DB::table('user_custom_workouts')->insert([
             'user_id' => $user_id,
             'workout_name' => $workout_name,
             'exercise_id' =>$jsonlist,
             'image' => $imagefile,
        ]);
        $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        $currentDay = $indiaTime->format('l');

        $event_users = DB::table('fitme_event')
            ->where('current_day_status',1)
            ->where('user_id',$user_id)
            ->first();
            
      if(!empty($event_users)){
          
          
          $check_status = DB::table('users')->where('id',$user_id)->where('weekly_custom_workout_status',1)->first();
        
        if(!$check_status){
               DB::table('fitcoin_history')->insert([
                'user_id' => $user_id,
                'current_day' =>$currentDay,
                'info' =>'create_custom_wokrout',
                'fit_coins'=>+7
                ]);
                
                 DB::table('users')->where('id',$user_id)->update([
                     'weekly_custom_workout_status'=>1
                     ]);

          $total_coin_add =7;
          DB::table('users')->where('id', $user_id)->update([
                'fit_coins' => DB::raw('fit_coins + ' . $total_coin_add),
            ]);
        }
          
      }
        return response()->json([
                'msg' => 'data inserted successfully'
            ]);
        
   }  
  public function test_coin_deduction_rec(Request $request){
        $user_id = $request->user_id;
        // $get_coins = $request->coins;
        $get_coins = 1;
        $day = $request->day;
        $day_Data = $day;
        $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        $currentDay = $indiaTime->format('l');
        
        
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            if (!in_array($day, $weekdays)) {
                return response()->json([
                    'msg' => 'please enter valid day'
                ]);
              
            } 
            // if (!$get_coins) {
            //     return response()->json([
            //         'msg' => 'coin is required'
            //     ]);
              
            // } 

            if(!$user_id){
                  return response()->json([
                        'msg' => 'user id is required'
                    ]);
            }
          $check_data = DB::table('event_exercise_completion_status')
            ->where('user_id', $user_id)
            ->first();
        
            
            
    
        // if ($check_data) {
        //single_exercise
          $check_monday_data = DB::table('event_exercise_completion_status')
                ->where('user_id', $user_id)
                ->where('user_day',$day)
                ->first();
            
                $monday=[];
          
                 // point deduction 
                $point_deduction =[];
                 $skip_status_monday = DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('skip_status')
                    ->sum('skip_status');
                    
                $point_deduction['skip_status']= $skip_status_monday;
        
                $prev_status_monday = DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('prev_status')
                    ->sum('prev_status');
                    
                    $point_deduction['prev_status']= $prev_status_monday;
        
                $next_status_monday = DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('next_status')
                    ->sum('next_status');
                    
                     $point_deduction['next_status']= $next_status_monday;
                     $late = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','late')->count();
                     $point_deduction['delay']= $late;
                    
                    
                    // workout section --------------------------------------------------------
                    // cardio 
                     $cardio =[];
                    
                    
                  $cardio_status_monday = DB::table('cardio_exercise_complete_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $day)
                    ->where('today_earning', '>', 0)
                    // ->where('cardio_status','done')
                     ->first();
                     
                    //  dd($cardio_status_monday);
                    
                     
                     $total_cardio_points =0;
                    if($cardio_status_monday){
                        $cardio_coins =$cardio_status_monday->fit_coins; 

                      $skip_cardio_status_monday = DB::table('cardio_exercise_complete_status')
                        ->where('user_id', $user_id)
                        ->where('user_day', $day)
                        // ->where('final_status', 'allcompleted')
                        ->where('exercise_status', 'completed')
                        ->whereNotNull('skip_status')
                        ->sum('skip_status');
                        
                        // $cardio['skip_status']= $skip_cardio_status_monday;
                        
                        // dd($skip_status_monday);
                 
            
                    $prev_cardio_status_monday = DB::table('cardio_exercise_complete_status')
                        ->where('user_id', $user_id)
                        ->where('user_day', $day)
                        // ->where('final_status', 'allcompleted')
                        ->where('exercise_status', 'completed')
                        ->whereNotNull('prev_status')
                        ->sum('prev_status');
                        
                        // $cardio['prev_status']= $prev_cardio_status_monday;
            
                    $next_cardio_status_monday = DB::table('cardio_exercise_complete_status')
                        ->where('user_id', $user_id)
                        ->where('user_day', $day)
                        // ->where('final_status', 'allcompleted')
                        // ->where('exercise_status', 'completed')
                        ->whereNotNull('next_status')
                        ->sum('next_status');
                        //  $cardio['next_status']= $next_cardio_status_monday;
                         $total_cardio_deduction = $next_cardio_status_monday+$prev_cardio_status_monday+$skip_cardio_status_monday;
                        //  $total_cardio_points =$cardio_coins-$total_cardio_deduction;
                        //  $monday['cardio']= $total_cardio_points;
                        
                        $total_cardio_points = $cardio_status_monday->today_earning;
                    }
                     $cardio['cardio']= $total_cardio_points;
                    
                   
                    
                $countSessionA = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_a', 'done')->count();
                $countSessionB = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_b', 'done')->count();
                $countSessionC = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_c', 'done')->count();
                $countSessionD = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_d', 'done')->count();
                
                $total_breath_coins= ($countSessionA+$countSessionB+$countSessionC+$countSessionD)*$get_coins;
                $cardio['breath_in']=0;
                if($countSessionA || $countSessionB || $countSessionC || $countSessionD){
                      $total_coins= ($countSessionA+$countSessionB+$countSessionC+$countSessionD)*$get_coins;
                     $cardio['breath_in']=$total_breath_coins;
                }
                    
                    
        // event overview  --------------------------------------------------------
        
        $event_overview =[];
        
        $eventoverview = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->get();
        if($eventoverview->isNotEmpty()){
            
              $total_exercises = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
              $event_overview['exercises'] =$total_exercises ;
            //   dd($eventoverview[0]->created_at);
               
              $dateTime = Carbon::parse($eventoverview[0]->created_at)->setTimezone('Asia/Kolkata');

                $Weekday = $dateTime->format('l');
                
                // Format the date and time
                $formattedDateTime = $dateTime->format('M-d-y');
                $formattedTime = $dateTime->format('h:iA');
                
                //   $event_overview['day'] =$Weekday ;
                  $event_overview['day'] =$day;
                  $event_overview['date'] =$formattedDateTime;
                  $event_overview['time'] =$formattedTime;
                  
                  $missed_exercises = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->where('exercise_status','undone')->count();
                  $event_overview['missed'] =$missed_exercises;
                  
        }
        
           
             // refer rewards --------------------------------------------------------
             $refer_rewards =[];
             $register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('current_day',$day)->where('info','referral_registerd')->count();
             $event_register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('current_day',$day)->where('info','event_registerd')->count();
             
             $reward_data['register'] =$register_data;
             $reward_data['event_register'] =$event_register_data;
             $total_register_points = $register_data*2;
             $total_event_register_points = $event_register_data*3;
             $reward_data['total_rewards_points'] =$event_register_data+$event_register_data;
             
         // week_day points --------------------------------------------------------
                          $currentDay = $indiaTime->format('l');
                            $week_day_data = [];
                            $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                            
                            // Find the index of the current day in the weekdays array
                            $currentDayIndex = array_search($currentDay, $weekdays);
                            
                            foreach ($weekdays as $index => $day) {
                                if ($index <= $currentDayIndex) {
                                    // Retrieve the points for the current day and days before it
                                    $points = DB::table('event_exercise_completion_status')
                                        ->where('user_id', $user_id)
                                        ->where('user_day', $day)
                                        ->first();
                                    if ($points) {
                                        $week_day_data[$day] = $points->today_earning;
                                    } else {
                                        $week_day_data[$day] = 0;
                                    }
                                } else {
                                    // Set to null for days after the current day
                                    $week_day_data[$day] = null;
                                }
                            }
                    // dd($week_day_data);
                    
                    // single_exercise
                    $single_exercise =[];
                    
                    $exercise_count =DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','single_exercise')->count();
                    $exercises = DB::table('fitcoin_history')
                      ->where('user_id',$user_id)
                      ->where('info','single_exercise')
                      ->get();
                      
                      $exercises = [];
                    

                   
                   
                    
                 $exerciseData=[];
                    foreach ($exercises as $exercise_id) {
                
                        $exerciseData = DB::table('exercises')
                                          ->where('exercise_id', $exercise_id)
                                          ->first();
                    
                        
                        if ($exerciseData) {
                           
                            $exercises[] = $exerciseData;
                        }
                        
                    }
                      
                    
                    
                
                
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // dd($monday_points);
                // $week_day_data['Monday'] =$monday_points;
                // $week_day_data['Tuesday'] =$monday_points;
                // $week_day_data['Wednesday'] =$monday_points;
                // $week_day_data['Thruesday'] =$thursday_points;
                // $week_day_data['Friday'] =$monday_friday;
                 
                //  $total_coins_to_add = 
                
                // dd($week_day_data[$day_Data] );

         $register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','referral_registerd')->count();
         $event_register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','event_registerd')->count();
        
         $day_missed = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','day_missed')->count();
         $custom_wokrout_coins = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','create_custom_wokrout')->count();
        
         $register_object = (object) $register_data;
         $response = [
            // $day => $monday,
            'points'=>$week_day_data,
            'point_deduction'=>$point_deduction,
            'workout'=>$cardio,
            'event_overview'=>$event_overview,
            'refer_rewards'=>$reward_data,
            'exercises' =>$exerciseData,
            // 'tuesday' => $tuesday,
            // 'wednesday' => $wednesday,
            // 'thursday' => $thursday,
            // 'friday' => $friday,
            // 'total_registerd' => $register_data,
            // 'total_event_registerd'=>$event_register_data,
            // 'late_exercises'=>$late,
            // 'custom_wokrout_coins' =>$custom_wokrout_coins,
            // 'missing_days_count'=>$missingDaysCount,
            // 'missing_days'=>$missedDays,
        ];

        return response()->json($response);
    // }

    // return response()->json([
    //     'msg' => 'no data found'
    // ]);
 }
   public function testing_all_user_data(Request $request)
{
    $version = $request->input('version');
    $user_id = $request->input('user_id');

    // Validate version
    $version_check = DB::table('versions')
        ->where('versions', $version)
        ->whereIn('type', ['current', 'middle', 'past'])
        ->first();

    if (!$version_check) {
        return response()->json(['msg' => 'Please update the app to the latest version.']);
    }

    // Fetch user data
    $check_data = DB::table('users')->where('id', $user_id)->first();
    if (!$check_data) {
        return response()->json(['msg' => 'User not found']);
    }

    // Check terms and conditions and location
    $term_condition = $check_data->term_and_conditions;
    $location = $check_data->country;

    // Fetch current day
    // $currentDay = Carbon::now()->dayOfWeek;
    
    $currentDay = Carbon::now('Asia/Kolkata')->dayOfWeek;
// dd($currentDay);
    // Fetch user event details
    // $details = DB::table('fitme_event')
    //     ->where('user_id', $user_id)
    //     ->orderByDesc('id')
    //     ->first();
        
$details = DB::table('fitme_event')
    ->where('user_id', $user_id)
    ->orderByDesc('id')
    ->first();

    $detailsArray = (array) $details;
    // $currentDay = date('w');
    $detailsArray['currentDay'] = $currentDay;
    $details = (object) $detailsArray;

        
        

    // Fetch user profile data
    $userData = DB::table('users')->select(
        'id', 'goal', 'age', 'height', 'weight', 'fitness_level', 'workout_plans', 'experience', 'injury', 
        'target_weight', 'workoutarea', 'equipment', 'focus_area', 'gender', 'name', 'email', 'image', 
        'login_token', 'profile_compl_status', 'signup_type', 'social_type'
    )->where('id', $user_id)->first();

    if ($userData) {
        $baseUrl = $this->base_url . '/adserver/public/profile_image/';
        $userData->image_path = $userData->image ? $baseUrl . $userData->image : null;

        $goal_title = DB::table('goals')->select('goal_title')->where('goal_id', $userData->goal)->first();
        $level_title = DB::table('levels')->select('level_title')->where('level_id', $userData->fitness_level)->first();
        $focusarea_title = DB::table('bodyparts')->select('bodypart_title')->where('bodypart_id', $userData->focus_area)->first();

        $userData->goal_title = $goal_title->goal_title ?? null;
        $userData->level_title = $level_title->level_title ?? null;
        $userData->focusarea_title = $focusarea_title->bodypart_title ?? null;
    } else {
        return response()->json(['error' => 'User not found']);
    }

    // Fetch custom workouts
    $week_day_exercise = DB::table('user_custom_workouts')->where('user_id', $user_id)->get();

    $workout_data = [];
    foreach ($week_day_exercise as $list) {
        $jsornarr_list = $list->exercise_id;
        $workout_name = $list->workout_name;
        $image = $list->image;
        $id = $list->id;

        $exercise_json = [];
        $arr_list = json_decode($jsornarr_list, true);
        foreach ($arr_list as $val) {
            $exercise_data = DB::table('exercises')->where('exercise_id', $val)->first();
            if ($exercise_data) {
                $exercise_json[] = [
                    'exercise_id' => $exercise_data->exercise_id,
                    'exercise_title' => $exercise_data->exercise_title,
                    'exercise_gender' => $exercise_data->exercise_gender,
                    'exercise_goal' => $exercise_data->exercise_goal,
                    'exercise_workoutarea' => $exercise_data->exercise_workoutarea,
                    'exercise_minage' => $exercise_data->exercise_minage,
                    'exercise_maxage' => $exercise_data->exercise_maxage,
                    'exercise_calories' => $exercise_data->exercise_calories,
                    'exercise_injury' => $exercise_data->exercise_injury,
                    'week_day' => $exercise_data->week_day,
                    'exercise_image' => $exercise_data->exercise_image,
                    'exercise_tips' => $exercise_data->exercise_tips,
                    'exercise_instructions' => $exercise_data->exercise_instructions,
                    'exercise_reps' => $exercise_data->exercise_reps,
                    'exercise_sets' => $exercise_data->exercise_sets,
                    'exercise_rest' => $exercise_data->exercise_rest,
                    'exercise_equipment' => $exercise_data->exercise_equipment,
                    'exercise_level' => $exercise_data->exercise_level,
                    'exercise_image_link' => $exercise_data->exercise_image_link,
                    'exercise_video' => $exercise_data->exercise_video,
                    'video' => $exercise_data->video
                ];
            }
        }

        $workout_data[] = [
            'total_exercises' => count($exercise_json),
            'workout_name' => $workout_name,
            'image' => $image,
            'custom_workout_id' => $id,
            'exercise_data' => $exercise_json
        ];
    }
   $get_custom_diet = DB::table('custom_diets')->where('user_id', $user_id)->first();
           if($get_custom_diet){
                $diet_arr_list = json_decode($get_custom_diet->diet_id, true);
    
               $diet_data_arr = [];
                foreach ($diet_arr_list as $val) {
                    
                    $diet_data = DB::table('diets')->where('diet_id', $val)->first();
                    if ($diet_data) {
                        $diet_data_arr[] = [
                            'diet_id' => $diet_data->diet_id,
                            'diet_title' => $diet_data->diet_title,
                            'diet_description' => $diet_data->diet_description,
                            'diet_ingredients' => $diet_data->diet_ingredients,
                            'diet_category' => $diet_data->diet_category,
                            'diet_goal' => $diet_data->diet_goal,
                            'diet_direction' => $diet_data->diet_directions,
                            'diet_calories' => $diet_data->diet_calories,
                            'diet_carbs' => $diet_data->diet_carbs,
                            'diet_protein' => $diet_data->diet_protein,
                            'diet_fat' => $diet_data->diet_fat,
                            'diet_time' => $diet_data->diet_time,
                            'diet_servings' => $diet_data->diet_servings,
                            'diet_featured' => $diet_data->diet_featured,
                            'diet_image_link' => $diet_data->diet_image_link,
                            'diet_status' => $diet_data->diet_status,
                            'diet_price' => $diet_data->diet_price,
                            'diet_image' =>$this->base_url . '/images/'.$diet_data->diet_image,
                            'diet_image_link' => $diet_data->diet_image_link,
                            'meal_time' => $diet_data->meal_time,
                            'meal_type' => $diet_data->meal_type
                        ];
                    }
                }
           }else{
                $diet_data_arr = [];
           }
    // Construct the final response
    $response = [
        'event_details' => $details ? $details : 'Not any subscription',
        'profile' => $userData,
        'diet_data' =>$diet_data_arr,
        'workout_data' => $workout_data,
        'additional_data' => ['term_condition' => $term_condition,'location' => $location,
      
    ]
    ];

    return response()->json($response);
    // $response = [
    //             'event_details' => $details ? $details : 'Not any subscription',
    //             'profile' => [is_array($userData) ? $userData : (array) $userData],
    //             'workout_data' => is_array($workout_data) ? $workout_data : (array) $workout_data,
    //             'additional_data' => [
    //             'term_condition' => is_array($term_condition) ? $term_condition : (array) $term_condition,
    //             'location' => is_array($location) ? $location : (array) $location,
    //         ]
    //     ];
        
    //     return response()->json($response);
}
  public function testing1_event_exercise_complete_status(Request $request)
{
    $day = $request->day;
    $id = $request->id;
    $workout_id = $request->workout_id;
    $user_id = $request->user_id;
    $version = $request->input('version');
    $next_status = $request->next_status;
    $prev_status = $request->prev_status;
    $skip_status = $request->skip_status;
    $current_date = Carbon::now();
    $current_date_gmt_plus_5_30 = $current_date->setTimezone('Asia/Kolkata'); // GMT+5:30 timezone

    $only_date = $current_date_gmt_plus_5_30->toDateString(); // Get only the date part
    $only_time = $current_date_gmt_plus_5_30->toTimeString(); // Get only the time part

    $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
    $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
    $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
    $type = $request->input('type');
       
        if ($type === "cardio") {
            $dbtable = 'cardio_exercise_complete_status';
        } else {
            $dbtable = 'event_exercise_completion_status';
        
        
        }

    if ($versions_current || $versions_middle) {
        $status = DB::table($dbtable)
            ->select('exercise_status')
            ->where('id', $id)
            ->first();

        if ($status) {
            if ($status->exercise_status == 'undone') {
                // Update the exercise status to completed
                DB::table($dbtable)
                    ->where('id', $id)
                    ->update([
                        'exercise_status' => 'completed',
                        'completed_date' => $only_date,
                        'completed_time' => $only_time,
                        'next_status' => $next_status,
                        'prev_status' => $prev_status,
                        'skip_status' => $skip_status
                    ]);

                  // Get the last completed exercise status
                $lastCompletedStatus = DB::table($dbtable)
                    ->select('completed_date', 'completed_time')
                    ->where('user_id', $user_id)
                    ->where('exercise_status', 'completed')
                    ->orderBy('completed_date', 'desc')
                    ->orderBy('completed_time', 'desc')
                    ->skip(1) // Skip the current one
                    ->first();

                if ($lastCompletedStatus) {
                    $lastCompletedDateTime = Carbon::parse($lastCompletedStatus->completed_date . ' ' . $lastCompletedStatus->completed_time);
                    $currentCompletedDateTime = Carbon::parse($only_date . ' ' . $only_time);

                    $diffInHours = $lastCompletedDateTime->diffInHours($currentCompletedDateTime);
                      

                    // Check if the current time is greater than the last completed time
                     if ($currentCompletedDateTime->greaterThan($lastCompletedDateTime) && $diffInHours > 24) {
                     $adjustedHours = $diffInHours - 24;
                     
                       
                        // Check if points have already been deducted for this period
                        $alreadyDeducted = DB::table($dbtable)
                            ->where('user_id', $user_id)
                          // ->where('completed_date', $lastCompletedStatus->completed_date)
                            ->where('deducted', 1)
                            ->exists();

                        if (!$alreadyDeducted) {
                            // Deduct points based on the number of hours difference
                            
                              $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
                              $current_Day = $indiaTime->format('l');
                              
                            DB::table('users')
                                ->where('id', $user_id)
                                ->decrement('fit_coins', $adjustedHours);
                                
                                 DB::table('fitcoin_history')->insert([
                                    'user_id'   =>$user_id,
                                    'info'=>'late',
                                    'fit_coins' => $adjustedHours,
                                    'current_day' =>$current_Day
                                    ]);
                                
                                 DB::table($dbtable)
                                ->where('user_id', $user_id)
                                ->decrement('today_earning', $adjustedHours);    

                            // Update the record to mark points as deducted
                            DB::table($dbtable)
                                ->where('id', $id)
                                ->update(['deducted' => 1]);
                        }
                    }
                }


                // Check if all exercises for the day are completed
                $dayStatuses = DB::table($dbtable)
                    ->select('exercise_status')
                    ->where('user_day', $day)
                    ->where('workout_id', $workout_id)
                    ->where('user_id', $user_id)
                    ->get();

                $allCompleted = $dayStatuses->every(function ($status) {
                    return $status->exercise_status == 'completed';
                });

                if ($allCompleted) {
                    DB::table($dbtable)
                        ->where('user_day', $day)
                        ->where('workout_id', $workout_id)
                        ->where('user_id', $user_id)
                        ->update(['final_status' => 'allcompleted']);
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
        return response()->json(['msg' => 'Please update the app to the latest version.']);
    }
}
   public function test1_add_coins(Request $request)
{
    $user_id = $request->user_id;
    $user_day = $request->user_day;
    
      $type = $request->input('type');
       
        if ($type === "cardio") {
            $dbtable = 'cardio_exercise_complete_status';
        } else {
            $dbtable = 'event_exercise_completion_status';
    
        }

    $check_data = DB::table($dbtable)
        ->where('user_id', $user_id)
        ->where('user_day', $user_day)
        ->where('exercise_status', 'completed')
        ->first();
        
        

    if ($check_data) {
        
        $coins = $check_data->fit_coins;

        $skip_status = DB::table($dbtable)
            ->where('user_id', $user_id)
            ->where('user_day', $user_day)
            ->where('exercise_status', 'completed')
            ->whereNotNull('skip_status')
            ->sum('skip_status');
     

        $prev_status = DB::table($dbtable)
            ->where('user_id', $user_id)
            ->where('user_day', $user_day)
            ->where('exercise_status', 'completed')
            ->whereNotNull('prev_status')
            ->sum('prev_status');

        $next_status = DB::table($dbtable)
            ->where('user_id', $user_id)
            ->where('user_day', $user_day)
            ->where('exercise_status', 'completed')
            ->whereNotNull('next_status')
            ->sum('next_status');

        $total_mineus_coins = $next_status + $prev_status + $skip_status;
        $total_coin_add = $coins - $total_mineus_coins;
       

        DB::table('users')->where('id', $user_id)->update([
            'fit_coins' => DB::raw('fit_coins + ' . $total_coin_add),
        ]);

        DB::table($dbtable)
            ->where('user_id', $user_id)
            ->where('user_day', $user_day)
            // ->where('exercise_status', 'completed')
            ->update(['today_earning' => $total_coin_add]);
            
         $completed_exercise = DB::table($dbtable)
            ->where('user_id', $user_id)
            ->where('user_day', $user_day)
            ->where('exercise_status', 'completed')
            ->count();
            

        return response()->json([
             'msg' => 'coin added successfully',
             'coins' =>$total_coin_add,
             'skip_status' =>$skip_status,
             'next_status' =>$next_status,
             'prev_status' =>$prev_status,
             'completed_exercise' => $completed_exercise
            
        ]);
    }

    return response()->json([
        'msg' => 'no data found'
    ]);
}

    // public function past_winners(Request $request)
    // {
        
    //      $version = $request->input('version');
         
    //      if(!$version){
    //           return response()->json([
    //             'msg' => 'version is required'
    //         ]);
    //      }
        
    //     $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
    //     $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
    //     $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
    //     if ($versions_current) {
        
    //         $data = DB::table('winner_history')
    //             ->join('users', 'users.id', '=', 'winner_history.user_id')
    //             ->select('users.name', 'users.fit_coins', 'users.id', 'users.image', 'winner_history.price', 'winner_history.week')
    //             ->orderBy('winner_history.week', 'desc')
    //             ->get();
                
    //             $winner_data=[];
    //         if($data){
             
    //           foreach ($data as $winners)
    //           {
    //                 // Determine the week suffix
    //                 if ($winners->week == 1) 
    //                 {
    //                     $week_suffix = "st";
    //                 } elseif ($winners->week == 2)
    //                 {
    //                     $week_suffix = "nd";
    //                 } elseif ($winners->week == 3)
    //                 {
    //                     $week_suffix = "rd";
    //                 } else
    //                 {
    //                     $week_suffix = "th";
    //                 }
    //               if (!isset($winners->image) || empty($winners->image)) {
    //                     $image_link = null;
    //                 } else {
    //                     $image_link =$this->base_url . "/adserver/public/storage/image/" . $winners->image;
    //                 }
                    
                    
    //                 $winner_data[] = [
    //                     'name' => $winners->name,
    //                     'fit_coins' => $winners->fit_coins,
    //                     'user_id' => $winners->id,
    //                     'image' => $image_link,
    //                     'price' => $winners->price,
    //                     'week' => $winners->week . $week_suffix,
    //                 ];
    //         }
    
    //               return response()->json([
    //                     'data' => $winner_data
    //                 ]);
    //         }
    //         return response()->json([
    //                     'msg' => "no data found"
    //                 ]);
                    
    //         }       
    //     if ($versions_middle) {
        
    //          $data = DB::table('winner_history')
    //             ->join('users', 'users.id', '=', 'winner_history.user_id')
    //             ->select('users.name', 'users.fit_coins', 'users.id', 'users.image', 'winner_history.price', 'winner_history.week')
    //             ->orderBy('winner_history.week', 'desc')
    //             ->get();
                
    //             $winner_data=[];
    //         if($data){
             
    //           foreach ($data as $winners)
    //           {
    //                 // Determine the week suffix
    //                 if ($winners->week == 1) 
    //                 {
    //                     $week_suffix = "st";
    //                 } elseif ($winners->week == 2)
    //                 {
    //                     $week_suffix = "nd";
    //                 } elseif ($winners->week == 3)
    //                 {
    //                     $week_suffix = "rd";
    //                 } else
    //                 {
    //                     $week_suffix = "th";
    //                 }
                    
                    
                    
    //               if (!isset($winners->image) || empty($winners->image)) {
    //                     $image_link = null;
    //                 } else {
    //                     $image_link =$this->base_url . "/adserver/public/storage/image/" . $winners->image;
    //                 }
                    
                    
    //                 $winner_data[] = [
    //                     'name' => $winners->name,
    //                     'fit_coins' => $winners->fit_coins,
    //                     'user_id' => $winners->id,
    //                     'image' => $image_link,
    //                     'price' => $winners->price,
    //                     'week' => $winners->week . $week_suffix,
    //                 ];
    //         }
    
    //               return response()->json([
    //                     'data' => $winner_data
    //                 ]);
    //         }
    //         return response()->json([
    //                     'msg' => "no data found"
    //                 ]);
                    
                    
    //         }
    //     if ($versions_past)
    //     {
        
    //         $data = DB::table('winner_history')
    //             ->join('users', 'users.id', '=', 'winner_history.user_id')
    //             ->select('users.name', 'users.fit_coins', 'users.id', 'users.image', 'winner_history.price', 'winner_history.week')
    //             ->orderBy('winner_history.week', 'desc')
    //             ->get();
                
    //             $winner_data=[];
    //         if($data){
             
    //           foreach ($data as $winners)
    //           {
    //                 // Determine the week suffix
    //                 if ($winners->week == 1) 
    //                 {
    //                     $week_suffix = "st";
    //                 } elseif ($winners->week == 2)
    //                 {
    //                     $week_suffix = "nd";
    //                 } elseif ($winners->week == 3)
    //                 {
    //                     $week_suffix = "rd";
    //                 } else
    //                 {
    //                     $week_suffix = "th";
    //                 }
                    
    //                
    //         }
    
    //               return response()->json([
    //                     'data' => $winner_data
    //                 ]);
    //         }
          
                    
    //   }
    //     return response()->json([
    //             'msg' => 'Please update the app to the latest version.'

    //         ]);
    // }
//   public function test_coin_deduct_history(Request $request){
//                 $user_id = $request->user_id;
//                 // $get_coins = $request->coins;
//                 $get_coins = 1;
//                 $day = $request->day;
//                 $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
//                 $currentDay = $indiaTime->format('l');
                
                
//                 $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
//                     if (!in_array($day, $weekdays)) {
//                         return response()->json([
//                             'msg' => 'please enter valid day'
//                         ]);
                      
//                     } 
//                     if (!$get_coins) {
//                         return response()->json([
//                             'msg' => 'coin is required'
//                         ]);
                      
//                     } 
        
//                     if(!$user_id){
//                           return response()->json([
//                                 'msg' => 'user id is required'
//                             ]);
//                     }
//                   $check_data = DB::table('event_exercise_completion_status')
//                     ->where('user_id', $user_id)
//                     ->first();
                
                
//                  // week_day points -------------------------------------------------------- done no need to validate
//                  $currentDay = $indiaTime->format('l');
//                 $week_day_data = [];
//                 $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                
//                 // Find the index of the current day in the weekdays array
//                 $currentDayIndex = array_search($currentDay, $weekdays);
                
//                 foreach ($weekdays as $index => $day) {
//                     if ($index <= $currentDayIndex) {
//                         // Retrieve the points for the current day and days before it
//                         $points = DB::table('event_exercise_completion_status')
//                             ->where('user_id', $user_id)
//                             ->where('user_day', $day)
//                             ->first();
//                         if ($points) {
//                             $week_day_data[$day] = $points->today_earning;
//                         } else {
//                             $week_day_data[$day] = 0;
//                         }
//                     } else {
//                         // Set to null for days after the current day
//                         $week_day_data[$day] = null;
//                     }
//                 }
                    
//                     // single_exercise-----------------------------------------
//                         $single_exercise =[];
//                         $exercise_count =DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','single_exercise')->count();
//                       $exercises = DB::table('fitcoin_history')
//                             ->where('user_id', $user_id)
//                             ->where('info', 'single_exercise')
//                             ->get();

//                         if ($exercises->isNotEmpty()) {
//                             $exerciseData = [];
//                             foreach ($exercises as $exercise) {
//                                 // Ensure 'exercise_id' is a valid column in your 'fitcoin_history' table
//                                 $exerciseDetails = DB::table('exercises')
//                                     ->where('exercise_id', $exercise->exercise_id)
//                                     ->first();
//                                 if ($exerciseDetails) {
//                                     $exerciseData[] = $exerciseDetails;
//                                 }
//                             }
//                         } else {
//                             $exerciseData = [];
//                         }
                        
//                   $check_monday_data = DB::table('event_exercise_completion_status')
//                         ->where('user_id', $user_id)
//                         ->where('user_day',$day)
//                         ->first();
                    
//                         $monday=[];
                  
//                 //point deduction----------------------------------------------------
//                         $point_deduction =[];
//                          $skip_status_monday = DB::table('event_exercise_completion_status')
//                             ->where('user_id', $user_id)
//                             ->where('user_day', $day)
//                             ->where('exercise_status', 'completed')
//                             ->whereNotNull('skip_status')
//                             ->sum('skip_status');
                            
//                         $point_deduction['skip_status']= $skip_status_monday;
                        
//                         $prev_status_monday = DB::table('event_exercise_completion_status')
//                             ->where('user_id', $user_id)
//                             ->where('user_day', $day)
//                             ->where('exercise_status', 'completed')
//                             ->whereNotNull('prev_status')
//                             ->sum('prev_status');
                            
//                         $point_deduction['prev_status']= $prev_status_monday;
                            

                
//                         $next_status_monday = DB::table('event_exercise_completion_status')
//                             ->where('user_id', $user_id)
//                             ->where('user_day', $day)
//                             ->where('exercise_status', 'completed')
//                             ->whereNotNull('next_status')
//                             ->sum('next_status');
                     
//                              $point_deduction['next_status']= $next_status_monday;
//                              $late = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','late')->count();
//                              $point_deduction['delay']= $late;
                            
                            
//                         // workout section --------------------------------------------------------
//                             // cardio 
//                              $cardio =[];
                            
                            
//                           $cardio_status_monday = DB::table('cardio_exercise_complete_status')
//                             ->where('user_id', $user_id)
//                             ->where('user_day', $day)
//                             ->where('final_status', 'allcompleted')
//                             // ->where('cardio_status','done')
//                              ->first();
                             
//                              $total_cardio_points =0;
//                             if($cardio_status_monday){
        
//                               $skip_cardio_status_monday = DB::table('cardio_exercise_complete_status')
//                                 ->where('user_id', $user_id)
//                                 ->where('user_day', $day)
//                                 // ->where('final_status', 'allcompleted')
//                                 ->where('exercise_status', 'completed')
//                                 ->whereNotNull('skip_status')
//                                 ->sum('skip_status');
                                
//                                 $cardio['skip_status']= $skip_cardio_status_monday;
                                
//                                 // dd($skip_status_monday);
                         
                    
//                             $prev_cardio_status_monday = DB::table('cardio_exercise_complete_status')
//                                 ->where('user_id', $user_id)
//                                 ->where('user_day', $day)
//                                 // ->where('final_status', 'allcompleted')
//                                 ->where('exercise_status', 'completed')
//                                 ->whereNotNull('prev_status')
//                                 ->sum('prev_status');
                                
//                                 $cardio['prev_status']= $prev_cardio_status_monday;
                    
//                             $next_cardio_status_monday = DB::table('cardio_exercise_complete_status')
//                                 ->where('user_id', $user_id)
//                                 ->where('user_day', $day)
//                                 // ->where('final_status', 'allcompleted')
//                                 // ->where('exercise_status', 'completed')
//                                 ->whereNotNull('next_status')
//                                 ->sum('next_status');
//                                  $cardio['next_status']= $next_cardio_status_monday;
//                                  $total_cardio_deduction = $next_cardio_status_monday+$prev_cardio_status_monday+$skip_cardio_status_monday;
//                                  $total_cardio_points =$cardio_coins-$total_cardio_deduction;
//                                  $monday['cardio']= $total_cardio_points;
//                             }
//                              $cardio['cardio']= $total_cardio_points;
                            
                            
//                         $countSessionA = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_a', 'done')->count();
//                         $countSessionB = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_b', 'done')->count();
//                         $countSessionC = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_c', 'done')->count();
//                         $countSessionD = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_d', 'done')->count();
                        
//                         $total_coins= ($countSessionA+$countSessionB+$countSessionC+$countSessionD)*$get_coins;
//                         $cardio['breath_in']=0;
//                         if($countSessionA || $countSessionB || $countSessionC || $countSessionD){
//                               $total_coins= ($countSessionA+$countSessionB+$countSessionC+$countSessionD)*$get_coins;
//                              $cardio['breath_in']=$total_coins;
//                         }
                            
//                 // event overview  --------------------------------------------------------
//                 $event_overview =[];
                
               
//                 $eventoverview = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->get();
//                 // dd($eventoverview);
//                 if($eventoverview->isNotEmpty()){
                    
//                       $total_exercises = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
//                       $event_overview['exercises'] =$total_exercises ;
                       
//                       $dateTime = Carbon::parse($eventoverview[0]->created_at)->setTimezone('Asia/Kolkata');
        
//                         $Weekday = $dateTime->format('l');
        
//                         $formattedDateTime = $dateTime->format('M-d-y');
//                         $formattedTime = $dateTime->format('h:iA');
        
//                           $event_overview['day'] =$day;
//                           $event_overview['date'] =$formattedDateTime;
//                           $event_overview['time'] =$formattedTime;
                          
//                           $missed_exercises = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->where('exercise_status','undone')->count();
//                           $event_overview['missed'] =$missed_exercises;
                          
//                 }
                
                   
//                      // refer rewards --------------------------------------------------------
//                          $refer_rewards =[];
//                          $register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('current_day',$day)->where('info','referral_registerd')->count();
//                          $event_register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('current_day',$day)->where('info','event_registerd')->count();
                         
//                          $reward_data['register'] =$register_data;
//                          $reward_data['event_register'] =$event_register_data;
//                          $total_register_points = $register_data*2;
//                          $total_event_register_points = $event_register_data*3;
//                          $reward_data['total_rewards_points'] =$event_register_data+$event_register_data;
                         
                    
                            

                        
                        
//                         // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
//                         // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
//                         // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
//                         // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
//                         // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
//                         // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
//                         // dd($monday_points);
//                         // $week_day_data['Monday'] =$monday_points;
//                         // $week_day_data['Tuesday'] =$monday_points;
//                         // $week_day_data['Wednesday'] =$monday_points;
//                         // $week_day_data['Thruesday'] =$thursday_points;
//                         // $week_day_data['Friday'] =$monday_friday;    
        
//                  $register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','referral_registerd')->count();
//                  $event_register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','event_registerd')->count();
                
//                  $day_missed = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','day_missed')->count();
//                  $custom_wokrout_coins = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','create_custom_wokrout')->count();
                
//                  $register_object = (object) $register_data;
//                  $response = [
//                     // $day => $monday,
//                     'points'=>$week_day_data,
//                     'point_deduction'=>$point_deduction,
//                     'workout'=>$cardio,
//                     'event_overview'=>$event_overview,
//                     'refer_rewards'=>$reward_data,
//                     'exercises' =>$exerciseData,
//                     // 'tuesday' => $tuesday,
//                     // 'wednesday' => $wednesday,
//                     // 'thursday' => $thursday,
//                     // 'friday' => $friday,
//                     // 'total_registerd' => $register_data,
//                     // 'total_event_registerd'=>$event_register_data,
//                     // 'late_exercises'=>$late,
//                     // 'custom_wokrout_coins' =>$custom_wokrout_coins,
//                     // 'missing_days_count'=>$missingDaysCount,
//                     // 'missing_days'=>$missedDays,
//                 ];
        
//                 return response()->json($response);
//             // }
        
//             // return response()->json([
//             //     'msg' => 'no data found'
//             // ]);
//          }

  public function testing_event_details($id){
       $currentDay = Carbon::now('Asia/Kolkata')->dayOfWeek;
    //   $currentDay="Monday";

        $details = DB::table('fitme_event')
            ->select('*')
            ->where('user_id', $id)
            ->orderByDesc('id') // Assuming 'id' is the primary key
            ->first();
            
           if($details->event_start_date_current==null && $details->event_start_date_upcoming ==null){
               $today = Carbon::now();
                $monday = $today->startOfWeek(Carbon::MONDAY);
            
                $mondayDate = $monday->format('Y-m-d');


                $details->event_start_date_current = $mondayDate;
           }
    
        if($details){
            $details->currentDay = $currentDay; // Add current day to details object
            return response()->json(['data' => $details]);
        } else {
            return response()->json(['message' => 'Not any subscription']);
        }
}
public function test_get_breathinout_session(Request $request)
    {
        // Get current time in 'HH:mm' format in the 'Asia/Kolkata' timezone
        $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        $currentHour = $indiaTime->format('g:i a');
        $currentHour24 = $indiaTime->format('H:i');
        $user_id = $request->input('user_id');
        $current_day = $indiaTime->format('l');
    
        // Define the sessions with their start and end times in 12-hour format
        $sessions = [
            ['start' => '6:00AM', 'end' => '7:00AM', 'message' => 'Breath in session start', 'title' => 'A'],
            ['start' => '11:00AM', 'end' => '12:00PM', 'message' => 'Breath in session start', 'title' => 'B'],
            // ['start' => '4:00PM', 'end' => '5:00PM', 'message' => 'Breath in session start', 'title' => 'C'],
            ['start' => '9:00AM', 'end' => '11:00PM', 'message' => 'Breath in session start', 'title' => 'C'],
            ['start' => '9:00PM', 'end' => '10:00PM', 'message' => 'Breath in session start', 'title' => 'D']
        ];
    
        // Function to convert 12-hour time to 24-hour time for comparison
        function convertTo24Hour($time)
        {
            return Carbon::createFromFormat('g:i a', $time)->format('H:i');
        }
    
        $sessionsWithStatus = [];
    
        // Determine the status of each session
        foreach ($sessions as $session) {
            $sessionStart = convertTo24Hour($session['start']);
            $sessionEnd = convertTo24Hour($session['end']);
            
            $lowercase_title = strtolower($session['title']);
            
            // Fixing the query and retrieving the status
            $check_complete_Status = DB::table('breathin_session')
                ->where('user_id', $user_id)
                ->where('session_'.$lowercase_title,'done')
                ->where('day',$current_day)
                ->get();
                
                // if($check_complete_Status->isEmpty()){
                //     echo "yes";
                // }else{
                //     echo "no";
                // }
                
                // print_r($check_complete_Status);
                // die();
        //   dd($check_complete_Status->user_id);

        $com_status = $check_complete_Status->isEmpty() ? false : true;
    
            if ($currentHour24 >= $sessionStart && $currentHour24 < $sessionEnd) {
                $status = 'open';
                $statusMessage = 'Session is open';
            } elseif ($currentHour24 < $sessionStart) {
                $status = 'upcoming';
                $statusMessage = 'Session is upcoming';
            } else {
                $status = 'closed';
                $statusMessage = 'Session is closed';
            }
            
            $sessionsWithStatus[] = [
                'start_time' => $session['start'],
                'end_time' => $session['end'],
                'title' => $session['title'],
                'status' => $status,
                'status_message' => $statusMessage,
                'fit_coins' => 1,
                'complete_status' => $com_status 
            ];
        }
    
        return response()->json([
            'sessions' => $sessionsWithStatus
        ]);
    }
    public function testnew_coin_deduction_rec(Request $request){
        $user_id = $request->user_id;
        // $get_coins = $request->coins;
        $get_coins = 1;
        $day = $request->day;
        $day_Data = $day;
        $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        $currentDay = $indiaTime->format('l');
        
        
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            if (!in_array($day, $weekdays)) {
                return response()->json([
                    'msg' => 'please enter valid day'
                ]);
              
            } 
            // if (!$get_coins) {
            //     return response()->json([
            //         'msg' => 'coin is required'
            //     ]);
              
            // } 

            if(!$user_id){
                  return response()->json([
                        'msg' => 'user id is required'
                    ]);
            }
          $check_data = DB::table('event_exercise_completion_status')
            ->where('user_id', $user_id)
            ->first();
        
            
            
    
        // if ($check_data) {
        //single_exercise
          $check_monday_data = DB::table('event_exercise_completion_status')
                ->where('user_id', $user_id)
                ->where('user_day',$day)
                ->first();
            
                $monday=[];
          
                 // point deduction 
                $point_deduction =[];
                 $skip_status_monday = DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('skip_status')
                    ->sum('skip_status');
                    
                $point_deduction['skip_status']= $skip_status_monday;
        
                $prev_status_monday = DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('prev_status')
                    ->sum('prev_status');
                    
                    $point_deduction['prev_status']= $prev_status_monday;
        
                $next_status_monday = DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('next_status')
                    ->sum('next_status');
                    
                     $point_deduction['next_status']= $next_status_monday;
                     $late = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','late')->count();
                     $point_deduction['delay']= $late;
                     
                     $total_event_coin_deduction = $skip_status_monday+$prev_status_monday+$next_status_monday;
                    
                    
                    // workout section --------------------------------------------------------
                    // cardio 
                     $cardio =[];
                    
                    
                  $cardio_status_monday = DB::table('cardio_exercise_complete_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $day)
                    ->where('final_status', 'allcompleted')
                    // ->where('cardio_status','done')
                     ->first();
                     
                    //  dd($cardio_status_monday);
                    
                     
                     $total_cardio_points =0;
                    if($cardio_status_monday){

                      $skip_cardio_status_monday = DB::table('cardio_exercise_complete_status')
                        ->where('user_id', $user_id)
                        ->where('user_day', $day)
                        // ->where('final_status', 'allcompleted')
                        ->where('exercise_status', 'completed')
                        ->whereNotNull('skip_status')
                        ->sum('skip_status');
                        
                        $cardio['skip_status']= $skip_cardio_status_monday;
                        
                        // dd($skip_status_monday);
                 
            
                    $prev_cardio_status_monday = DB::table('cardio_exercise_complete_status')
                        ->where('user_id', $user_id)
                        ->where('user_day', $day)
                        // ->where('final_status', 'allcompleted')
                        ->where('exercise_status', 'completed')
                        ->whereNotNull('prev_status')
                        ->sum('prev_status');
                        
                        $cardio['prev_status']= $prev_cardio_status_monday;
            
                    $next_cardio_status_monday = DB::table('cardio_exercise_complete_status')
                        ->where('user_id', $user_id)
                        ->where('user_day', $day)
                        // ->where('final_status', 'allcompleted')
                        // ->where('exercise_status', 'completed')
                        ->whereNotNull('next_status')
                        ->sum('next_status');
                         $cardio['next_status']= $next_cardio_status_monday;
                         $total_cardio_deduction = $next_cardio_status_monday+$prev_cardio_status_monday+$skip_cardio_status_monday;
                         $total_cardio_points =$cardio_coins-$total_cardio_deduction;
                         $monday['cardio']= $total_cardio_points;
                    }
                     $cardio['cardio']= $total_cardio_points;
                    
                   
                    
                $countSessionA = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_a', 'done')->count();
                $countSessionB = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_b', 'done')->count();
                $countSessionC = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_c', 'done')->count();
                $countSessionD = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_d', 'done')->count();
                
                $total_breath_coins= ($countSessionA+$countSessionB+$countSessionC+$countSessionD)*$get_coins;
                $cardio['breath_in']=0;
                if($countSessionA || $countSessionB || $countSessionC || $countSessionD){
                      $total_coins= ($countSessionA+$countSessionB+$countSessionC+$countSessionD)*$get_coins;
                     $cardio['breath_in']=$total_breath_coins;
                }
                    
                    
        // event overview  --------------------------------------------------------
        
        $event_overview =[];
        
        $eventoverview = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->get();
        if($eventoverview->isNotEmpty()){
            
              $total_exercises = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
              $event_overview['exercises'] =$total_exercises ;
            //   dd($eventoverview[0]->created_at);
               
              $dateTime = Carbon::parse($eventoverview[0]->created_at)->setTimezone('Asia/Kolkata');

                $Weekday = $dateTime->format('l');
                
                // Format the date and time
                $formattedDateTime = $dateTime->format('M-d-y');
                $formattedTime = $dateTime->format('h:iA');
                
                //   $event_overview['day'] =$Weekday ;
                  $event_overview['day'] =$day;
                  $event_overview['date'] =$formattedDateTime;
                  $event_overview['time'] =$formattedTime;
                  
                  $missed_exercises = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->where('exercise_status','undone')->count();
                  $event_overview['missed'] =$missed_exercises;
                  
        }
        
           
             // refer rewards --------------------------------------------------------
             $refer_rewards =[];
             $register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('current_day',$day)->where('info','referral_registerd')->count();
             $event_register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('current_day',$day)->where('info','event_registerd')->count();
             
             $reward_data['register'] =$register_data;
             $reward_data['event_register'] =$event_register_data;
             $total_register_points = $register_data*2;
             $total_event_register_points = $event_register_data*3;
             $reward_data['total_rewards_points'] =$event_register_data+$event_register_data;
             
         // week_day points --------------------------------------------------------
                          $currentDay = $indiaTime->format('l');
                            $week_day_data = [];
                            $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                            
                            // Find the index of the current day in the weekdays array
                            $currentDayIndex = array_search($currentDay, $weekdays);
                            
                            foreach ($weekdays as $index => $day) {
                                if ($index <= $currentDayIndex) {
                                    // Retrieve the points for the current day and days before it
                                    $points = DB::table('event_exercise_completion_status')
                                        ->where('user_id', $user_id)
                                        ->where('user_day', $day)
                                        ->first();
                                    if ($points) {
                                        $week_day_data[$day] = $points->today_earning;
                                    } else {
                                        $week_day_data[$day] = 0;
                                    }
                                } else {
                                    // Set to null for days after the current day
                                    $week_day_data[$day] = null;
                                }
                            }
                    // dd($week_day_data);
                    
                    // single_exercise
                    $single_exercise =[];
                    
                    $exercise_count =DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','single_exercise')->count();
                    $exercises = DB::table('fitcoin_history')
                      ->where('user_id',$user_id)
                      ->where('info','single_exercise')
                      ->get();
                      
                      $exercises = [];
 
                     $exerciseData=[];
                        foreach ($exercises as $exercise_id) {
                    
                            $exerciseData = DB::table('exercises')
                                              ->where('exercise_id', $exercise_id)
                                              ->first();
    
                            if ($exerciseData) {
                               
                                $exercises[] = $exerciseData;
                            }
                            
                        }
                   
                        foreach($week_day_data as $days =>$points){
                        
                                $countSessionA = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $days)->where('session_a', 'done')->count();
                                $countSessionB = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $days)->where('session_b', 'done')->count();
                                $countSessionC = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $days)->where('session_c', 'done')->count();
                                $countSessionD = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $days)->where('session_d', 'done')->count();
                                
                                $total_breath_coins= ($countSessionA+$countSessionB+$countSessionC+$countSessionD)*$get_coins;
                                $cardio['breath_in']=0;
                                if($countSessionA || $countSessionB || $countSessionC || $countSessionD){
                                      $total_coins= ($countSessionA+$countSessionB+$countSessionC+$countSessionD)*$get_coins;
                                     $cardio['breath_in']=$total_breath_coins;
                                }
                                $week_day_data[$days] +=$total_breath_coins + $total_cardio_points;
                                
                                
                                
                                
                                $check_monday_data = DB::table('event_exercise_completion_status')
                                    ->where('user_id', $user_id)
                                    ->where('user_day',$days)
                                    ->first();
                                    
                               if($check_monday_data){
                                   
                                $monday=[];
                          
                                 // point deduction 
                                $point_deduction =[];
                                 $skip_status_monday = DB::table('event_exercise_completion_status')
                                    ->where('user_id', $user_id)
                                    ->where('user_day', $days)
                                    ->where('exercise_status', 'completed')
                                    ->whereNotNull('skip_status')
                                    ->sum('skip_status');
                                    
                                // $point_deduction['skip_status']= $skip_status_monday;
                        
                                $prev_status_monday = DB::table('event_exercise_completion_status')
                                    ->where('user_id', $user_id)
                                    ->where('user_day', $days)
                                    ->where('exercise_status', 'completed')
                                    ->whereNotNull('prev_status')
                                    ->sum('prev_status');
                                    
                                    // $point_deduction['prev_status']= $prev_status_monday;
                        
                                $next_status_monday = DB::table('event_exercise_completion_status')
                                    ->where('user_id', $user_id)
                                    ->where('user_day', $days)
                                    ->where('exercise_status', 'completed')
                                    ->whereNotNull('next_status')
                                    ->sum('next_status');
                                    
                                    //  $point_deduction['next_status']= $next_status_monday;
                                    //  $late = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','late')->count();
                                    //  $point_deduction['delay']= $late;
                                     
                                     $total_event_coin_deduction = $skip_status_monday+$prev_status_monday+$next_status_monday;
                                      $week_day_data[$days] =  $week_day_data[$days]-$total_event_coin_deduction;
                                   
                               }
                        }
                      
                      
                      
                    
                    
                
                
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // dd($monday_points);
                // $week_day_data['Monday'] =$monday_points;
                // $week_day_data['Tuesday'] =$monday_points;
                // $week_day_data['Wednesday'] =$monday_points;
                // $week_day_data['Thruesday'] =$thursday_points;
                // $week_day_data['Friday'] =$monday_friday;
                 
                //  $total_coins_to_add = 
                
                // dd($week_day_data[$day_Data] );

         $register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','referral_registerd')->count();
         $event_register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','event_registerd')->count();
        
         $day_missed = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','day_missed')->count();
         $custom_wokrout_coins = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','create_custom_wokrout')->count();
        
         $register_object = (object) $register_data;
         $response = [
            // $day => $monday,
            'points'=>$week_day_data,
            'point_deduction'=>$point_deduction,
            'workout'=>$cardio,
            'event_overview'=>$event_overview,
            'refer_rewards'=>$reward_data,
            'exercises' =>$exerciseData,
            // 'tuesday' => $tuesday,
            // 'wednesday' => $wednesday,
            // 'thursday' => $thursday,
            // 'friday' => $friday,
            // 'total_registerd' => $register_data,
            // 'total_event_registerd'=>$event_register_data,
            // 'late_exercises'=>$late,
            // 'custom_wokrout_coins' =>$custom_wokrout_coins,
            // 'missing_days_count'=>$missingDaysCount,
            // 'missing_days'=>$missedDays,
        ];

        return response()->json($response);
    // }

    // return response()->json([
    //     'msg' => 'no data found'
    // ]);
 }
 
   public function test_get_all_weekday_exercise(Request $request)   //update 29-08
    {
        $user_id = $request->input('user_id');    
        $version = $request->input('version');
        $equipment = $request->input('equipment');
        // dd($equipment);
        $days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        
        // Check if the version is valid
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        
        if ($versions_current === null && $versions_middle === null && $versions_past === null) {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'
            ]);
        }
        
        // Check if user exists
        $check_user = DB::table('users')->where('id', $user_id)->first();
        if ($check_user === null) {
            return response()->json(['msg' => 'User not exist.']);
        }
        
        $gender = $check_user->gender;
        
        // Initialize the response array
        $response = [];
        
        foreach ($days as $day) {
            $exercise_data = DB::table('user_edit_exercise')
                ->where('day', $day)
                ->where('user_id', $user_id)
                ->get();
        
            if ($exercise_data->isEmpty()) {
                $exercise_data = DB::table('week_day_exercise')->where('day', $day)->get();
            }
        
            if ($exercise_data->isEmpty()) {
                $response[$day] = ['msg' => 'No data found for day ' . $day];
                continue;
            }
        
            $exercise_json = [];
            $title = $exercise_data[0]->title ?? '';
            $image = $exercise_data[0]->image ?? '';
            $total_coins = $exercise_data[0]->total_coins ?? 0;
    
            // Default empty arrays
            $exercise_ids = [];
            $exercise_ids_equipment = [];
        
            if ($gender == "Male") {
               
                if($equipment=='yes'){
                       
                    $exercise_ids = json_decode($exercise_data[0]->male_exercise_id_equipment ?? '[]', true);
                }else{
                   
                     $exercise_ids = json_decode($exercise_data[0]->male_exercise_id ?? '[]', true);
                }
              
            } else {
                  if($equipment=="yes"){
                        
                     $exercise_ids = json_decode($exercise_data[0]->female_exercise_id_equipment ?? '[]', true);
                    }else{
                       
                         $exercise_ids = json_decode($exercise_data[0]->female_exercise_id ?? '[]', true);
                    }
                
            }
        
            $unique_exercise_ids = array_unique($exercise_ids);
            $exercise_json_equipment = [];
    
            foreach ($unique_exercise_ids as $exercise_id) {
                $exercise_detail = DB::table('exercises')->where('exercise_id', $exercise_id)->first();
                if ($exercise_detail) {
                    $exercise_json[] = [
                        'exercise_id' => $exercise_detail->exercise_id,
                        'exercise_title' => $exercise_detail->exercise_title,
                        'exercise_gender' => $exercise_detail->exercise_gender,
                        'exercise_goal' => $exercise_detail->exercise_goal,
                        'exercise_workoutarea' => $exercise_detail->exercise_workoutarea,
                        'exercise_minage' => $exercise_detail->exercise_minage,
                        'exercise_maxage' => $exercise_detail->exercise_maxage,
                        'exercise_calories' => $exercise_detail->exercise_calories,
                        'exercise_injury' => $exercise_detail->exercise_injury,
                        'week_day' => $exercise_detail->week_day,
                        'exercise_image' => $exercise_detail->exercise_image,
                        'exercise_tips' => $exercise_detail->exercise_tips,
                        'exercise_instructions' => $exercise_detail->exercise_instructions,
                        'exercise_reps' => $exercise_detail->exercise_reps,
                        'exercise_sets' => $exercise_detail->exercise_sets,
                        'exercise_rest' => $exercise_detail->exercise_rest,
                        'exercise_equipment' => $exercise_detail->exercise_equipment,
                        'exercise_level' => $exercise_detail->exercise_level,
                        'exercise_image_link' => $exercise_detail->exercise_image_link,
                        'exercise_video' => $exercise_detail->exercise_video,
                        'video' => $exercise_detail->video
                    ];
                }
            }
    
            $response[$day] = [
                'title' => $title,
                'image' => $image,
                'total_coins' => $total_coins,
                'exercises' => $exercise_json,
            ];
        }
        
        return response()->json($response);
    }


   public function send_sunday_notification_us(Request $request){
       
       {
    $upcomingSunday = Carbon::now()->next(Carbon::SUNDAY);
    $message = 'Get Ready to Move and Win!🏅' . "\n" . 'Our exciting Fitness Event is almost here! Tap now and get ready to win some awesome rewards.🎁';
    $title = '📢 Exciting News!';
    
    $event_data = DB::table('fitme_event')
        ->join('users', 'fitme_event.user_id', '=', 'users.id')
        // ->where('users.id', 10805)
        ->where('users.country', 'United States')
        // ->where('fitme_event.event_start_date_current', '>=', $upcomingSunday)
        ->select('users.device_token', 'fitme_event.*')
        ->get();
        
        // dd($event_data);
       
    $notificationData = [
        'message' => $message,
        'notification_id' => 'FitMe',
        'booking_id' => 'FitMe',
        'title' => $title,
    ];

    $responses = [];
    
    foreach ($event_data as $data) {
     
        if (!empty($data->device_token)) {
          
            if ($data->event_start_date_upcoming==NULL && $data->current_day_status == 1 ) {
                 DB::table('fitme_event')
                 ->join('users', 'fitme_event.user_id', '=', 'users.id')
                  ->where('users.country', 'United States')
                    ->where('fitme_event.user_id',$data->user_id)
                    ->update([
                        'current_day_status' => 0,
            
                    ]);
                // $response = $this->sendFirebasePush([$data->device_token], $notificationData);
                // $responses[$data->id] = $response;
            } if($data->upcoming_day_status == 1){
               
                DB::table('fitme_event')
                   ->join('users', 'fitme_event.user_id', '=', 'users.id')
                    ->where('users.country', 'United States')
                    ->where('fitme_event.user_id',$data->user_id)
                    ->update([
                        'event_start_date_current' => $data->event_start_date_upcoming,
                        'event_start_date_upcoming' => null,
                        'current_day_status' => 1,
                        'upcoming_day_status' => 0,
                    ]);

                // DB::table('users')
                //     ->where('id', $data->user_id)
                //     ->update([
                //         'fit_coins' => 0,
                //     ]);
                
                // Send the notification after the update
                // $response = $this->sendFirebasePush([$data->device_token], $notificationData);
                // $responses[$data->id] = $response;
                
            }

            DB::table('users')
                ->where('id', $data->user_id)
                ->where('country', 'United States')
                ->update(['fit_coins' => 0]);

            // DB::table('event_exercise_completion_status')->truncate();
            // DB::table('cardio_exercise_complete_status')->truncate();
            // DB::table('fitcoin_history')->truncate();
            // DB::table('breathin_session')->truncate();
        }
    }

    return response()->json(['message' => 'Notifications sent successfully!', 'responses' => $responses]);
}
       
   }


 public function testing_get_breathinout_session(Request $request) 
    {
        // Get current time in 'HH:mm' format in the 'Asia/Kolkata' timezone
        $user_id = $request->input('user_id');
        $check_country  = DB::table('users')->where('id',$user_id)->first();  //country
        $timezone = 'UTC'; // Default timezone, can be set to any default you prefer

        if ($check_country->country === 'India') {
            $timezone = 'Asia/Kolkata';
        } elseif ($check_country->country === 'United States') {
            $timezone = 'America/New_York';
        }
        $indiaTime = Carbon::now()->setTimezone($timezone);
            
        // $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        $currentHour = $indiaTime->format('g:i a');
        $currentHour24 = $indiaTime->format('H:i');
        $current_day = $indiaTime->format('l');
    
        // Define the sessions with their start and end times in 12-hour format
        $sessions = [
            ['start' => '6:00AM', 'end' => '7:00AM', 'message' => 'Breath in session start', 'title' => 'A'],
            ['start' => '11:00AM', 'end' => '12:00PM', 'message' => 'Breath in session start', 'title' => 'B'],
            ['start' => '4:00PM', 'end' => '5:00PM', 'message' => 'Breath in session start', 'title' => 'C'],
            ['start' => '9:00PM', 'end' => '10:00PM', 'message' => 'Breath in session start', 'title' => 'D'],
          
        ];
    
        // Function to convert 12-hour time to 24-hour time for comparison
        function convertTo24Hour($time)
        {
            return Carbon::createFromFormat('g:i a', $time)->format('H:i');
        }
    
        $sessionsWithStatus = [];
    
        // Determine the status of each session
        foreach ($sessions as $session) {
            $sessionStart = convertTo24Hour($session['start']);
            $sessionEnd = convertTo24Hour($session['end']);
            
            $lowercase_title = strtolower($session['title']);
            
            // Fixing the query and retrieving the status
            $check_complete_Status = DB::table('breathin_session')
            ->where('user_id', $user_id)
            ->where('day', $current_day)
            ->where('session_'.$lowercase_title, 'done')
            ->first();
            
            
           

        $com_status = $check_complete_Status ? true : false;
    
            if ($currentHour24 >= $sessionStart && $currentHour24 < $sessionEnd) {
                $status = 'open';
                $statusMessage = 'Session is open';
            } elseif ($currentHour24 < $sessionStart) {
                $status = 'upcoming';
                $statusMessage = 'Session is upcoming';
            } else {
                $status = 'closed';
                $statusMessage = 'Session is closed';
            }
            
            $sessionsWithStatus[] = [
                'start_time' => $session['start'],
                'end_time' => $session['end'],
                'title' => $session['title'],
                'status' => $status,
                'status_message' => $statusMessage,
                'fit_coins' => 1,
                'complete_status' => $com_status 
            ];
        }
    
        return response()->json([
            'sessions' => $sessionsWithStatus
        ]);
    }
    public function testa_coin_deduction_rec(Request $request){
        $user_id = $request->user_id;
        $day = $request->day;
        $day_Data = $day;
        $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        $currentDay = $indiaTime->format('l');
        $get_coins=1;
    
    
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            if (!in_array($day, $weekdays)) {
                return response()->json([
                    'msg' => 'please enter valid day'
                ]);
            }

            if(!$user_id){
                  return response()->json([
                        'msg' => 'user id is required'
                    ]);
            }
          $check_data = DB::table('event_exercise_completion_status')
            ->where('user_id', $user_id)
            ->first();
        
            
            
    
        
                 $check_monday_data = DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->where('user_day',$day)
                    ->first();
                
                    $monday=[];
          
           
                $point_deduction =[];
                $skip_status_monday = DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('skip_status')
                    ->sum('skip_status');
                    
                $point_deduction['skip_status']= $skip_status_monday;
        
                $prev_status_monday = DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('prev_status')
                    ->sum('prev_status');
                    
                    $point_deduction['prev_status']= $prev_status_monday;
        
                $next_status_monday = DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('next_status')
                    ->sum('next_status');
                    
                     $point_deduction['next_status']= $next_status_monday;
                     $late = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','late')->count();
                     $point_deduction['delay']= $late;
                    
                    
                    // workout section --------------------------------------------------------
                    // cardio 
                     $cardio =[];
                    
                    
                  $cardio_status_monday = DB::table('cardio_exercise_complete_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $day)
                    ->where('today_earning', '>', 0)
                    // ->where('cardio_status','done')
                     ->first();
                    
                     
                     $total_cardio_points =0;
                    if($cardio_status_monday){
                        $cardio_coins =$cardio_status_monday->fit_coins; 

                      $skip_cardio_status_monday = DB::table('cardio_exercise_complete_status')
                        ->where('user_id', $user_id)
                        ->where('user_day', $day)
                        // ->where('final_status', 'allcompleted')
                        ->where('exercise_status', 'completed')
                        ->whereNotNull('skip_status')
                        ->sum('skip_status');
                        
                        // $cardio['skip_status']= $skip_cardio_status_monday;
                        
                        // dd($skip_status_monday);
                 
            
                    $prev_cardio_status_monday = DB::table('cardio_exercise_complete_status')
                        ->where('user_id', $user_id)
                        ->where('user_day', $day)
                        // ->where('final_status', 'allcompleted')
                        ->where('exercise_status', 'completed')
                        ->whereNotNull('prev_status')
                        ->sum('prev_status');
                        
                        // $cardio['prev_status']= $prev_cardio_status_monday;
            
                    $next_cardio_status_monday = DB::table('cardio_exercise_complete_status')
                        ->where('user_id', $user_id)
                        ->where('user_day', $day)
                        // ->where('final_status', 'allcompleted')
                        // ->where('exercise_status', 'completed')
                        ->whereNotNull('next_status')
                        ->sum('next_status');
                        //  $cardio['next_status']= $next_cardio_status_monday;
                         $total_cardio_deduction = $next_cardio_status_monday+$prev_cardio_status_monday+$skip_cardio_status_monday;
                        //  $total_cardio_points =$cardio_coins-$total_cardio_deduction;
                        //  $monday['cardio']= $total_cardio_points;
                        
                        $total_cardio_points = $cardio_status_monday->today_earning;
                    }
                     $cardio['cardio']= $total_cardio_points;
  
                $countSessionA = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_a', 'done')->count();
                $countSessionB = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_b', 'done')->count();
                $countSessionC = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_c', 'done')->count();
                $countSessionD = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_d', 'done')->count();
                
                $total_breath_coins= ($countSessionA+$countSessionB+$countSessionC+$countSessionD)*$get_coins;
                $cardio['breath_in']=0;
                if($countSessionA || $countSessionB || $countSessionC || $countSessionD){
                      $total_coins= ($countSessionA+$countSessionB+$countSessionC+$countSessionD)*$get_coins;
                     $cardio['breath_in']=$total_breath_coins;
                }
                    
                    
                // event overview  --------------------------------------------------------
                
                $event_overview =[];
                
                $eventoverview = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->where('exercise_status','completed')->get();
                if($eventoverview->isNotEmpty()){
                    
                      $total_exercises = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->where('exercise_status','completed')->count();
                      $event_overview['exercises'] =$total_exercises ;
                    //   dd($eventoverview[0]->created_at);
                       
                      $dateTime = Carbon::parse($eventoverview[0]->created_at)->setTimezone('Asia/Kolkata');
        
                        $Weekday = $dateTime->format('l');
                        
                        // Format the date and time
                        $formattedDateTime = $dateTime->format('M-d-y');
                        $formattedTime = $dateTime->format('h:iA');
                        
                        //   $event_overview['day'] =$Weekday ;
                          $event_overview['day'] =$day;
                          $event_overview['date'] =$formattedDateTime;
                          $event_overview['time'] =$formattedTime;
                          
                          $missed_exercises = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->where('exercise_status','undone')->count();
                          $event_overview['missed'] =$missed_exercises;
                          
                }
        
           
                 // refer rewards --------------------------------------------------------
                 $refer_rewards =[];
                 $register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('current_day',$day)->where('info','referral_registerd')->count();
                 $event_register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('current_day',$day)->where('info','event_registerd')->count();
                 
                 $reward_data['register'] =$register_data;
                 $reward_data['event_register'] =$event_register_data;
                 $total_register_points = $register_data*2;
                 $total_event_register_points = $event_register_data*3;
                 $reward_data['total_rewards_points'] =$event_register_data+$event_register_data;
             
                // week_day points --------------------------------------------------------
                $currentDay = $indiaTime->format('l');
                $week_day_data = [];
                $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                
                // Find the index of the current day in the weekdays array
                $currentDayIndex = array_search($currentDay, $weekdays);
                
                //  foreach ($weekdays as $index => $day) {
                     
                //      echo $day;
                     
                //  }
                //  die();
                 
                            
                          foreach ($weekdays as $index => $day) {
                                    // echo $day;
                                if ($index <= $currentDayIndex) {
                                 $cardio_status_monday = DB::table('cardio_exercise_complete_status')
                                    ->where('user_id', $user_id)
                                    ->where('user_day', $day)
                                    ->where('today_earning', '>', 0)
                                    // ->where('cardio_status','done')
                                     ->first();
                                    
                                    $countSessionA = DB::table('breathin_session')->where('user_id', $user_id)->where('day',$day)->where('session_a', 'done')->count();
                                    $countSessionB = DB::table('breathin_session')->where('user_id', $user_id)->where('day',$day )->where('session_b', 'done')->count();
                                    $countSessionC = DB::table('breathin_session')->where('user_id', $user_id)->where('day',$day)->where('session_c', 'done')->count();
                                    $countSessionD = DB::table('breathin_session')->where('user_id', $user_id)->where('day',$day)->where('session_d', 'done')->count();
                                    $breath_coins = ($countSessionA + $countSessionB + $countSessionC + $countSessionD);
                            
                                    $points = DB::table('event_exercise_completion_status')
                                        ->where('user_id', $user_id)
                                        ->where('user_day', $day)
                                        ->first();
                                        
                                        
                                    if($cardio_status_monday){
                                        $week_day_data[$day] = $cardio_status_monday->today_earning;
                                    }else{
                                        $week_day_data[$day] = 0;
                                    }
                                    

                                    $week_day_data[$day] = $breath_coins+$week_day_data[$day];
                                    
                                    
                                   
                                    if ($points  && $points->today_earning > 0) {
                                        
                                      
                                        
                                        $week_day_data[$day] = $points->today_earning + $week_day_data[$day];
                                        // dd($week_day_data[$day]);
                                    } else {
                                        // $week_day_data[$day] = 0;
                                        // $week_day_data[$day] = $breath_coins;
                                    }
                                } else {
                                    $week_day_data[$day] = null;
                                }
                            }
                        
                foreach ($weekdays as $index => $day) {
                    if ($index <= $currentDayIndex) {
                        // Fetch cardio status
                        $cardio_status_monday = DB::table('cardio_exercise_complete_status')
                            ->where('user_id', $user_id)
                            ->where('user_day', $day)
                            ->where('today_earning', '>', 0)
                            ->first();
                
                        // Count breathing session completions
                        $countSessionA = DB::table('breathin_session')
                            ->where('user_id', $user_id)
                            ->where('day', $day)
                            ->where('session_a', 'done')
                            ->count();
                        $countSessionB = DB::table('breathin_session')
                            ->where('user_id', $user_id)
                            ->where('day', $day)
                            ->where('session_b', 'done')
                            ->count();
                        $countSessionC = DB::table('breathin_session')
                            ->where('user_id', $user_id)
                            ->where('day', $day)
                            ->where('session_c', 'done')
                            ->count();
                        $countSessionD = DB::table('breathin_session')
                            ->where('user_id', $user_id)
                            ->where('day', $day)
                            ->where('session_d', 'done')
                            ->count();
                
                        // Calculate total breath coins for the day
                        // $breath_coins = $countSessionA + $countSessionB + $countSessionC + $countSessionD;
                
                        // Initialize the week day data with cardio today earning or 0
                        // if ($cardio_status_monday) {
                        //     $week_day_data[$day] = $cardio_status_monday->today_earning;  // Use the 'today_earning' field from the object
                            
                        // } else {
                        //     $week_day_data[$day] = 0;
                        // }
                
                        // Add breath coins to the week's data
                        // $week_day_data[$day] += $breath_coins;
                        
                
                        // Fetch points for the day and add to the total
                        $points = DB::table('event_exercise_completion_status')
                            ->where('user_id', $user_id)
                            ->where('user_day', $day)
                            ->first();
                
                        // if ($points) {
                        //     $week_day_data[$day] += $points->today_earning;  // Add 'today_earning' from the points object
                        // } else {
                        //     // Reset to only breath coins if no points are found
                        //     $week_day_data[$day] = $week_day_data[$day];
                        // }
                    } else {
                        // Set to null for days after the current day
                        $week_day_data[$day] = null;
                    }
                   
                }
                            

                $single_exercise =[];
                
                $exercise_count =DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','single_exercise')->count();
                $exercises = DB::table('fitcoin_history')
                  ->where('user_id',$user_id)
                  ->where('info','single_exercise')
                  ->get();
                  
                  $exercises = [];
                    
                 $exerciseData=[];
                    foreach ($exercises as $exercise_id) {
                
                        $exerciseData = DB::table('exercises')
                                          ->where('exercise_id', $exercise_id)
                                          ->first();
                        if ($exerciseData) {
                           
                            $exercises[] = $exerciseData;
                        }
                        
                    }

                
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // $monday_points = DB::table('event_exercise_completion_status')->where('user_id',$user_id)->where('user_day',$day)->count();
                // dd($monday_points);
                // $week_day_data['Monday'] =$monday_points;
                // $week_day_data['Tuesday'] =$monday_points;
                // $week_day_data['Wednesday'] =$monday_points;
                // $week_day_data['Thruesday'] =$thursday_points;
                // $week_day_data['Friday'] =$monday_friday;
                 
                //  $total_coins_to_add = 
                
                // dd($week_day_data[$day_Data] );

         $register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','referral_registerd')->count();
         $event_register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','event_registerd')->count();
        
         $day_missed = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','day_missed')->count();
         $custom_wokrout_coins = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','create_custom_wokrout')->count();
        
         $register_object = (object) $register_data;
         $response = [
            // $day => $monday,
            'points'=>$week_day_data,
            'point_deduction'=>$point_deduction,
            'workout'=>$cardio,
            'event_overview'=>$event_overview,
            'refer_rewards'=>$reward_data,
            'exercises' =>$exerciseData,
            // 'tuesday' => $tuesday,
            // 'wednesday' => $wednesday,
            // 'thursday' => $thursday,
            // 'friday' => $friday,
            // 'total_registerd' => $register_data,
            // 'total_event_registerd'=>$event_register_data,
            // 'late_exercises'=>$late,
            // 'custom_wokrout_coins' =>$custom_wokrout_coins,
            // 'missing_days_count'=>$missingDaysCount,
            // 'missing_days'=>$missedDays,
        ];

        return response()->json($response);
    // }

    // return response()->json([
    //     'msg' => 'no data found'
    // ]);
 }
    public function testa_all_user_with_condition(Request $request){
        $version = $request->input('version');
       
        // Validate version
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
    
        if (!$versions_current && !$versions_middle && !$versions_past) {
            return response()->json(['msg' => 'Please update the app to the latest version.']);
        }
           $user_id = $request->input('user_id');

            if (empty($user_id)) {
                    return response()->json([
                      'msg' => 'user id is required'
                      ]);
             }
                $user_gender = DB::table('users')->where('id',$user_id)->first();
                
                if($user_gender){
                    $user_gender = $user_gender->gender;
                }
                $daysdata = DB::table('challenges')->where('gender',$user_gender)->get();
      
                      $data=[];
                      foreach ($daysdata as $day) {  
                        $josn_user_id = $day->user_id;
                        
                        
                        $simple_arr = json_decode($josn_user_id, true);
                        if (empty($simple_arr)) {
                            $status = "not active";
                        } else {
                            if (is_array($simple_arr) && ($key = array_search($user_id, $simple_arr)) !== false) {
                                $status = "active";
                            } else {
                                $status = "not active"; // If $simple_arr is not an array or $user_id is not found in the array
                            }
                        }
                            //   dd($day->id);           
                        $title = $day->title;
                        $sub_title = $day->sub_title;
                        $id = $day->id;
                        $image = $day->image;
                        $days = $day->days;
                        $gender = $day->gender;
                        
                        $numberOfWeeks = $days/7;
                        
                        
                            $day1 = $day->day_1;
                            $day_1decodedData = json_decode($day1, true);
                            $day1exercises =[];
                            $day1_seconds =0;
                            $day1_total_calories =0;
                           foreach ($day_1decodedData as $day1) {
                               
                            $exercisedata = DB::table('exercises')->where('exercise_id', $day1)->first();
                            
                            if ($exercisedata) {
                                $string_time = $exercisedata->exercise_rest;
                                $parts = sscanf($string_time, "%d %s");
                                if (count($parts) == 2) { 
                                    list($number, $unit) = $parts;
                                
                                    // Convert time to seconds based on the unit
                                    switch (strtolower($unit)) {
                                        case 'sec':
                                        case 'secs':
                                        case 'second':
                                        case 'seconds':
                                            $day1_seconds += $number;
                                            break;
                                        case 'min':
                                        case 'mins':
                                        case 'minute':
                                        case 'minutes':
                                            $day1_seconds += $number * 60;
                                            break;
                                        default:
                                            echo "Unexpected time unit: $unit";
                                    }
                                }
                                $day1_seconds;
                              
                                $exercise_calories = $exercisedata->exercise_calories;
                                $day1_total_calories += $exercise_calories;
                        
                                $exerciseData = [
                                    'exercise_id' => $exercisedata->exercise_id,
                                    'exercise_title' => $exercisedata->exercise_title,
                                    'exercise_gender' => $exercisedata->exercise_gender,
                                    'exercise_goal' => $exercisedata->exercise_goal,
                                    'exercise_workoutarea' => $exercisedata->exercise_workoutarea,
                                    'exercise_minage' => $exercisedata->exercise_minage,
                                    'exercise_maxage' => $exercisedata->exercise_maxage,
                                    'exercise_calories' => $exercisedata->exercise_calories,
                                    'exercise_injury' => $exercisedata->exercise_injury,
                                    'exercise_reps' => $exercisedata->exercise_reps,
                                    'exercise_sets' => $exercisedata->exercise_sets,
                                    'exercise_rest' => $exercisedata->exercise_rest,
                                    'exercise_equipment' => $exercisedata->exercise_equipment,
                                    'week_day' => $exercisedata->week_day,
                                    'exercise_level' => $exercisedata->exercise_level,
                                    'exercise_image' => $exercisedata->exercise_image_link,
                                    'exercise_video' => $exercisedata->exercise_video,
                                    'exercise_instructions' => $exercisedata->exercise_instructions,
                                    'video' => $this->base_url . "/images/" . $exercisedata->video,
                                ];
                        
                                // Add individual exercise data to the array
                                $day1exercises[] = $exerciseData;
                            }
                        }
                          
                            $day2 = $day->day_2;
                            $day2_total_calories =0;
                            $day_2decodedData = json_decode($day2, true);
                            $day2exercises =[];
                            $day2_seconds=0;
                            foreach($day_2decodedData as $day2){
                                  $exercisedata = DB::table('exercises')->where('exercise_id',$day2)->first();
                                  $exercise_calories = $exercisedata->exercise_calories;
                                  $day2_total_calories += $exercise_calories;
                                if ($exercisedata) {
                                     $string_time = $exercisedata->exercise_rest;
                                    $parts = sscanf($string_time, "%d %s");
                                    if (count($parts) == 2) { 
                                        list($number, $unit) = $parts;
                                    
                                        // Convert time to seconds based on the unit
                                        switch (strtolower($unit)) {
                                            case 'sec':
                                            case 'secs':
                                            case 'second':
                                            case 'seconds':
                                                $day2_seconds += $number;
                                                break;
                                            case 'min':
                                            case 'mins':
                                            case 'minute':
                                            case 'minutes':
                                                $day2_seconds += $number * 60;
                                                break;
                                            default:
                                                echo "Unexpected time unit: $unit";
                                        }
                                    }
                                $day2_seconds;
                                    $exerciseData = [
                                        'exercise_id' => $exercisedata->exercise_id,
                                        'exercise_title' => $exercisedata->exercise_title,
                                        'exercise_gender' => $exercisedata->exercise_gender,
                                        'exercise_goal' => $exercisedata->exercise_goal,
                                        'exercise_workoutarea' => $exercisedata->exercise_workoutarea,
                                        'exercise_minage' => $exercisedata->exercise_minage,
                                        'exercise_maxage' => $exercisedata->exercise_maxage,
                                        'exercise_calories' => $exercisedata->exercise_calories,
                                        'exercise_injury' => $exercisedata->exercise_injury,
                                        'exercise_reps' => $exercisedata->exercise_reps,
                                        'exercise_sets' => $exercisedata->exercise_sets,
                                        'exercise_rest' => $exercisedata->exercise_rest,
                                        'exercise_equipment' => $exercisedata->exercise_equipment,
                                        'week_day' => $exercisedata->week_day,
                                        'exercise_level' => $exercisedata->exercise_level,
                                        'exercise_image' => $exercisedata->exercise_image_link,
                                        'exercise_video' => $exercisedata->exercise_video,
                                        'exercise_instructions' => $exercisedata->exercise_instructions,
                                        'video' => $this->base_url . "/images/" . $exercisedata->video,
                                    ];
                                    $day2exercises[] = $exerciseData;
                             }
                            } 
                            $day3_total_calories =0;
                            $day3 = $day->day_3;
                            $day_3decodedData = json_decode($day3, true);
                            $day3exercises =[];
                            $day3_seconds =0;
                            foreach($day_3decodedData as $day3){
                                 $exercisedata = DB::table('exercises')->where('exercise_id',$day3)->first();
                                  $exercise_calories = $exercisedata->exercise_calories;
                                  $day3_total_calories += $exercise_calories;
                                if ($exercisedata) {
                                     $string_time = $exercisedata->exercise_rest;
                                    $parts = sscanf($string_time, "%d %s");
                                    if (count($parts) == 2) { 
                                        list($number, $unit) = $parts;
                                    
                                        // Convert time to seconds based on the unit
                                        switch (strtolower($unit)) {
                                            case 'sec':
                                            case 'secs':
                                            case 'second':
                                            case 'seconds':
                                                $day3_seconds += $number;
                                                break;
                                            case 'min':
                                            case 'mins':
                                            case 'minute':
                                            case 'minutes':
                                                $day3_seconds += $number * 60;
                                                break;
                                            default:
                                                echo "Unexpected time unit: $unit";
                                        }
                                    }
                                $day3_seconds;
                                    $exerciseData = [
                                        'exercise_id' => $exercisedata->exercise_id,
                                        'exercise_title' => $exercisedata->exercise_title,
                                        'exercise_gender' => $exercisedata->exercise_gender,
                                        'exercise_goal' => $exercisedata->exercise_goal,
                                        'exercise_workoutarea' => $exercisedata->exercise_workoutarea,
                                        'exercise_minage' => $exercisedata->exercise_minage,
                                        'exercise_maxage' => $exercisedata->exercise_maxage,
                                        'exercise_calories' => $exercisedata->exercise_calories,
                                        'exercise_injury' => $exercisedata->exercise_injury,
                                        'exercise_reps' => $exercisedata->exercise_reps,
                                        'exercise_sets' => $exercisedata->exercise_sets,
                                        'exercise_rest' => $exercisedata->exercise_rest,
                                        'exercise_equipment' => $exercisedata->exercise_equipment,
                                        'week_day' => $exercisedata->week_day,
                                        'exercise_level' => $exercisedata->exercise_level,
                                        'exercise_image' => $exercisedata->exercise_image_link,
                                        'exercise_video' => $exercisedata->exercise_video,
                                        'exercise_instructions' => $exercisedata->exercise_instructions,
                                        'video' => $this->base_url . "/images/" . $exercisedata->video,
                                    ];
                                    $day3exercises[] = $exerciseData;
                             }
                            } 
                            $day4 = $day->day_4;
                            $day4_total_calories=0;
                            $day_4decodedData = json_decode($day4, true);
                            $day4exercises =[];
                            $day4_seconds=0;
                            foreach($day_4decodedData as $day4){
                                 $exercisedata = DB::table('exercises')->where('exercise_id',$day4)->first();
                                 $exercise_calories = $exercisedata->exercise_calories;
                                 $day4_total_calories += $exercise_calories;
                                if ($exercisedata) {
                                     $string_time = $exercisedata->exercise_rest;
                                    $parts = sscanf($string_time, "%d %s");
                                    if (count($parts) == 2) { 
                                        list($number, $unit) = $parts;
                                    
                                        // Convert time to seconds based on the unit
                                        switch (strtolower($unit)) {
                                            case 'sec':
                                            case 'secs':
                                            case 'second':
                                            case 'seconds':
                                                $day4_seconds += $number;
                                                break;
                                            case 'min':
                                            case 'mins':
                                            case 'minute':
                                            case 'minutes':
                                                $day4_seconds += $number * 60;
                                                break;
                                            default:
                                                echo "Unexpected time unit: $unit";
                                        }
                                    }
                                $day4_seconds;
                                    $exerciseData = [
                                        'exercise_id' => $exercisedata->exercise_id,
                                        'exercise_title' => $exercisedata->exercise_title,
                                        'exercise_gender' => $exercisedata->exercise_gender,
                                        'exercise_goal' => $exercisedata->exercise_goal,
                                        'exercise_workoutarea' => $exercisedata->exercise_workoutarea,
                                        'exercise_minage' => $exercisedata->exercise_minage,
                                        'exercise_maxage' => $exercisedata->exercise_maxage,
                                        'exercise_calories' => $exercisedata->exercise_calories,
                                        'exercise_injury' => $exercisedata->exercise_injury,
                                        'exercise_reps' => $exercisedata->exercise_reps,
                                        'exercise_sets' => $exercisedata->exercise_sets,
                                        'exercise_rest' => $exercisedata->exercise_rest,
                                        'exercise_equipment' => $exercisedata->exercise_equipment,
                                        'week_day' => $exercisedata->week_day,
                                        'exercise_level' => $exercisedata->exercise_level,
                                        'exercise_image' => $exercisedata->exercise_image_link,
                                        'exercise_video' => $exercisedata->exercise_video,
                                        'exercise_instructions' => $exercisedata->exercise_instructions,
                                        'video' => $this->base_url . "/images/" . $exercisedata->video,
                                    ];
                                    $day4exercises[] = $exerciseData;
                             }
                            } 
                            $day5 = $day->day_5;
                            $day5_total_calories =0;
                            $day_5decodedData = json_decode($day5, true);
                            $day5exercises =[];
                            $day5_seconds=0;
                            foreach($day_5decodedData as $day5){
                                 $exercisedata = DB::table('exercises')->where('exercise_id',$day5)->first();
                                  $exercise_calories = $exercisedata->exercise_calories;
                                  $day5_total_calories += $exercise_calories;
                                if ($exercisedata) {
                                     $string_time = $exercisedata->exercise_rest;
                                    $parts = sscanf($string_time, "%d %s");
                                    if (count($parts) == 2) { 
                                        list($number, $unit) = $parts;
                                    
                                        // Convert time to seconds based on the unit
                                        switch (strtolower($unit)) {
                                            case 'sec':
                                            case 'secs':
                                            case 'second':
                                            case 'seconds':
                                                $day5_seconds += $number;
                                                break;
                                            case 'min':
                                            case 'mins':
                                            case 'minute':
                                            case 'minutes':
                                                $day5_seconds += $number * 60;
                                                break;
                                            default:
                                                echo "Unexpected time unit: $unit";
                                        }
                                    }
                                $day5_seconds;
                                    $exerciseData = [
                                        'exercise_id' => $exercisedata->exercise_id,
                                        'exercise_title' => $exercisedata->exercise_title,
                                        'exercise_gender' => $exercisedata->exercise_gender,
                                        'exercise_goal' => $exercisedata->exercise_goal,
                                        'exercise_workoutarea' => $exercisedata->exercise_workoutarea,
                                        'exercise_minage' => $exercisedata->exercise_minage,
                                        'exercise_maxage' => $exercisedata->exercise_maxage,
                                        'exercise_calories' => $exercisedata->exercise_calories,
                                        'exercise_injury' => $exercisedata->exercise_injury,
                                        'exercise_reps' => $exercisedata->exercise_reps,
                                        'exercise_sets' => $exercisedata->exercise_sets,
                                        'exercise_rest' => $exercisedata->exercise_rest,
                                        'exercise_equipment' => $exercisedata->exercise_equipment,
                                        'week_day' => $exercisedata->week_day,
                                        'exercise_level' => $exercisedata->exercise_level,
                                        'exercise_image' => $exercisedata->exercise_image_link,
                                        'exercise_video' => $exercisedata->exercise_video,
                                        'exercise_instructions' => $exercisedata->exercise_instructions,
                                        'video' => $this->base_url . "/images/" . $exercisedata->video,
                                    ];
                                    $day5exercises[] = $exerciseData;
                             }
                            } 
                            $day6 = $day->day_6;
                            $day6_total_calories=0;
                            $day_6decodedData = json_decode($day6, true);
                            $day6exercises =[];
                            $day6_seconds=0;
                            foreach($day_6decodedData as $day6){
                                 $exercisedata = DB::table('exercises')->where('exercise_id',$day6)->first();
                                  $exercise_calories = $exercisedata->exercise_calories;
                                  $day6_total_calories += $exercise_calories;
                                if ($exercisedata) {
                                     $string_time = $exercisedata->exercise_rest;
                                    $parts = sscanf($string_time, "%d %s");
                                    if (count($parts) == 2) { 
                                        list($number, $unit) = $parts;
                                    
                                        // Convert time to seconds based on the unit
                                        switch (strtolower($unit)) {
                                            case 'sec':
                                            case 'secs':
                                            case 'second':
                                            case 'seconds':
                                                $day6_seconds += $number;
                                                break;
                                            case 'min':
                                            case 'mins':
                                            case 'minute':
                                            case 'minutes':
                                                $day6_seconds += $number * 60;
                                                break;
                                            default:
                                                echo "Unexpected time unit: $unit";
                                        }
                                    }
                                $day6_seconds;
                                    $exerciseData = [
                                        'exercise_id' => $exercisedata->exercise_id,
                                        'exercise_title' => $exercisedata->exercise_title,
                                        'exercise_gender' => $exercisedata->exercise_gender,
                                        'exercise_goal' => $exercisedata->exercise_goal,
                                        'exercise_workoutarea' => $exercisedata->exercise_workoutarea,
                                        'exercise_minage' => $exercisedata->exercise_minage,
                                        'exercise_maxage' => $exercisedata->exercise_maxage,
                                        'exercise_calories' => $exercisedata->exercise_calories,
                                        'exercise_injury' => $exercisedata->exercise_injury,
                                        'exercise_reps' => $exercisedata->exercise_reps,
                                        'exercise_sets' => $exercisedata->exercise_sets,
                                        'exercise_rest' => $exercisedata->exercise_rest,
                                        'exercise_equipment' => $exercisedata->exercise_equipment,
                                        'week_day' => $exercisedata->week_day,
                                        'exercise_level' => $exercisedata->exercise_level,
                                        'exercise_image' => $exercisedata->exercise_image_link,
                                        'exercise_video' => $exercisedata->exercise_video,
                                        'exercise_instructions' => $exercisedata->exercise_instructions,
                                        'video' =>$this->base_url . "/images/" . $exercisedata->video,
                                    ];
                                    $day6exercises[] = $exerciseData;
                             }
                            } 
                            $day7 = $day->day_7;
                            $day_7decodedData = json_decode($day7, true);
                            $day7_total_calories =0;
                            $day7exercises =[];
                            $day7_seconds=0;
                            foreach($day_7decodedData as $day7){
                                $exercisedata = DB::table('exercises')->where('exercise_id',$day7)->first();
                                $exercise_calories = $exercisedata->exercise_calories;
                                $day7_total_calories += $exercise_calories;
                                if ($exercisedata) {
                                    $string_time = $exercisedata->exercise_rest;
                                    $parts = sscanf($string_time, "%d %s");
                                    if (count($parts) == 2) { 
                                        list($number, $unit) = $parts;
                                    
                                        // Convert time to seconds based on the unit
                                        switch (strtolower($unit)) {
                                            case 'sec':
                                            case 'secs':
                                            case 'second':
                                            case 'seconds':
                                                $day7_seconds += $number;
                                                break;
                                            case 'min':
                                            case 'mins':
                                            case 'minute':
                                            case 'minutes':
                                                $day7_seconds += $number * 60;
                                                break;
                                            default:
                                                echo "Unexpected time unit: $unit";
                                        }
                                    }
                                    $day7_seconds;
                                    $exerciseData = [
                                        'exercise_id' => $exercisedata->exercise_id,
                                        'exercise_title' => $exercisedata->exercise_title,
                                        'exercise_gender' => $exercisedata->exercise_gender,
                                        'exercise_goal' => $exercisedata->exercise_goal,
                                        'exercise_workoutarea' => $exercisedata->exercise_workoutarea,
                                        'exercise_minage' => $exercisedata->exercise_minage,
                                        'exercise_maxage' => $exercisedata->exercise_maxage,
                                        'exercise_calories' => $exercisedata->exercise_calories,
                                        'exercise_injury' => $exercisedata->exercise_injury,
                                        'exercise_reps' => $exercisedata->exercise_reps,
                                        'exercise_sets' => $exercisedata->exercise_sets,
                                        'exercise_rest' => $exercisedata->exercise_rest,
                                        'exercise_equipment' => $exercisedata->exercise_equipment,
                                        'week_day' => $exercisedata->week_day,
                                        'exercise_level' => $exercisedata->exercise_level,
                                        'exercise_image' => $exercisedata->exercise_image_link,
                                        'exercise_video' => $exercisedata->exercise_video,
                                        'exercise_instructions' => $exercisedata->exercise_instructions,
                                        'video' => $this->base_url . "/images/" . $exercisedata->video,
                                    ];
                                    $day7exercises[] = $exerciseData;
                             }
                            } 
                        $day1data=['exercises'=>$day1exercises,'total_calories'=>$day1_total_calories,'total_rest' => $day1_seconds];
                        $day2data=['exercises'=>$day2exercises,'total_calories'=>$day2_total_calories,'total_rest' => $day2_seconds];
                        $day3data=['exercises'=>$day3exercises,'total_calories'=>$day3_total_calories,'total_rest' => $day3_seconds];
                        $day4data=['exercises'=>$day4exercises,'total_calories'=>$day4_total_calories,'total_rest' => $day4_seconds];
                        $day5data=['exercises'=>$day5exercises,'total_calories'=>$day5_total_calories,'total_rest' => $day5_seconds];
                        $day6data=['exercises'=>$day6exercises,'total_calories'=>$day6_total_calories,'total_rest' => $day6_seconds];
                        $day7data=['exercises'=>$day7exercises,'total_calories'=>$day7_total_calories,'total_rest' => $day7_seconds];
                            
                            $cc = 1;
                            $dddd = [];
                        for($i=0; $i<$numberOfWeeks; $i++){
                            $mergedArray = [
                                'day_'.$cc++ => $day1data,
                                'day_'.$cc++ => $day2data,
                                'day_'.$cc++ => $day3data,
                                'day_'.$cc++ => $day4data,
                                'day_'.$cc++ => $day5data,
                                'day_'.$cc++ => $day6data,
                                'day_'.$cc++ => $day7data,
                            ];
                            
                            $dddd = array_merge($dddd, $mergedArray);
                            
                        }
                        
                            $json_data[] = [
                                    'workout_id' => $id,
                                    'title' => $title,
                                    'sub_title' =>$sub_title,
                                    'gender' => $gender,
                                    'status' => $status,
                                    'workout_image_link'=>$image,
                                    'workout_image'=>$image,
                                    'total_days' => $days,
                                    'days' => $dddd,
                            ];
                        }
                        
                      
            
        
        
         if($user_id){
                $user_data = DB::table('users')->where('id',$user_id)->first();
                $gender = $user_data->gender;
                $exercise_data = DB::table('exercises')->where('exercise_gender',$gender)
                ->join('exercises_bodyparts', 'exercises.exercise_id', '=', 'exercises_bodyparts.exercise_id')
                 ->join('bodyparts', 'exercises_bodyparts.bodypart_id', '=', 'bodyparts.bodypart_id')
                 ->join('equipments', 'exercises.exercise_equipment', '=', 'equipments.equipment_id')
                ->get();
            // print_r($exercise_data);
            // die();
                
            }else{
             $exercise_data = DB::table('exercises')
                ->join('exercises_bodyparts', 'exercises.exercise_id', '=', 'exercises_bodyparts.exercise_id')
                 ->join('bodyparts', 'exercises_bodyparts.bodypart_id', '=', 'bodyparts.bodypart_id')
                 ->join('equipments', 'exercises.exercise_equipment', '=', 'equipments.equipment_id')
                ->get();
                    // print_r($exercise_data);
                    // die();
                
            }
           
           $exercise_json = []; // Initialize the array outside of the loop

        foreach($exercise_data as $data) {
            // $bodypart_id = $data->exercise_equipment;
            // $bodypart_data = DB::table('bodyparts')->where('bodypart_id', $bodypart_id)->first();
        
            // // Check if bodypart_data is not null
            // $bodypart_title = $bodypart_data ? $bodypart_data->bodypart_title : 'Not Available';
        
            // Extract data from $data object
            $exercise_json[] = [
                'exercise_id' => $data->exercise_id,
                'exercise_title' => $data->exercise_title,
                'exercise_gender' => $data->exercise_gender,
                'exercise_goal' => $data->exercise_goal,
                'exercise_workoutarea' => $data->exercise_workoutarea,
                'exercise_minage' => $data->exercise_minage,
                'exercise_maxage' => $data->exercise_maxage,
                'exercise_calories' => $data->exercise_calories,
                'exercise_injury' => $data->exercise_injury,
                'week_day' => $data->week_day,
                'exercise_image' => $data->exercise_image,
                'exercise_tips' => $data->exercise_tips,
                'exercise_instructions' => $data->exercise_instructions,
                'exercise_reps' => $data->exercise_reps,
                'exercise_sets' => $data->exercise_sets,
                'exercise_rest' => $data->exercise_rest,
                'exercise_bodypart' => $data->bodypart_title, // Using safe value
                'exercise_equipment' => $data->equipment_title, // Using safe value
                'exercise_level' => $data->exercise_level,
                'exercise_image_link' => $data->exercise_image_link,
                'exercise_video' => $data->exercise_video,
                'video' => $data->video,
                'fit_coins' => $data->fit_coins
                
            ];
        }
        
        // Construct the final response
        $response = [
            'data' => $exercise_json,
            'challenge_data' => $json_data,
           
        ];
    
        return response()->json($response);
    }
    
   public function testa_add_referral_coin(Request $request)
   {
        
        $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        $currentDay = $indiaTime->format('l');
        // $currentDay = 'Saturday';
        if($currentDay == 'Saturday' || $currentDay == 'Sunday' ){
              return response()->json([
                'msg' => 'You are not able to use this referal code'
            ]);
        }
        $code = $request->referral_code;
        $user_id = $request->user_id;
    
        $check_current_Status = DB::table('referral_code')->where('referral_code',$code)->whereNotNull('user_id')->first();
        $check_current_Status->user_id;
        $message = 'Credited extra fitcoins to you as your friend registered using your code.';
        $title = 'Money Credited!';
   
        
        $plan_current_Status = DB::table('fitme_event')->where('user_id',$check_current_Status->user_id)->where('current_day_status',1)->first();
        // dd($plan_current_Status);
        if(!$plan_current_Status){
               return response()->json([
                'msg' => 'you are not in current plan please try later.'
            ]);
            
        }

        $get_user_data = DB::table('users')->where('id',$user_id)->first(); 
        if(!$get_user_data){
         return response()->json([
                    'msg' => 'user not exist'
            ]);
        }
        $device_id = $get_user_data->device_id;
       
         $user_code = DB::table('referral_code')
            ->where('referral_code', $code)
            ->where('user_id',$user_id)
            ->whereNotNull('user_id')
            ->first();
            
            
            if($user_code){
                   return response()->json([
                        'msg' => 'You are not able to use this referal code'
                    ]);
            }
            
        $user_code = DB::table('referral_code')
            ->where('referral_code', $code)
            ->first();
    
        if (!$user_code) {
            return response()->json([
                'msg' => 'Invalid referral code'
            ]);
        }
    
        $referred_by = $user_code->user_id;
     
     $check_deviceid_register = DB::table('referral_code')
            ->where('register_status','used')
            ->where('device_id',$device_id)
            ->first();
            
     $check_deviceid_eventregister = DB::table('referral_code')
            ->where('referral_code', $code)
            ->where('event_register_status','used')
            ->where('device_id',$device_id)
            ->first();
            
       if($check_deviceid_register){
            return response()->json([
                'msg' => 'referral used'
                //   'msg' => 'This device has already used a referral code to claim the benefits.'
            ]);
       }
    
        $check_register_status = DB::table('referral_code')
            ->where('referral_code', $code)
            ->where('used_by',$user_id)
            ->whereNotNull('register_status')
            ->first();
    
        $check_event_register = DB::table('referral_code')
            ->where('referral_code', $code)
            ->where('used_by',$user_id)
            ->whereNotNull('event_register_status')
            ->first();
            
           if ($check_event_register && $check_register_status) {
                return response()->json([
                    'msg' => 'This code has already been used'
                ]);
              }
            
    
       
    
        if (!$check_register_status) {
             $notificationData = [
                'message' => $message,
                'notification_id' => 'FitMe',
                'booking_id' => 'FitMe',
                'title' => $title,
            ];
            $user_data = DB::table('users')->where('id',$referred_by)->first();
            // dd($user_data);
          
          
            if($user_data->device_token){
                 $response = $this->sendFirebasePush([$user_data->device_token], $notificationData);
               
                $responses[$user_data->id] = $response;
            }
                DB::table('users')->where('id', $referred_by)->update([
                    'fit_coins' => DB::raw('fit_coins + 2')
                ]);
                
                //   DB::table('all_history')->insert([
                //         'user_id' =>$referred_by,
                //         'user_register' =>'done',
                //         'day'=> $current_Day,
                //         'fit_coins'  =>2
                //         ]);
                
                $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
                $current_Day = $indiaTime->format('l');
                
                 DB::table('fitcoin_history')->insert([
                     
                        'fit_coins' =>2,
                        'user_id' =>$referred_by,
                        'info' => 'referral_registerd',
                        'current_day' =>$current_Day,
                    ]);
        
                DB::table('referral_code')->insert([
                    'register_status' => 'used',
                    'used_by' => $user_id,
                    'referral_code' =>$code,
                    'device_id'=>$device_id,
                ]);
        }
    
        if (!$check_event_register) {
              $event_register_used = DB::table('referral_code')
                ->where('event_register_status','used')
                ->where('used_by',$user_id)
                ->where('referral_code')->first();
            
             
                // DB::table('users')->where('id', $referred_by)->update([
                //     'fit_coins' => DB::raw('fit_coins + 5')
                // ]);
        
                // DB::table('referral_code')->insert([
                //     'event_register_status' => 'used',
                //     'used_by' => $user_id,
                //     'referral_code' =>$code,
                //     'device_id'=>$device_id,
                // ]);
             
        }
      
        // send push notificatoion
        $send_mssg =   DB::table('users')->where('id', $referred_by)->get();
        
        
    
        return response()->json([
            'msg' => 'Referral coin added'
        ]);
    
}

   public function resize_videos(Request $request)
   {
         $directory = public_path('/music'); // Adjust path
        if (!file_exists($directory)) {
            return response()->json(['error' => 'Directory not found.']);
        }

    
        $files = scandir($directory);
        foreach ($files as $file) {
            $filePath = $directory . '/' . $file;
            if (is_file($filePath)) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
    
                if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp'])) {
                    $this->reduceImageSize($filePath);
                } elseif (in_array(strtolower($extension), ['mp4','mp4', 'mov', 'avi', 'mkv'])) {
                    $this->reduceAllVideos($filePath);
                }
            }
        }
    
        return response()->json(['success' => 'Files processed successfully!']);
           
       }
       
   private function reduceImageSize($filePath)
   {
        Image::load($filePath)
        ->quality(50) 
        ->save($filePath);

    // Optimize image further using Spatie Image Optimizer
    $optimizerChain = OptimizerChainFactory::create();
    $optimizerChain->optimize($filePath);
    }

   public function reduceAllVideos()
    {
       $directory = public_path('/music'); // Adjust path // Adjust your folder path
    
        if (!file_exists($directory)) {
            return response()->json(['error' => 'Directory not found.'], 404);
        }
    
        $videoFiles = glob($directory . '/*.{mp4,mov,avi,mkv}', GLOB_BRACE);
        // dd($videoFiles);
    
        if (empty($videoFiles)) {
            return response()->json(['message' => 'No videos found in the directory.']);
        }
    
        foreach ($videoFiles as $filePath) {
            if (is_file($filePath)) {
                // chmod($filePath, 0775);
                $this->reduceVideoSize($filePath);
            }
        }
    
        return response()->json(['message' => 'All videos have been processed.']);
    }

    private function reduceVideoSize($filePath)
    {
        try {
            $ffmpeg = FFMpeg::create();
            $video = $ffmpeg->open($filePath);
            
            $format = new X264();
            $format->setKiloBitrate(500); // Reduce bitrate for smaller size
            $format->setAudioKiloBitrate(96); // Reduce audio size
            $format->setAdditionalParameters(['-preset', 'ultrafast']); // Speed optimization
    
            $outputFile = $filePath; // Overwrite original
            $video->save($format, $outputFile);
    
            return true;
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'FFmpeg error: ' . $e->getMessage()], 500);
        }
    }

    
  
}
