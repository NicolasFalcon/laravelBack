<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Passport;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use App\Models\Bodypart;
use Carbon\Carbon;
use Google\Auth\Credentials\ServiceAccountCredentials;

class LoginController  extends Controller
{


    protected $base_url;

    public function __construct()
    {
        $this->base_url = url('/'); // or config('app.url')
    }
     public function user_login(Request $request){
 
 
    $credentials = $request->only('email', 'password');
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyLaravelApp')-> accessToken; 
            $success['userId'] = $user->id;

           return response()->json(['success' => $success], $this-> successStatus); 
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
public function send_notification(Request $request)

{

    $title = $request->input('title');
    $sound = "fitme_notification.wav";
    $type = $request->input('notificationtype');
    $message = strip_tags($request->input('message'));
    $image = $request->input('image') ?? null;

    // Retrieve all user records from the database
    $users = DB::table('users')->whereNotNull('device_token')->get();
    //singl user to send notification
        // $users = DB::table('users')->where('id', 111)->whereNotNull('device_token')->get();

    // dd($users);

    // Define notification data
    $data = [
        'message' => $message,
        'notification_id' => 'FitMe',
        'booking_id' => 'FitMe',
        'title' => $title,
        'type' => $type,
        'sound' => $sound,
    ];

    // Include image in data if it is provided
    if ($image !== null) {
        $data['image'] = $image;
    }

    // Array to store responses for each user
    $responses = [];

    // Loop through each user and send notification
    foreach ($users as $user) {
        // Check if the user has a device token
        if (!empty($user->device_token)) {
            $response = $this->sendFirebasePush([$user->device_token], $data);
            $responses[$user->id] = $response;
             DB::table('wallet')->insert([
                'user_id' => $user->id
                ]);
                
        }
    }
    // $chunkSize = 100; // Number of users per chunk
    // $userChunks = array_chunk($users, $chunkSize);
    
    // $responses = [];
    
    // foreach ($userChunks as $userChunk) {
    //     foreach ($userChunk as $user) {
    //         // Check if the user has a device token
    //         if (!empty($user->device_token)) {
    //             $response = $this->sendFirebasePush([$user->device_token], $data);
    //             $responses[$user->id] = $response;
    //         }
    //     }
        
    //     sleep(2); 
    // }

    return response()->json(['message' => 'Notifications sent successfully!', 'responses' => $responses]);
}

// public function sendFirebasePush($tokens, $data)
// {
//     $serverKey = 'AAAADgoThgU:APA91bHFusexjj2_BQ8hggO6eJgVRojGLLlk4rsWELZMf-49GO9mBW5tGxLNiFsFqC8rG15SCgOTzC8yVkQvYnK0vHFT9kx9N5UuMCf10u08KNZF6HFv9O6szfXADVHucZsVx0mOd_Xb';

//     $notification = [
//         'title' => $data['title'],
//         'body' => $data['message'],
//         'sound' => $data['sound'],
//     ];

//     $msg = [
//         'message' => $data['message'],
//         'notification_id' => 'Test',
//         'type' => $data['type'],
//         'booking_id' => 'kwh_unit_100%',
//         'title' => $data['title'],
//         'image' => $data['image'] ?? null,
//         'sound' => $data['sound'],
//     ];

//     $apnsPayload = [
//         'aps' => [
//             'mutable-content' => 1,
//             'alert' => [
//                 'title' => $data['title'],
//                 'body' => $data['message']
//             ],
//             'sound' => $data['sound'], // Ensure sound is included
//         ],
//         'fcm_options' => [
//             'image' => $data['image']
//         ]
//     ];

//     $fields = [
//         'notification' => $notification,
//         'data' => $msg,
//         'apns' => $apnsPayload,
//         'priority' => 'high'
//     ];

//     if (count($tokens) > 1) {
//         $fields['registration_ids'] = $tokens;
//     } else {
//         $fields['to'] = $tokens[0];
//     }

//     $headers = [
//         'Content-Type: application/json',
//         'Authorization: key=' . $serverKey,
//     ];

//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
//     $result = curl_exec($ch);

//     if ($result === FALSE) {
//         error_log('FCM Send Error: ' . curl_error($ch));
//     }

//     curl_close($ch);

//     return json_decode($result, true);
// }
// public function sendFirebasePush($tokens, $data)
// {
//     $serverKey = 'AAAADgoThgU:APA91bHFusexjj2_BQ8hggO6eJgVRojGLLlk4rsWELZMf-49GO9mBW5tGxLNiFsFqC8rG15SCgOTzC8yVkQvYnK0vHFT9kx9N5UuMCf10u08KNZF6HFv9O6szfXADVHucZsVx0mOd_Xb';

//     $notification = [
//         'title' => $data['title'],
//         'body' => $data['message'],
//         'sound' => $data['sound'],
//     ];

//     $msg = [
//         'sound' => $data['sound'],
//         'message' => $data['message'],
//         'notification_id' => 'Test',
//         'type' => $data['type'],
//         'booking_id' => 'kwh_unit_100%',
//         'title' => $data['title'],
//         'image' => $data['image'] ?? null,
//         'notification' => $notification,
//         'apns' => [
//             'payload' => [
//                 'aps' => [
//                     'mutable-content' => 1,
//                     'sound' => $data['sound'], // Added sound to APNs payload
//                 ],
//             ],
//             'fcmOptions' => [
//                 'imageUrl' => $data['image'],
//             ],
//         ],
//     ];
    
//       $notifyData = [
//         'body' => $data['message'],
//         'notification_id' => $data['notification_id'],
//         'booking_id' => $data['booking_id'],
//         'title' => $data['title'],
//         'image' => $data['image'] ?? null,
//         'data' => $data['sound'] ?? null,
//     ];

//     $apnsPayload = [
//         'aps' => [
//             'mutable-content' => 1,
//             'alert' => [
//                 'title' => $data['title'],
//                 'body' => $data['message'],
//             ],
//             'sound' => $data['sound'],
//         ],
//         'fcm_options' => [
//             'image' => $data['image'],
//         ],
//     ];

//  $registrationIds = $tokens;
//      if (count($tokens) > 1) {
//         $fields = [
//             'registration_ids' => $registrationIds,
//             'notification' => $notifyData,
//             'data' => $msg,
//             'apns' => $apnsPayload,
//             'priority' => 'high'
//         ];
//     } else {
//         $fields = [
//             'to' => $registrationIds[0],
//             'notification' => $notifyData,
//             'data' => $msg,
//             'apns' => $apnsPayload,
//             'priority' => 'high'
//         ];
//     }

//     // if (count($tokens) > 1) {
//     //     $fields['registration_ids'] = $tokens;
//     // } else {
//     //     $fields['to'] = $tokens[0];
//     // }

//     $headers = [
//         'Content-Type: application/json',
//         'Authorization: key=' . $serverKey,
//     ];

//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
//     $result = curl_exec($ch);

//     if ($result === FALSE) {
//         error_log('FCM Send Error: ' . curl_error($ch));
//     }

//     curl_close($ch);

//     return json_decode($result, true);
// }

public function sendFirebasePush($tokens, $data)
    {
         $pathToServiceAccount = storage_path('app/keys/fitme-f65b3-9ad5f96a3e61.json');

    if (!file_exists($pathToServiceAccount)) {
        die("Service account file does not exist.");
    }

    $projectId = 'fitme-f65b3';  // Replace with your Firebase project ID
    $endpoint = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

    // Create a credentials object
    $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
    $credentials = new ServiceAccountCredentials($scopes, $pathToServiceAccount);

    // Fetch the access token
    try {
        $accessToken = $credentials->fetchAuthToken()['access_token'];
    } catch (\Exception $e) {
        error_log('Error fetching access token: ' . $e->getMessage());
        return ['status' => 'error', 'message' => 'Unable to fetch access token'];
    }

    // Prepare message payload
    $fields = [
        'message' => [
            'token' => $tokens[0],  // Send to a single token or loop through if multiple
            'notification' => [
                'title' => $data['title'],
                'body' => $data['message'],
            ],
            'data' => [
                'message' => $data['message'],
                'notification_id' => 'Test',
                'type' => $data['type'] ?? 'default_type',
                'booking_id' => 'kwh_unit_100%',
                'image' => $data['image'] ?? null,
            ],
            'android' => [
                'priority' => 'high',
            ],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'mutable-content' => 1,
                    ],
                ],
            ],
        ],
    ];

    // Set up the headers for the request
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken,
    ];

    // Initialize CURL for sending the request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    // Execute the CURL request
    $result = curl_exec($ch);

    // Check for CURL errors
    if ($result === FALSE) {
        error_log('FCM Send Error: ' . curl_error($ch));
        return ['status' => 'error', 'message' => 'Failed to send notification'];
    }

    // Close the CURL session
    curl_close($ch);

    // Decode the result to interpret the response
    $responseData = json_decode($result, true);

    // Log the response for debugging
    error_log('FCM Response: ' . print_r($responseData, true));

    // Check if the response was successful
    if (isset($responseData['name'])) {
        return [
            'status' => 'success',
            'message' => 'Notification sent successfully.',
            'response' => $responseData,
        ];
    } else {
        return [
            'status' => 'failure',
            'message' => 'Failed to send notification.',
            'response' => $responseData,
        ];
    }
    }




//   public function send_notification(Request $request)
// {
//     // dd('testing');
//     // Validate incoming request data
//     // $validatedData = $request->validate([
//     //     'title' => 'required|string',
//     //     'message' => 'required|string',
//     //     'image' => 'nullable|string',
//     //     'notificationtype' => 'nullable|string',
//     // ]);



//     // Extract necessary data from the request
//     $title = $request->input('title');
//     $type = $request->input('notificationtype');
//     $message = strip_tags($request->input('message'));
//     $image = $request->input('image') ?? null;

//     // Retrieve all user records from the database
//     $users = DB::table('users')
//     ->get();
//     // dd($users);
   

//     // Define notification data
//     $data = [
//         'message' => $message,
//         'notification_id' => 'FitMe',
//         'booking_id' => 'FitMe',
//         'title' => $title,
//         'type' =>$type,
//     ];
    

//     // Include image in data if it is provided
//     if ($image !== null) {
//         $data['image'] = $image;
//     }

//     // Array to store responses for each user
//     $responses = [];

//     // Loop through each user and send notification
//     foreach ($users as $user) {
//         // Check if the user has a device token
//         if (!empty($user->device_token)) {
//             // Send notification to the user
            
//             // dd($user->device_token);
//             $response = $this->sendFirebasePush([$user->device_token], $data);
//             $responses[$user->id] = $response;
//         }
//     }

//     return response()->json(['message' => 'Notifications sent successfully!', 'responses' => $responses]);
// }

  
    

// public function sendFirebasePush($tokens, $data)
// {
//     $serverKey = 'AAAADgoThgU:APA91bHFusexjj2_BQ8hggO6eJgVRojGLLlk4rsWELZMf-49GO9mBW5tGxLNiFsFqC8rG15SCgOTzC8yVkQvYnK0vHFT9kx9N5UuMCf10u08KNZF6HFv9O6szfXADVHucZsVx0mOd_Xb';

//     $msg = [
//         'message' => $data['message'],
//         'notification_id' => 'Test',
//         'type'   => $data['type'],
//         'booking_id' => 'kwh_unit_100%',
//         'title' => $data['title'],
//         'image' => $data['image']??null,
//         'notification' => [
//             'body' => $data['message'],
//             'title' => $data['title'],
//         ],
//         'apns' => [
//             'payload' => [
//                 'aps' => [
//                     'mutable-content' => 1,
//                 ],
//             ],
//             'fcmOptions' => [
//                 'imageUrl' => $data['image'],
//             ],
//         ],
//     ];

//     $notifyData = [
//         "body" => $data['message'],
//         'notification_id' => $data['notification_id'],
//         'booking_id' => $data['booking_id'],
//         'title' => $data['title'],
//         'image' => $data['image']??null,
//     ];

//     $apnsPayload = [
//         'payload' => [
//             'aps' => [
//                 'mutable-content' => 1,
//                 'alert' => [
//                     'title' => $data['title'],
//                     'body' => $data['message']
//                 ]
//             ]
//         ],
//         'fcmOptions' => [
//             'imageUrl' => $data['image']
//         ]
//     ];

//     $registrationIds = $tokens;

//     if (count($tokens) > 1) {
//         $fields = [
//             'registration_ids' => $registrationIds,
//             'notification' => $notifyData,
//             'data' => $msg,
//             'apns' => $apnsPayload,
//             'priority' => 'high'
//         ];
//     } else {
//         $fields = [
//             'to' => $registrationIds[0],
//             'notification' => $notifyData,
//             'data' => $msg,
//             'apns' => $apnsPayload,
//             'priority' => 'high'
//         ];
//     }

//     $headers = [
//         'Content-Type: application/json',
//         'Authorization: key=' . $serverKey,
//     ];

//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
//     $result = curl_exec($ch);

//     if ($result === FALSE) {
//         // Log error instead of dying
//         error_log('FCM Send Error: ' . curl_error($ch));
//     }

//     curl_close($ch);

//     // Return the result as JSON
//     return [
//         'result' => $result,
//         'ios' => [
//             'categoryId' => 'default',
//             'foregroundPresentationOptions' => [
//                 'badge' => true,
//                 'sound' => true,
//                 'banner' => true,
//                 'list' => true,
//             ],
//             'attachments' => [
//                 [
//                     'url' => $data['image'],
//                 ],
//             ],
//         ],
//     ];
// }



    public function send_jsonfile()
    {
        // Read the JSON file
        $json = file_get_contents(storage_path('app/1-min-stitched.json'));

        // Decode JSON
        $data = json_decode($json, true);

        // Return as response
        return response()->json($data);
    }


    public function send_notification_payment()
    {
        // Fetch users and their device tokens
        $users = DB::table('transaction')
            ->select('transaction.user_id', 'transaction.plan_start_date', 'transaction.plan_end_date', 'transaction.plan_name', 'users.device_token')
            ->join('users', 'transaction.user_id', '=', 'users.id')
            ->where('plan_status', 'Active')
            ->get();
           

        // Define notification data outside the loop
     

        foreach ($users as $user) {
            $planStartDate = Carbon::parse($user->plan_start_date);
            $daysDifference = Carbon::now()->diffInDays($planStartDate);
         
           
            // Adjust the notification timing based on the plan type
            if ($user->plan_name == 'Monthly') {
                if ($daysDifference >= 27 && $daysDifference < 29) {
                    // Send notification to the user
                       $data = [
                            'message' => 'Your plan has been expired soon',
                            'notification_id' => 'Test',
                            'booking_id' => 'kwh_unit_100%',
                            'title' => 'Fit Me',
                            'image' => 'image',
                        ];
                    
                    $tokens = [$user->device_token];
                    $response = $this->sendFirebasePush($tokens, $data);
                  
                }
            if($daysDifference == 30){
                DB::table('transaction')
                ->where('user_id', $user->user_id)
                ->update(['plan_status' => 'Not Active']);
                 $data = [
                            'message' => 'Your plan has been expired',
                            'notification_id' => 'Test',
                            'booking_id' => 'kwh_unit_100%',
                            'title' => 'Fit Me',
                            'image' => 'image',
                        ];
                    
                    $tokens = [$user->device_token];
                    $response = $this->sendFirebasePush($tokens, $data);
            }
            } elseif ($user->plan_name == 'Quarterly') {
                if ($daysDifference >= 87 && $daysDifference <= 89) {
                    // Send notification to the user
                       $data = [
                            'message' => 'Your plan has been expired soon',
                            'notification_id' => 'Test',
                            'booking_id' => 'kwh_unit_100%',
                            'title' => 'Fit Me',
                            'image' => 'image',
                        ];
                    $tokens = [$user->device_token];
                    $response = $this->sendFirebasePush($tokens, $data);
                }
             if($daysDifference == 90){
                DB::table('transaction')
                ->where('user_id', $user->user_id)
                ->update(['plan_status' => 'Not Active']);
                 $data = [
                            'message' => 'Your plan has been expired',
                            'notification_id' => 'Test',
                            'booking_id' => 'kwh_unit_100%',
                            'title' => 'Fit Me',
                            'image' => 'image',
                        ];
                    
                    $tokens = [$user->device_token];
                    $response = $this->sendFirebasePush($tokens, $data);
            }
            }
            elseif ($user->plan_name == 'Yearly') {
                if ($daysDifference >= 361 && $daysDifference <= 364) {
                    // Send notification to the user
                     $data = [
                            'message' => 'Your plan has been expired soon',
                            'notification_id' => 'Test',
                            'booking_id' => 'kwh_unit_100%',
                            'title' => 'Fit Me',
                            'image' => 'image',
                        ];
                    $tokens = [$user->device_token];
                    $response = $this->sendFirebasePush($tokens, $data);
                }
            }
              if($daysDifference == 365){
                DB::table('transaction')
                ->where('user_id', $user->user_id)
                ->update(['plan_status' => 'Not Active']);
                 $data = [
                            'message' => 'Your plan has been expired',
                            'notification_id' => 'Test',
                            'booking_id' => 'kwh_unit_100%',
                            'title' => 'Fit Me',
                            'image' => 'image',
                        ];
                    
                    $tokens = [$user->device_token];
                    $response = $this->sendFirebasePush($tokens, $data);
            }
        }

        // Return the response after processing all notifications
        return response()->json(['message' => 'Notifications sent successfully']);
    }
    
    public function getNearbyGyms(Request $request)
{
    // Get latitude and longitude from the request
    $latitude = $request->input('latitude');
    $longitude = $request->input('longitude');
    
    // Define the radius within which to search for nearby gym centers (in kilometers)
    $radius = 10;
    
    // Calculate distances and retrieve nearby gym centers
    $nearbyGyms = DB::table('gym_center')->select('*')
        ->where('service', 'gym') // Assuming 'type' is the column specifying the type of location
        ->selectRaw(
            '( 6371 * acos( cos( radians(?) ) *
            cos( radians( latitude ) )
            * cos( radians( longitude ) - radians(?)
            ) + sin( radians(?) ) *
            sin( radians( latitude ) ) )
            ) AS distance', [$latitude, $longitude, $latitude]
        )
        ->having('distance', '<', $radius)
        ->orderBy('distance')
        ->get();
    
    
 
    if ($nearbyGyms->isEmpty()) {
         
        return response()->json(['mssg' => 'No data found']);
    }else{
    
    return response()->json(['nearby_gyms' => $nearbyGyms]);
    }
}





}
