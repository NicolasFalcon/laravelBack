<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Google\Auth\CredentialsLoader;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Google\Auth\Credentials\ServiceAccountCredentials;

class PlansController extends Controller
{


    protected $base_url;

    public function __construct()
    {
        $this->base_url = url('/'); // or config('app.url')
    }


    public function testing(){
    }
    
    public function add_coin(Request $request){
        
         $user_id = $request->user_id;
         $fit_coins = $request->fit_coins;
         $check_data = DB::table('wallet')->where('user_id',$user_id)->first();
         
         if($check_data){
             DB::table('wallet')->where('user_id',$user_id)->update([
                 'fit_coins' => DB::raw('fit_coins + ' . $fit_coins)
                 ]);
                 
              return response()->json([
                      'msg' => 'account update successfully'
                    ]);
         }
         
         DB::table('wallet')->insert([
                 'user_id' => $user_id,
                 'fit_coins' => $fit_coins,
                 
                 ]);
                 
              return response()->json([
                      'msg' => 'account created successfully'
                    ]);
         
    }
    
    public function get_event_data(Request $request){
        
          $user_id = $request->user_id;
          $data = DB::table('fitme_event')
            ->join('users', 'users.id', '=', 'fitme_event.user_id')
            ->where('fitme_event.user_id',$user_id)
            ->first();
            
            if(empty($data)){
                return response()->json(['data' => 'no data found']);
            }
                 
         return response()->json(['data' =>$data]);
        
    }
    public function convert_coins(Request $request){
        
          $user_id = $request->user_id;
          $check_data = DB::table('wallet')->where('user_id',$user_id)->first();
         
         if(!$check_data){
              return response()->json([
                      'msg' => 'no data found'
                    ]);
         }
         
          $data = DB::table('wallet')->where('user_id',$user_id)->first();
          
         $rupees = $data->fit_coins/2;
         
         DB::table('wallet')->where('user_id',$user_id)->update([
             
             'Rupees' => DB::raw('rupees + ' . $rupees),
             'fit_coins' => 0,
             ]);
             
         return response()->json([
                      'msg' => 'coins converted'
                    ]);

    }
    
    public function create_code(Request $request){
          $user_id = $request->user_id;
          $amount = $request->amount;
          $check_data = DB::table('referral_code')->where('user_id',$user_id)->first();
          
          if($check_data){
               return response()->json([
                      'msg' => 'code alrady created'
                    ]);
          }
          
          DB::table('referral_code')->insert([
              'user_id' => $user_id,
              'amount' => $amount,
              'referral_code' =>124,
              'used' => 'no',
              ]);
           return response()->json([
                  'msg' => 'code created'
                ]);
          
    }
    
    public function get_plans(Request $request){
      
        $version = $request->input('version');
        $user_id = $request->input('user_id');
        $plan_id = $request->input('plan_id');
        $plan_name = $request->input('plan_name'); 
        $amount = $request->input('amount'); 
        $check_plan = DB::table('fitme_plans_transactions')->where('user_id', $user_id)->first();
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        
        if($check_plan){
            $prev_plan = $check_plan->plan_id;
            
            
            
            
              return response()->json([
                  'msg' => 'plan alrady exist'
                ]);
            
        }
        if ($versions_current) {
            
           DB::table('fitme_plans_transactions')->insert([
               'user_id' => $user_id,
               'plan_name' => $plan_name,
               'amount' =>$amount,
               'plan_id' =>$plan_id,

               ]);
               
                  return response()->json([
                  'msg' => 'done'
                ]);

        } elseif ($versions_middle) {
             DB::table('fitme_plans_transactions')->insert([
               'user_id' => $user_id,
               'plan_name' => $plan_name,
               'amount' =>$amount,
               'plan_id' =>$plan_id,

               ]);
               
                  return response()->json([
                  'msg' => 'done'
                ]);

            
            
        } elseif ($versions_past) {
             
             DB::table('fitme_plans_transactions')->insert([
               'user_id' => $user_id,
               'plan_name' => $plan_name,
               'amount' =>$amount,
               'plan_id' =>$plan_id,

               ]);
               
                  return response()->json([
                  'msg' => 'done'
                ]);

           
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }
     public function get_active_plans(Request $request){
        $version = $request->input('version');
        $user_id = $request->input('user_id');
        $plan_id = $request->input('plan_id');
        $amount = $request->input('amount'); 
        $today = Carbon::now();
            $currentMonth = $today->month;
            $currentYear = $today->year;
            
        $check_plan = DB::table('fitme_plans_transactions')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->get();
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        
     
        if ($versions_current) {
         
               
                  return response()->json([
                  'data' => $check_plan
                ]);

        } elseif ($versions_middle) {
            
                  return response()->json([
                  'data' => $check_plan
                ]);

            
            
        } elseif ($versions_past) {
             
            
                  return response()->json([
                  'data' => $check_plan
                ]);

           
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }
    
 public function leader_board(Request $request)
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
        
        $topUsers = DB::table('users')
          ->join('fitme_event', 'fitme_event.user_id', '=', 'users.id')
            ->select('users.name', 'users.fit_coins', 'users.id', 'users.image')
            ->where('fitme_event.current_day_status',1)
            // ->where('users.country','india')
            //  ->where(function($query) {
            //     $query->where('users.country', 'United States')
            //           ->orWhere('users.country', 'India');
            // })
                    
            // ->whereNotNull('users.country')
            ->distinct('users.id')
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
            //   ->where(function($query) {
            //         $query->where('users.country', 'United States')
            //               ->orWhere('users.country', 'India');
            //     })
            ->where('fitme_event.current_day_status',1)
            // ->whereNotNull('users.country')
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
                    // ->where(function($query) {
                    //     $query->where('users.country', 'United States')
                    //           ->orWhere('users.country', 'India');
                    // })
                    ->where('fitme_event.current_day_status',1)
                    // ->whereNotNull('users.country')
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
                    // ->where(function($query) {
                    // $query->where('users.country', 'United States')
                    //               ->orWhere('users.country', 'India');
                    //     })
                    ->where('fitme_event.current_day_status',1)
                    // ->whereNotNull('users.country')
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
                // ->where(function($query) {
                //     $query->where('users.country', 'United States')
                //           ->orWhere('users.country', 'India');
                // })
                // ->whereNotNull('country')
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






      public function event_details(Request $request ,$id){
          
       $version = $request->version;
       
       if($version){
            $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
            $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
            $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
            
            
            if($versions_current){
                
              $free_status = true;
              $check_free_plan = DB::table('fitme_event')->where('user_id',$id)->where('product_id','fitme_free')->first();
              
              if($check_free_plan){
                  $free_status = false;
              }
              
              $currentDay = Carbon::now('Asia/Kolkata')->dayOfWeek;
                //   $currentDay="Monday";
            
                $details = DB::table('fitme_event')
                    ->select('*')
                    ->where('user_id', $id)
                    ->orderByDesc('id') // Assuming 'id' is the primary key
                    ->first();
                    
                    
            
                if($details){
                    $details->currentDay = $currentDay;
                     $details->free_status = $free_status;// Add current day to details object
                    return response()->json(['data' => $details]);
                } else {
                    return response()->json(['message' => 'Not any subscription','free_status'=>$free_status]);
                }
                
            }
            if($versions_middle){
                
                 $currentDay = Carbon::now('Asia/Kolkata')->dayOfWeek;
                //   $currentDay="Monday";
            
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
            if($versions_past){
                 $currentDay = Carbon::now('Asia/Kolkata')->dayOfWeek;
                //   $currentDay="Monday";
            
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


       }else{
           
         $currentDay = Carbon::now('Asia/Kolkata')->dayOfWeek;
        //   $currentDay="Monday";
    
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
     
}



//     public function event_exercise_complete_status(Request $request)
// {
//     $day = $request->day;
//     $id = $request->id;
//     $workout_id = $request->workout_id;
//     $user_id = $request->user_id;
//     $version = $request->input('version');
//     $next_status = $request->next_status;
//     $prev_status = $request->prev_status;
//     $skip_status = $request->skip_status;
//     $current_date = Carbon::now();
//     $current_date_gmt_plus_5_30 = $current_date->setTimezone('Asia/Kolkata'); // GMT+5:30 timezone

//     $only_date = $current_date_gmt_plus_5_30->toDateString(); // Get only the date part
//     $only_time = $current_date_gmt_plus_5_30->toTimeString(); // Get only the time part

//     $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
//     $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
//     $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

//     if ($versions_current || $versions_middle) {
//         $status = DB::table('event_exercise_completion_status')
//             ->select('exercise_status')
//             ->where('id', $id)
//             ->first();

//         if ($status) {
//             if ($status->exercise_status == 'undone') {
//                 // Update the exercise status to completed
//                 DB::table('event_exercise_completion_status')
//                     ->where('id', $id)
//                     ->update([
//                         'exercise_status' => 'completed',
//                         'completed_date' => $only_date,
//                         'completed_time' => $only_time,
//                         'next_status' => $next_status,
//                         'prev_status' => $prev_status,
//                         'skip_status' => $skip_status
//                     ]);

//                   // Get the last completed exercise status
//                 $lastCompletedStatus = DB::table('event_exercise_completion_status')
//                     ->select('completed_date', 'completed_time')
//                     ->where('user_id', $user_id)
//                     ->where('exercise_status', 'completed')
//                     ->orderBy('completed_date', 'desc')
//                     ->orderBy('completed_time', 'desc')
//                     ->skip(1) // Skip the current one
//                     ->first();

//                 if ($lastCompletedStatus) {
//                     $lastCompletedDateTime = Carbon::parse($lastCompletedStatus->completed_date . ' ' . $lastCompletedStatus->completed_time);
//                     $currentCompletedDateTime = Carbon::parse($only_date . ' ' . $only_time);

//                     $diffInHours = $lastCompletedDateTime->diffInHours($currentCompletedDateTime);
                      

//                     // Check if the current time is greater than the last completed time
//                      if ($currentCompletedDateTime->greaterThan($lastCompletedDateTime) && $diffInHours > 24) {
//                      $adjustedHours = $diffInHours - 24;
                     
                       
//                         // Check if points have already been deducted for this period
//                         $alreadyDeducted = DB::table('event_exercise_completion_status')
//                             ->where('user_id', $user_id)
//                             ->where('completed_date', $lastCompletedStatus->completed_date)
//                             ->where('deducted', 1)
//                             ->exists();

//                         if (!$alreadyDeducted) {
//                             // Deduct points based on the number of hours difference
                            
//                               $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
//                               $current_Day = $indiaTime->format('l');
                              
//                             DB::table('users')
//                                 ->where('id', $user_id)
//                                 ->decrement('fit_coins', $adjustedHours);
                                
//                                  DB::table('fitcoin_history')->insert([
//                                     'user_id'   =>$user_id,
//                                     'info'=>'late',
//                                     'fit_coins' => -1,
//                                     'current_day' =>$current_Day
//                                     ]);

//                             // Update the record to mark points as deducted
//                             DB::table('event_exercise_completion_status')
//                                 ->where('id', $id)
//                                 ->update(['deducted' => 1]);
//                         }
//                     }
//                 }


//                 // Check if all exercises for the day are completed
//                 $dayStatuses = DB::table('event_exercise_completion_status')
//                     ->select('exercise_status')
//                     ->where('user_day', $day)
//                     ->where('workout_id', $workout_id)
//                     ->where('user_id', $user_id)
//                     ->get();

//                 $allCompleted = $dayStatuses->every(function ($status) {
//                     return $status->exercise_status == 'completed';
//                 });

//                 if ($allCompleted) {
//                     DB::table('event_exercise_completion_status')
//                         ->where('user_day', $day)
//                         ->where('workout_id', $workout_id)
//                         ->where('user_id', $user_id)
//                         ->update(['final_status' => 'allcompleted']);
//                 }

//                 return response()->json(['msg' => 'Exercise Status Updated to Completed']);
//             } elseif ($status->exercise_status == 'completed') {
//                 return response()->json(['msg' => 'Exercise Status is Already Completed']);
//             } else {
//                 return response()->json(['msg' => 'Invalid Exercise Status']);
//             }
//         } else {
//             return response()->json(['msg' => 'No Exercise Status Found for the given ID']);
//         }
//     } else {
//         return response()->json(['msg' => 'Please update the app to the latest version.']);
//     }
// }

     public function event_exercise_complete_status(Request $request)
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
                            // $alreadyDeducted = DB::table($dbtable)
                            //     ->where('user_id', $user_id)
                            //   // ->where('completed_date', $lastCompletedStatus->completed_date)
                            //     ->where('deducted', 1)
                            //     ->exists();
    
                            if (!$alreadyDeducted) {
                                // Deduct points based on the number of hours difference
                                
                                  $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
                                  $current_Day = $indiaTime->format('l');
                                  
                                // DB::table('users')
                                //     ->where('id', $user_id)
                                //     ->decrement('fit_coins', $adjustedHours);
                                    
                                    //  DB::table('fitcoin_history')->insert([
                                    //     'user_id'   =>$user_id,
                                    //     'info'=>'late',
                                    //     'fit_coins' => $adjustedHours,
                                    //     'current_day' =>$current_Day
                                    //     ]);
                                    
                                    //  DB::table($dbtable)
                                    // ->where('user_id', $user_id)
                                    // ->decrement('today_earning', $adjustedHours);    
    
                                // Update the record to mark points as deducted
                                // DB::table($dbtable)
                                //     ->where('id', $id)
                                //     ->update(['deducted' => 1]);
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

    
    
    // public function user_event__exercise_status(Request $request)
    // {
    //     $user_details_list = $request->input('user_details');
    //     $insertedData = [];
    //     $existingData = [];
    //     $msg = "";
    //     $datago = 0;
    
    //     foreach ($user_details_list as $user_details) 
    //     {
    //         // Check if all required keys exist in $user_details
    //         if (
    //             isset($user_details['user_id']) &&
    //             isset($user_details['workout_id']) &&
    //             isset($user_details['user_exercise_id']) &&
    //             isset($user_details['user_day']) &&
    //             isset($user_details['fit_coins'])
    //         ) 
    //         {
    //             // Check if the data already exists
    //             $existingRecord = DB::table('event_exercise_completion_status')
    //                 ->where('user_id', $user_details['user_id'])
    //                 ->where('workout_id', $user_details['workout_id'])
    //                 ->where('user_exercise_id', $user_details['user_exercise_id'])
    //                 ->where('user_day', $user_details['user_day'])
    //                 ->where('fit_coins', $user_details['fit_coins'])
    //                 ->first();
    
    //             if ($existingRecord) 
    //             {
    //                 $existingData[] = $existingRecord;
    //                 $datago = 1;
    //             } 
    //             else 
    //             {
    //                 // Perform insert with 'undone' status
    //                 $insertedRecord = DB::table('event_exercise_completion_status')->insertGetId([
    //                     'user_id' => $user_details['user_id'],
    //                     'workout_id' => $user_details['workout_id'],
    //                     'user_exercise_id' => $user_details['user_exercise_id'],
    //                     'user_day' => $user_details['user_day'],
    //                     'fit_coins' => $user_details['fit_coins'],
    //                     'exercise_status' => 'undone' // Always set the status to 'undone'
    //                 ]);
    
    //                 // Fetch the inserted record and add it to the result array
    //                 $insertedData[] = DB::table('event_exercise_completion_status')
    //                     ->where('id', $insertedRecord)
    //                     ->first();
    //                 $datago = 2;
    //                 $msg = "Exercise Status for All Users Inserted Successfully";
    //             }
    //         } 
    //         else 
    //         {
    //             $msg = "Required keys are missing in user_details";
    //         }
    //     }
    
    //     if ($datago == 2) 
    //     {
    //         return response()->json(['msg' => $msg, 'inserted_data' => $insertedData]);
    //     } 
    //     elseif ($datago == 1) 
    //     {
    //         $msg = "All user exercise data already exists";
    //         return response()->json(['msg' => $msg, 'existing_data' => $existingData]);
    //     } 
    //     else 
    //     {
    //         return response()->json(['msg' => $msg]);
    //     }
    // }
    
    public function user_event__exercise_status(Request $request)
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

   public function send_push_notification_sunday(Request $request)
    {
    $upcomingSunday = Carbon::now()->next(Carbon::SUNDAY);
    $message = 'Get Ready to Move and Win!🏅' . "\n" . 'Our exciting Fitness Event is almost here! Tap now and get ready to win some awesome rewards.🎁';
    $title = '📢 Exciting News!';
    
    DB::table('wallet')->insert([
            'user_id'=>'sunday'
            ]);
    
    $event_data = DB::table('fitme_event')
        ->join('users', 'fitme_event.user_id', '=', 'users.id')
        // ->where('users.country', 'India')
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
          
            if ($data->upcoming_day_status == 0 && $data->current_day_status == 1 ) {
                 DB::table('fitme_event')
                    ->where('user_id',$data->user_id)
                    ->update([
                        'current_day_status' => 0,
            
                    ]);
                // $response = $this->sendFirebasePush([$data->device_token], $notificationData);
                // $responses[$data->id] = $response;
            } if($data->upcoming_day_status == 1){
               
                DB::table('fitme_event')
                   ->where('user_id',$data->user_id)
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
                $response = $this->sendFirebasePush([$data->device_token], $notificationData);
                $responses[$data->id] = $response;
                
            }

            DB::table('users')
                ->where('id', $data->user_id)  // Corrected user ID reference
                ->update(['fit_coins' => 0]);

            DB::table('event_exercise_completion_status')->truncate();
            DB::table('cardio_exercise_complete_status')->truncate();
            DB::table('fitcoin_history')->truncate();
            DB::table('breathin_session')->truncate();
        }
    }

    return response()->json(['message' => 'Notifications sent successfully!', 'responses' => $responses]);
}

 public function send_push_notification_sunday_us(Request $request)
    {
    $upcomingSunday = Carbon::now()->next(Carbon::SUNDAY);
    $message = 'Get Ready to Move and Win!🏅' . "\n" . 'Our exciting Fitness Event is almost here! Tap now and get ready to win some awesome rewards.🎁';
    $title = '📢 Exciting News!';
    
    $event_data = DB::table('fitme_event')
        ->join('users', 'fitme_event.user_id', '=', 'users.id')
        // ->where('users.country', 'United States')
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
          
            if ($data->upcoming_day_status == 0 && $data->current_day_status == 1 ) {
                 DB::table('fitme_event')
                  ->join('users', 'fitme_event.user_id', '=', 'users.id')
                    ->where('fitme_event.user_id',$data->user_id)
                    // ->where('users.country','United States')
                    ->update([
                        'current_day_status' => 0,
            
                    ]);
                // $response = $this->sendFirebasePush([$data->device_token], $notificationData);
                // $responses[$data->id] = $response;
            } if($data->upcoming_day_status == 1){
               
                DB::table('fitme_event')
                   ->join('users', 'fitme_event.user_id', '=', 'users.id')
                    ->where('fitme_event.user_id',$data->user_id)
                    // ->where('users.country','United States')
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
                //  ->where('country','United States')// Corrected user ID reference
                 ->update(['fit_coins' => 0]);

            // DB::table('event_exercise_completion_status')->truncate();
            // DB::table('cardio_exercise_complete_status')->truncate();
            // DB::table('fitcoin_history')->truncate();
            // DB::table('breathin_session')->truncate();
        }
    }

    return response()->json(['message' => 'Notifications sent successfully!', 'responses' => $responses]);
}






   
   
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
    
//   public function send_mail_winner() {
       
       
//       $currentDay = Carbon::now()->format('l');
      
//     //Retrieve the user with the highest fit_coins from the fitme_event table
//     $event_data = DB::table('fitme_event')
//         ->join('users', 'fitme_event.user_id', '=', 'users.id')
//         ->select('users.fit_coins', 'fitme_event.user_id', 'users.email','users.name')
//         ->where('users.id',9833)
//         ->orderBy('users.fit_coins', 'desc')
//         ->first();
//          $email = $event_data->email;
//         //  dd($email);
        
       
//             $message = "Check Out The Winner of this Week's Challenge";
//             $title = 'Winner Winner Chicken Dinner🏆✨';
       
//         $notificationData = [
//             'message' => $message,
//             'notification_id' => 'FitMe',
//             'booking_id' => 'FitMe',
//             'title' => $title,
//             'type' => 'event_saturday',
//         ];
        
//         $responses = [];
//     if ($event_data) {
        
        
//         $data = [
//             'fit_coins' => $event_data->fit_coins,
//             'user_id' => $event_data->user_id,
//           'name' => $event_data->name
//         ];
//         // dd($data);
//         //  $arrdata = DB::table('fitme_event')
//         //         ->join('users', 'fitme_event.user_id', '=', 'users.id')
//         //         ->select('users.fit_coins', 'fitme_event.user_id', 'users.email','users.name')
//         //         ->where('users.id',$arr)
//         //         // ->orderBy('users.fit_coins', 'desc')
//         //         ->first();
//         //         $email = $arrdata->email;
//         //     echo $arr;
            
//         //     $arr_data = DB::table('fitme_event')
//         //         ->join('users', 'fitme_event.user_id', '=', 'users.id')
//         //         ->select('users.fit_coins', 'fitme_event.user_id', 'users.email','users.name')
//         //         ->where('users.id',$arr)
//         //         // ->orderBy('users.fit_coins', 'desc')
//         //         ->first();
//         //         // dd($arr_data);
                
                
                
//                 //   $data = [
//                 //         'fit_coins' => $arr_data->fit_coins,
//                 //         'user_id' => $arr_data->user_id,
//                 //       'name' => $arr_data->name
//                 //     ];
//                     print_r($data);
//                       Mail::send('winner_body', ['data' => $data], function ($message) use ($email) {
//                      $message->to($email)->subject('Congratulations! You are a Winner!');
//                      dd('mail sent');
//         });
//          $user_data = DB::table('fitme_event')
//             ->join('users', 'fitme_event.user_id', '=', 'users.id')
//             // ->where('fitme_event.event_start_date_current', '>', $upcomingSunday)
//             ->where('users.id',$arr)
//              ->select('users.fit_coins', 'fitme_event.user_id', 'users.email','users.name','users.device_token')
//               ->get(); 
//          foreach ($user_data as $data) {
             
//              echo "notificaton";
//              print_r($data);
//             if (!empty($data->device_token)) {
//                 // $response = $this->sendFirebasePush_message([$data->device_token], $notificationData);
//                 // $responses[$data->user_id] = $response;
//             }
//          }

//         // Mail::send('winner_body', ['data' => $data], function ($message) use ($email) {
//         //     $message->to($email)->subject('Congratulations! You are a Winner!');
//         // });
//         //  $user_data = DB::table('fitme_event')
//         //     ->join('users', 'fitme_event.user_id', '=', 'users.id')
//         //     // ->where('fitme_event.event_start_date_current', '>', $upcomingSunday)
//         //     ->where('users.id',9833)
//         //      ->select('users.fit_coins', 'fitme_event.user_id', 'users.email','users.name','users.device_token')
//         //       ->get(); 
//         //  foreach ($user_data as $data) {
//         //     if (!empty($data->device_token)) {
//         //         $response = $this->sendFirebasePush_message([$data->device_token], $notificationData);
//         //         $responses[$data->user_id] = $response;
//         //     }
        
//         // $email_arr=[9821,9994,9830];
//         // foreach($email_arr as $arr){
//         //     $arrdata = DB::table('fitme_event')
//         //         ->join('users', 'fitme_event.user_id', '=', 'users.id')
//         //         ->select('users.fit_coins', 'fitme_event.user_id', 'users.email','users.name')
//         //         ->where('users.id',$arr)
//         //         // ->orderBy('users.fit_coins', 'desc')
//         //         ->first();
//         //         $email = $arrdata->email;
//         //     echo $arr;
            
//         //     $arr_data = DB::table('fitme_event')
//         //         ->join('users', 'fitme_event.user_id', '=', 'users.id')
//         //         ->select('users.fit_coins', 'fitme_event.user_id', 'users.email','users.name')
//         //         ->where('users.id',$arr)
//         //         // ->orderBy('users.fit_coins', 'desc')
//         //         ->first();
//         //         // dd($arr_data);
                
                
                
//         //           $data = [
//         //                 'fit_coins' => $arr_data->fit_coins,
//         //                 'user_id' => $arr_data->user_id,
//         //               'name' => $arr_data->name
//         //             ];
//         //             print_r($data);
//         //               Mail::send('winner_body', ['data' => $data], function ($message) use ($email) {
//         //              $message->to($email)->subject('Congratulations! You are a Winner!');
//         // });
//         //  $user_data = DB::table('fitme_event')
//         //     ->join('users', 'fitme_event.user_id', '=', 'users.id')
//         //     // ->where('fitme_event.event_start_date_current', '>', $upcomingSunday)
//         //     ->where('users.id',$arr)
//         //      ->select('users.fit_coins', 'fitme_event.user_id', 'users.email','users.name','users.device_token')
//         //       ->get(); 
//         //  foreach ($user_data as $data) {
             
//         //      echo "notificaton";
//         //      print_r($data);
//         //     if (!empty($data->device_token)) {
//         //         $response = $this->sendFirebasePush_message([$data->device_token], $notificationData);
//         //         $responses[$data->user_id] = $response;
//         //     }
//         //  }

//         // // Mail::send('winner_body', ['data' => $data], function ($message) use ($email) {
//         // //     $message->to($email)->subject('Congratulations! You are a Winner!');
//         // // });
//         // //  $user_data = DB::table('fitme_event')
//         // //     ->join('users', 'fitme_event.user_id', '=', 'users.id')
//         // //     // ->where('fitme_event.event_start_date_current', '>', $upcomingSunday)
//         // //     ->where('users.id',9833)
//         // //      ->select('users.fit_coins', 'fitme_event.user_id', 'users.email','users.name','users.device_token')
//         // //       ->get(); 
//         // //  foreach ($user_data as $data) {
//         // //     if (!empty($data->device_token)) {
//         // //         $response = $this->sendFirebasePush_message([$data->device_token], $notificationData);
//         // //         $responses[$data->user_id] = $response;
//         // //     }
//         // }
//     } else {
//         // Handle the case when no data is found
//         Log::error('No event data found');
//     }
// }

     public function send_mail_winner() {
        $currentDay = Carbon::now('Asia/Kolkata')->format('l');
        $event_data = DB::table('fitme_event')
            ->join('users', 'fitme_event.user_id', '!=', 'users.id')
            ->select('users.fit_coins', 'fitme_event.user_id', 'users.email', 'users.name')
            ->orderBy('users.fit_coins', 'desc')
            // ->where('users.id',13296)
            ->first();
     
            
           
        if ($event_data) {
            $name = $event_data->name;
            $message = $name . "has won the FitMe Challenge.";
            $title = 'Winner Winner Chicken Dinner!';
            $notificationData = [
                'message' => $message,
                'notification_id' => 'FitMe',
                'booking_id' => 'FitMe',
                'title' => $title,
                'type' => 'event_saturday',
            ];
        
            // $responses = [];
            // $email = $event_data->email;
            // $data = [
            //     'fit_coins' => $event_data->fit_coins,
            //     'user_id' => $event_data->user_id,
            //     'name' => $event_data->name ?? 'Valued User'
            // ];
        
         
            // Mail::send('winner_body', ['data' => $data], function ($message) use ($email) {
            //     $message->to($email)->subject('Congratulations! You are a Winner!');
            // });
      
              
            $user_data = DB::table('users')
                ->select('fit_coins', 'id', 'email', 'name', 'device_token')
                // ->where('id',13296)
                ->get();
                
            
        
                foreach ($user_data as $data) {
                    if (!empty($data->device_token)) {
                        $response = $this->sendFirebasePush_message([$data->device_token], $notificationData);
                        $responses[$data->id] = $response;
                    }
                }
            //   $total_winner =  DB::table('total_winner_announced')->first();
            //  if($total_winner){
            //       $winner_data = DB::table('fitme_event')
            //             ->join('users', 'fitme_event.user_id', '=', 'users.id')
            //             ->select('users.fit_coins', 'fitme_event.user_id', 'users.email', 'users.name')
            //             ->orderBy('users.fit_coins', 'desc')
            //             ->take($total_winner->winner_announced) // This will limit the results to the top 2
            //             ->get();
                    
                    
            //         foreach($winner_data as $winners){
            //             $maxWeek = DB::table('winner_history')->max('week');
            //             DB::table('winner_history')->insert([
            //                 'user_id'=>$winners->user_id,
            //                 'email'=>$winners->email,
            //                 'price'=>1000,
            //                 'week' =>$maxWeek+1,
            //                 ]);
            //         }
                 
            //  } 
        } else {
        // Log::error('No event data found for user with ID 9833');
    }
}

//  public function send_notification_saturday_winner() {
//               $currentDay = Carbon::now('Asia/Kolkata')->format('l');
//               $winner_in = DB::table('fitme_event')
//                 ->join('users', 'fitme_event.user_id', '=', 'users.id')
//                 ->select('users.fit_coins', 'fitme_event.user_id', 'users.email', 'users.name')
//                 ->where('users.country', 'India')
//                 ->orderBy('users.fit_coins', 'desc')
//                 ->first();
                
//             if ($winner_in) {
//                 $winner = DB::table('fitme_event')
//                     ->join('users', 'fitme_event.user_id', '=', 'users.id')
//                     ->select('users.fit_coins', 'fitme_event.user_id', 'users.email', 'users.name')
//                     ->orderBy('users.fit_coins', 'desc')
//                     ->first();
//                 $name = $winner->name;
//                 $message = $name . " won ₹1000 cash prize in our weekly fitness challenge! Now it's your chance to win. Join now!";
//                 $title = 'Winner Winner Chicken Dinner🏆✨';
//                 $notificationData = [
//                     'message' => $message,
//                     'notification_id' => 'FitMe',
//                     'booking_id' => 'FitMe',
//                     'title' => $title,
//                     'type' => 'event_saturday',
//                 ];
            
//                 $responses = [];
//                 $email = $winner_in->email;
//                 $data = [
//                     'fit_coins' => $winner->fit_coins,
//                     'user_id' => $winner->user_id,
//                     'name' => $winner->name ?? 'Valued User'
//                 ];

//                 Mail::send('winner_body', ['data' => $data], function ($message) use ($email) {
//                     $message->to($email)->subject('Congratulations! You are a Winner!');
//                 });
          
                  
//                 $user_data = DB::table('users')
//                     ->select('fit_coins', 'id', 'email', 'name', 'device_token')
//                     ->where('country','India')
//                     ->get();
                
            
//                     foreach ($user_data as $data) {
//                         if (!empty($data->device_token)) {
//                             $response = $this->sendFirebasePush_message([$data->device_token], $notificationData);
//                             $responses[$data->id] = $response;
//                         }
//                     }
//                   $total_winner =  DB::table('total_winner_announced')->first();
//                 //  if($total_winner){
//                 //       $winner_data = DB::table('fitme_event')
//                 //             ->join('users', 'fitme_event.user_id', '=', 'users.id')
//                 //             ->select('users.fit_coins', 'fitme_event.user_id', 'users.email', 'users.name')
//                 //             ->orderBy('users.fit_coins', 'desc')
//                 //             ->take($total_winner->winner_announced) // This will limit the results to the top 2
//                 //             ->get();
                        
                        
//                 //         foreach($winner_data as $winners){
//                 //             $maxWeek = DB::table('winner_history')->max('week');
//                 //             DB::table('winner_history')->insert([
//                 //                 'user_id'=>$winners->user_id,
//                 //                 'email'=>$winners->email,
//                 //                 'price'=>1000,
//                 //                 'week' =>$maxWeek+1,
//                 //                 ]);
//                 //         }
                     
//                 //  } 
//     }
//   }
  
//   public function send_notification_saturday_winner_us() {
//               $currentDay = Carbon::now('Asia/Kolkata')->format('l');
//               $winner_data = DB::table('fitme_event')
//                 ->join('users', 'fitme_event.user_id', '=', 'users.id')
//                 ->select('users.fit_coins', 'fitme_event.user_id', 'users.email', 'users.name','users.country')
//                 ->orderBy('users.fit_coins', 'desc')
//                 ->first();
                
//                 // dd($winner_data->country);
                
//                 if($winner_data->country = 'India'){
                    
          
//                 $winner = DB::table('fitme_event')
//                     ->join('users', 'fitme_event.user_id', '=', 'users.id')
//                     ->select('users.fit_coins', 'fitme_event.user_id', 'users.email', 'users.name')
//                     ->where('users.','')
//                     ->orderBy('users.fit_coins', 'desc')
//                     ->first();
//                 $name = $winner->name;
//                 $message = $name . " won ₹1000 cash prize in our weekly fitness challenge! Now it's your chance to win. Join now!";
//                 $title = 'Winner Winner Chicken Dinner🏆✨';
//                 $notificationData = [
//                     'message' => $message,
//                     'notification_id' => 'FitMe',
//                     'booking_id' => 'FitMe',
//                     'title' => $title,
//                     'type' => 'event_saturday',
//                 ];
            
//                 $responses = [];
//                 $email = $winner_in->email;
//                 $data = [
//                     'fit_coins' => $winner->fit_coins,
//                     'user_id' => $winner->user_id,
//                     'name' => $winner->name ?? 'Valued User'
//                 ];

//                 Mail::send('winner_body', ['data' => $data], function ($message) use ($email) {
//                     $message->to($email)->subject('Congratulations! You are a Winner!');
//                 });
          
                  
//                 $user_data = DB::table('users')
//                     ->select('fit_coins', 'id', 'email', 'name', 'device_token')
//                     ->where('country','India')
//                     ->get();
                
            
//                     foreach ($user_data as $data) {
//                         if (!empty($data->device_token)) {
//                             $response = $this->sendFirebasePush_message([$data->device_token], $notificationData);
//                             $responses[$data->id] = $response;
//                         }
//                     }
//                   $total_winner =  DB::table('total_winner_announced')->first();
//                 //  if($total_winner){
//                 //       $winner_data = DB::table('fitme_event')
//                 //             ->join('users', 'fitme_event.user_id', '=', 'users.id')
//                 //             ->select('users.fit_coins', 'fitme_event.user_id', 'users.email', 'users.name')
//                 //             ->orderBy('users.fit_coins', 'desc')
//                 //             ->take($total_winner->winner_announced) // This will limit the results to the top 2
//                 //             ->get();
                        
                        
//                 //         foreach($winner_data as $winners){
//                 //             $maxWeek = DB::table('winner_history')->max('week');
//                 //             DB::table('winner_history')->insert([
//                 //                 'user_id'=>$winners->user_id,
//                 //                 'email'=>$winners->email,
//                 //                 'price'=>1000,
//                 //                 'week' =>$maxWeek+1,
//                 //                 ]);
//                 //         }
                     
//                 //  } 
//     }
//   }

 public function sendFirebasePush_message($tokens, $data)
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

// public function exercise_points_day(Request $request)
// {
//     $user_id = $request->user_id;
//     $day = $request->day;
//     $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
//     $current_Day = $indiaTime->format('l');

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
//                     //   dd($userDayCheck);

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
//         $errorResponse = ['error' => "{$day} record does not exist for the user.", $day => null];
 
//         // Additional error messages based on specific days
//         if ($day === 'Tuesday') {
//         //   dd('test'.$day);
          
//             $mondayPoints = $exercise_points->firstWhere('user_day', 'Monday');
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
//                 $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
//                 $current_Day = $indiaTime->format('l');
        
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

//                 // Mark the deduction as applied in the database
//                 DB::table('event_exercise_completion_status')
//                     ->updateOrInsert(
//                         ['user_id' => $user_id, 'user_day' => $weekday],
//                         ['deducted' => true,'today_earning' => $deduction]
//                     );
//             }
//         }

//                   DB::table('fitcoin_history')->insert([
//                               'user_id'=>$user_id,
//                               'info'=>"day_missed",
//                               'fit_coins' =>-5,
//                               'current_day'=>$current_Day
//                               ]);
//         // Deduct fit_coins from the user's balance
//         DB::table('users')->where('id', $user_id)->decrement('fit_coins', $fit_coins_deduction);
//     }

//     return response()->json(['responses' => $response]);
// }


public function exercise_points_day(Request $request)
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
           
            
           
            $deductionsAlreadyApplied = DB::table('event_exercise_completion_status')
                ->where('user_id', $user_id)
                ->where('user_day', $pre_days)
                ->whereNotNull('deducted')
                ->exists();
    
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

  public function delete_exercise_event(Request $request){
        $user_id = $request->input('user_id');
        $current_date = $request->input('current_date');
        $workout_id = $request->input('workout_id');
        $type = $request->input('type');
           
            if ($type === "cardio") {
                $dbtable = 'cardio_exercise_complete_status';
            } else {
                $dbtable = 'event_exercise_completion_status';
            }

        $deletedRows = DB::table($dbtable)
            ->where('user_id', $user_id)
            ->whereDate('created_at', '=', $current_date) // Assuming 'created_at' is a datetime field
            ->where('workout_id', $workout_id)
            // ->where('exercise_status','undone')
            ->whereNull('final_status')
            ->delete();
        
        if ($deletedRows > 0) {
            // Records were deleted successfully
            return response()->json([
                'msg' => 'Data deleted successfully'
            ]);
        } else {
            // No records matched the delete conditions
            return response()->json([
                'msg' => 'No matching data found to delete'
            ]);
        }
        
   }
   
   
 public function all_in_one(Request $request)
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

    public function all_user_data(Request $request)
    {
    $version = $request->input('version');
    $user_id = $request->input('user_id');

    // Validate version
    $version_check = DB::table('versions')
        ->where('versions', $version)
        ->whereIn('type', ['current', 'middle', 'past'])
        ->first();
        
        if($version_check){
            
             
           if($version){
                $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
                $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
                $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
                
                
                if($versions_current){
                    
                    $free_status = true;
                      $check_free_plan = DB::table('fitme_event')->where('user_id',$user_id)->where('product_id','fitme_free')->first();
                      
                      if($check_free_plan){
                          $free_status = false;
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
                    // $currentDay = $currentDay === 0 ? 7 : $currentDay;
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
                    
                    if($details){
                        if($details->event_start_date_current==null && $details->event_start_date_upcoming ==null){
                               $today = Carbon::now();
                                $monday = $today->startOfWeek(Carbon::MONDAY);
                            
                                $mondayDate = $monday->format('Y-m-d');
                                $details->event_start_date_current = $mondayDate;
                           }
                    }else{
                        $details=[];
                    }
                
                    $detailsArray = (array) $details;
                    // $currentDay = date('w');
                    $detailsArray['currentDay'] = $currentDay;
                    $details = (object) $detailsArray;
                
                    // Fetch user profile data
                    $userData = DB::table('users')->select(
                        'id', 'goal', 'age', 'height', 'weight', 'fitness_level', 'workout_plans', 'experience', 'injury', 
                        'target_weight', 'workoutarea', 'equipment', 'focus_area', 'gender', 'name', 'email', 'image', 
                        'login_token', 'profile_compl_status', 'signup_type', 'social_type','social_id'
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
                                            'diet_image' => $this->base_url . '/images/'.$diet_data->diet_image,
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
                    
                    $details->free_status = $free_status;
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
                                
                            }
        }
        
        
        
        
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
    // $currentDay = $currentDay === 0 ? 7 : $currentDay;
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
    
    if($details){
        if($details->event_start_date_current==null && $details->event_start_date_upcoming ==null){
               $today = Carbon::now();
                $monday = $today->startOfWeek(Carbon::MONDAY);
            
                $mondayDate = $monday->format('Y-m-d');
                $details->event_start_date_current = $mondayDate;
           }
    }else{
        $details=[];
    }

    $detailsArray = (array) $details;
    // $currentDay = date('w');
    $detailsArray['currentDay'] = $currentDay;
    $details = (object) $detailsArray;

    // Fetch user profile data
    $userData = DB::table('users')->select(
        'id', 'goal', 'age', 'height', 'weight', 'fitness_level', 'workout_plans', 'experience', 'injury', 
        'target_weight', 'workoutarea', 'equipment', 'focus_area', 'gender', 'name', 'email', 'image', 
        'login_token', 'profile_compl_status', 'signup_type', 'social_type','social_id'
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
                            'diet_image' => $this->base_url . '/images/'.$diet_data->diet_image,
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

    
    
    public function all_user_with_condition(Request $request)
    {
        $version = $request->input('version');
        $user_id = $request->input('user_id');
    
        // Validate version
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
    
        if (!$versions_current && !$versions_middle && !$versions_past) {
            return response()->json(['msg' => 'Please update the app to the latest version.']);
        }
        
                $daysdata = DB::table('challenges')
                      ->get();
      
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
                                        'video' =>$this->base_url . "/images/" . $exercisedata->video,
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
                                        'video' => $this->base_url . "/images/" . $exercisedata->video,
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
    public function send_push_notification_monday(){
           
        //   $currentDay = Carbon::now()->format('l');
        //     if($currentDay!=='Monday'){
        //      return response()->json(['msg' => 'Notifications can only be sent on monday']);
        //      }
       $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        // echo $indiaTime->format('Y-m-d');
        

            $event_data = DB::table('fitme_event')
                ->join('users', 'fitme_event.user_id', '=', 'users.id')
                ->whereDate('fitme_event.event_start_date_current', '=', $indiaTime->format('Y-m-d'))
                // ->where('users.country','India')
                ->select('users.fit_coins', 'fitme_event.user_id', 'users.email', 'users.name', 'users.device_token')
                ->get();
                
           $message = 'Aapka fitness event start ho chuka hai, Start your workout or you will miss a chance to earn your cash reward! 💰💸';
           $title = 'Nazar hati Durghatna ghati! 🤑';

            $notificationData = [
                'message' => $message,
                'notification_id' => 'FitMe',
                'booking_id' => 'FitMe',
                'title' => $title,
                 'type' => 'event_monday',
            ];
        
            $responses = [];

            if ($event_data) {
                 foreach ($event_data as $data) {
                    if (!empty($data->device_token)) {
                        // dd($data->device_token);
                        $response = $this->sendFirebasePush_monday([$data->device_token], $notificationData);
                        $responses[$data->user_id] = $response;
                    }
                }
            } else {
                Log::error('No event data found');
            }
     }
     
     
     public function send_push_notification_monday_us(){
           
        //   $currentDay = Carbon::now()->format('l');
        //     if($currentDay!=='Monday'){
        //      return response()->json(['msg' => 'Notifications can only be sent on monday']);
        //      }
        $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        // echo $indiaTime->format('Y-m-d');
        

            $event_data = DB::table('fitme_event')
                ->join('users', 'fitme_event.user_id', '=', 'users.id')
                ->whereDate('fitme_event.event_start_date_current', '=', $indiaTime->format('Y-m-d'))
                // ->where('users.country','United States')
                ->select('users.fit_coins', 'fitme_event.user_id', 'users.email', 'users.name', 'users.device_token')
                ->get();
           $message = 'Aapka fitness event start ho chuka hai, Start your workout or you will miss a chance to earn your cash reward! 💰💸';
           $title = 'Nazar hati Durghatna ghati! 🤑';

            $notificationData = [
                'message' => $message,
                'notification_id' => 'FitMe',
                'booking_id' => 'FitMe',
                'title' => $title,
                 'type' => 'event_monday',
            ];
        
            $responses = [];

            if ($event_data) {
                 foreach ($event_data as $data) {
                    if (!empty($data->device_token)) {
                        // dd($data->device_token);
                        $response = $this->sendFirebasePush_monday([$data->device_token], $notificationData);
                        $responses[$data->user_id] = $response;
                    }
                }
            } else {
                Log::error('No event data found');
            }
     }
     public function sendFirebasePush_monday($tokens, $data)
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
             'type' => $data['type']
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
    public function generat_code(Request $request){
        $name = $request->name;
        $user_id = $request->user_id;
        $get_user_data = DB::table('users')->where('id',$user_id)->first();
        if(!$get_user_data){
             return response()->json([
            'msg' => 'user not found'
        ]);
            
        }
        $check_code =DB::table('referral_code')->where('user_id',$user_id)->first();
       if($check_code){
            if($check_code->referral_code){
                 return response()->json([
                'msg' => 'referral code alrady generated',
                'code' =>$check_code->referral_code,
                'link' =>$this->base_url . '/adserver/public/api/download_url',
            ]);
                
            }
       }
        $name = $get_user_data->name;
        $id = $get_user_data->id;
        $words = explode(' ', $name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= substr($word, 0, 2);
            break;
        }
        
         $lover_case = strtoupper($initials);
         $randomNumber = rand(10000, 99999);
         $code =$lover_case.'FIT'.$id;
         DB::table('referral_code')->insert([
             'user_id'=>$user_id,
             'referral_code'=>$code,
          ]);
           return response()->json([
            'msg' => 'referral code genrated',
            'code' =>$code,
            'link' =>$this->base_url . '/adserver/public/api/download_url',
        ]);

    }
    public function add_referral_coin(Request $request)
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
        if(!$check_current_Status){
             return response()->json([
                    'msg' => 'Invalid referral code'
            ]);
            
        }
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
    public function create_custom_diet(Request $request){
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
   public function dawnload_url(Request $request){
      
      
        $now = Carbon::now();
        $formattedDate = $now->format('Y-m-d');
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        // dd($userAgent);
        if (strpos($userAgent, 'Android') !== false) {
           DB::table('download_tracking')->insert([
               'device'=>'Android',
               'date'=>$formattedDate
               ]);
            return redirect()->away('https://play.google.com/store/apps/details?id=fitme.health.fitness.homeworkouts.equipment');
        } elseif (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false || strpos($userAgent, 'iPod') !== false) {
            
            DB::table('download_tracking')->insert([
               'device'=>'iPhone',
               'date'=>$formattedDate
               ]);
             
            return redirect()->away('https://apps.apple.com/in/app/lose-weight-at-home-in-15-days/id6470018217');
        } else {
        
        }
    
   }
    
    public function event(Request $request)
    {
    // Retrieve input data from the request
    $user_id = $request->input('user_id');
    $plan_amount = $request->input('plan');
    $plan_value = $request->input('plan_value');
    $transaction_id = $request->input('transaction_id');
    $platform = $request->input('platform');
    $product_id = $request->input('product_id');
    $used = 1;
    
    $version = $request->input('version'); 
    $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
    $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
    $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        
    if($versions_current){
        
    $allow_usage = 0;
    if ($plan_amount == 'noob') {
        $allow_usage = 1;
    } elseif ($plan_amount == 'pro') {
        $allow_usage = 2;
    } elseif ($plan_amount == 'premium') {
        $allow_usage = 3;
    }
    elseif ($plan_amount == 'free') {
        $allow_usage = 1;
    }
    
    if($plan_amount == 'free'){
        
        $checkFree_plan = DB::table('fitme_event')->where('user_id',$user_id)->where('product_id','fitme_free')->first();
        if($checkFree_plan){
            
            $free_status = false;
            
            return response()->json([
                'message' => 'Event created successfully',
                'hasFreeSubscription' =>$free_status,
            ]);
        }else{
            $free_status = true;
        }
        
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
                }elseif($existing_subscription->plan == 'free'){
                    
                    if ($plan_amount == 'premium') {
                        $allow_usage = 3;
                    } elseif ($plan_amount == 'pro') {
                        $allow_usage = 2;
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

                return response()->json([
                    'message' => 'Plan upgraded and existing subscription updated successfully',
                    'hasFreeSubscription' =>$free_status
                     ]);
            } else {
                // Complete the current subscription
                DB::table('fitme_event')
                    ->where('id', $existing_subscription->id)
                    ->update(['plan_status' => 'completed']);
                return response()->json([
                    'message' => 'You have reached the maximum usage for your current subscription. Please upgrade to a higher plan.',
                     'hasFreeSubscription' =>$free_status
                     ]);
            }
        } else {
            // Update the used_plan count
            DB::table('fitme_event')
                ->where('id', $existing_subscription->id)
                ->update(['used_plan' => $existing_subscription->used_plan + 1, 'event_start_date_upcoming' => $start_date, 'upcoming_day_status' => 1]);

            return response()->json([
                'message' => 'Subscription usage updated successfully',
                'hasFreeSubscription' =>$free_status,
            ]);
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
                    if ($currentDay != 'Saturday' && $currentDay != 'Sunday') {
                        //   return response()->json([
                        //     'msg' => 'you can only use this referral code between mon to friday.'
                        // ]);
                        
                       $check_current_status = DB::table('fitme_event')->where('user_id',$code_gen->user_id)->where('current_day_status',1)->first();
                       if($check_current_status){
                           
                           
                       
                        
                        if (!$check_event_register) {
                              $message = 'Credited extra fitcoins to you as  your friend join event using your code.';
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
                            $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
                            $current_Day = $indiaTime->format('l');
                         DB::table('fitcoin_history')->insert([       
                                'fit_coins' =>3,
                                'user_id' =>$code_gen->user_id,
                                'info' => 'event_registerd',
                                'current_day' =>$current_Day
                            ]);
                            
                         DB::table('all_history')->insert([
                                'user_id' =>$code_gen->user_id,
                                'event_register' =>'done',
                                'day'=> $current_Day,
                                'fit_coins'  =>3
                                ]);
                            
                        DB::table('referral_code')->insert([
                                'event_register_status' => 'used',
                                'used_by' => $user_id,
                                'referral_code' =>$add_coin_to->referral_code,
                                'device_id'=> $add_coin_to->device_id,
                            ]);
                        }
                    }    
                  }
               }
            
    
        return response()->json([
            'message' => 'Event created successfully',
             'hasFreeSubscription' => $free_status
             ]);
    }
        
    }else{
        
    
  
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
                    if ($currentDay != 'Saturday' && $currentDay != 'Sunday') {
                        //   return response()->json([
                        //     'msg' => 'you can only use this referral code between mon to friday.'
                        // ]);
                        
                       $check_current_status = DB::table('fitme_event')->where('user_id',$code_gen->user_id)->where('current_day_status',1)->first();
                       if($check_current_status){
                           
                           
                       
                        
                        if (!$check_event_register) {
                              $message = 'Credited extra fitcoins to you as  your friend join event using your code.';
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
                            $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
                            $current_Day = $indiaTime->format('l');
                         DB::table('fitcoin_history')->insert([       
                                'fit_coins' =>3,
                                'user_id' =>$code_gen->user_id,
                                'info' => 'event_registerd',
                                'current_day' =>$current_Day
                            ]);
                            
                         DB::table('all_history')->insert([
                                'user_id' =>$code_gen->user_id,
                                'event_register' =>'done',
                                'day'=> $current_Day,
                                'fit_coins'  =>3
                                ]);
                            
                        DB::table('referral_code')->insert([
                                'event_register_status' => 'used',
                                'used_by' => $user_id,
                                'referral_code' =>$add_coin_to->referral_code,
                                'device_id'=> $add_coin_to->device_id,
                            ]);
                        }
                    }    
                  }
               }
            
    
        return response()->json(['message' => 'Event created successfully'], 201);
    }
  }    

}
 

  public function breathinout_us(Request $request)
{
       $usTime = \Carbon\Carbon::now()->setTimezone('America/New_York'); // You can change this to your specific US timezone
        $currentHour = $usTime->format('H:i');
        
        // Initialize default message and title
        $message = "Breath in session start";
        $title = "Start";
    
        // Define session times
        $sessions = [
            ['start' => '06:00', 'end' => '07:00'],
            ['start' => '11:00', 'end' => '12:00'],
            ['start' => '16:00', 'end' => '17:00'],
            ['start' => '21:00', 'end' => '22:00']
        ];
    
        // Check if current time falls within any session
        $session = false;
        foreach ($sessions as $time) {
            if ($currentHour >= $time['start'] && $currentHour < $time['end']) {
                $session = true;
                break;
            }
        }
    
        if (!$session) {
            $message = "Breath in session close";
            $title = "Close";
        }
    
        // Prepare notification data
        $notificationData = [
            'message' => $message,
            'notification_id' => 'FitMe',
            'booking_id' => 'FitMe',
            'title' => $title,
            'breathin_session' => $session
        ];
    
        // Fetch data from the database
        $data = DB::table('fitme_event')
            ->join('users', 'users.id', '=', 'fitme_event.user_id')
        //   ->where('users.country', 'United States')
            ->where('fitme_event.current_day_status', 1)
            ->get();
      
        // Send notifications
        $responses = [];
        foreach ($data as $event) {
            if (!empty($event->device_token)) {
                try {
                    // Send notification
                    $response = $this->sendFirebasePushBrithin([$event->device_token], $notificationData);
                    $responses[$event->id] = $response;
                } catch (\Exception $e) {
                    // Handle error
                    $responses[$event->id] = ['error' => $e->getMessage()];
                }
            }
        }
    
        // Return the responses for debugging or logging
        return response()->json([
            'status' => 'success',
            'responses' => $responses
        ]);
    }
    
     public function breathinout(Request $request)
{

    $indiaTime = \Carbon\Carbon::now()->setTimezone('Asia/Kolkata');
    $currentHour = $indiaTime->format('H:i');
    

    $message = "Join Now to Earn Bonus Coins";
    $title = "Breathing Session is Now Live!";

    // Define session times
    $sessions = [
        ['start' => '06:00', 'end' => '07:00'],
        ['start' => '11:00', 'end' => '12:00'],
        ['start' => '16:00', 'end' => '17:00'],
        ['start' => '21:00', 'end' => '22:00']
    ];

    // Check if current time falls within any session
    $session = false;
    foreach ($sessions as $time) {
        if ($currentHour >= $time['start'] && $currentHour < $time['end']) {
            $session = true;
            break;
        }
    }

    if (!$session) {
        $message = "Be Ready to Earn More Coins in Next Session!";
        $title = "Breathing Session is Over!";
    }

    // Prepare notification data
    $notificationData = [
        'message' => $message,
        'notification_id' => 'FitMe',
        'booking_id' => 'FitMe',
        'title' => $title,
        'breathin_session' => $session
    ];

    // Fetch data from the database
    $data = DB::table('fitme_event')
        ->join('users', 'users.id', '=', 'fitme_event.user_id')
        // ->where('users.country', 'India')
        // ->where('users.id', '13190') 
        ->where('fitme_event.current_day_status', 1)
        ->get();
        
        
        //  $data = DB::table('users')
        // // ->join('users', 'users.id', '=', 'fitme_event.user_id')
        // // // ->where('users.country', 'India')
        // ->where('id',13296) 
        // // ->where('fitme_event.current_day_status', 1)
        // ->get();

    // Send notifications
    $responses = [];
    foreach ($data as $event) {
        
        
        if (!empty($event->device_token)) {
            try {
                // Send notification
           
                $response = $this->sendFirebasePush([$event->device_token], $notificationData);
                $responses[$event->id] = $response;
                // $response = $this->sendFirebasePush([$data->device_token], $notificationData);
                // $responses[$data->id] = $response;
            } catch (\Exception $e) {
                // Handle error
                $responses[$event->id] = ['error' => $e->getMessage()];
            }
        }
    }

    // Return the responses for debugging or logging
    return response()->json([
        'status' => 'success',
        'responses' => $responses
    ]);
}



public function sendFirebasePushBrithin($tokens, $data)
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

    
    public function monday_to_friday_notification(Request $request){
        
        dd('monday');
         // Get the upcoming Sunday date
        $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        $currentDay = $indiaTime->format('l');
    
        
        $message = 'Aaj ka workout 50 FC ke liye shuru ho gaya hai. Streak miss mat karo aur abhi FC jeeto!';
        $title = 'Aaj Ka Workout Shuru Ho Gaya Hai!';
        
        // Fetch data for the user with user_id = 111 where event_start_date_current is after upcoming Sunday
        $event_data = DB::table('fitme_event')
            ->join('users', 'fitme_event.user_id', '=', 'users.id')
            // ->where('users.id', 9833)
            ->select('users.device_token', 'fitme_event.*')
            ->get();
            // print_r($event_data);
            // die();
           
        // Define notification data
        $notificationData = [
            'message' => $message,
            'notification_id' => 'FitMe',
            'booking_id' => 'FitMe',
            'title' => $title,
            'image' =>'https://res.cloudinary.com/drfp9prvm/image/upload/v1721102775/DALL_E_2024-07-16_09.36.00_-_A_vibrant_and_eye-catching_banner_design_for_a_fitness_app_to_collect_50_FC_coins_and_win_a_cash_prize._The_banner_should_include_the_text_in_Hinglish_mbxfq8.webp'
        ];
    
        $responses = [];
        foreach ($event_data as $data) {
            if (!empty($data->device_token)) {
                    $response = $this->sendFirebasePush([$data->device_token], $notificationData);
                    $responses[$data->id] = $response;
            }
        }
        return response()->json(['message' => 'Notifications sent successfully!', 'responses' => $responses]);
    }
    
    public function coin_deduction_rec(Request $request){
        $user_id = $request->user_id;
        $get_coins = $request->coins;
        $day = $request->day;
        $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        $currentDay = $indiaTime->format('l');
        
        
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            if (!in_array($day, $weekdays)) {
                return response()->json([
                    'msg' => 'please enter valid day'
                ]);
              
            } 
            if (!$get_coins) {
                return response()->json([
                    'msg' => 'coin is required'
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
            
            
    
        // if ($check_data) {
          $check_monday_data = DB::table('event_exercise_completion_status')
                ->where('user_id', $user_id)
                ->where('user_day',$day)
                ->first();
            
                $monday=[];
            // if($check_monday_data){
                
                 $skip_status_monday = DB::table('event_exercise_completion_status')
                ->where('user_id', $user_id)
                ->where('user_day', $day)
                ->where('exercise_status', 'completed')
                ->whereNotNull('skip_status')
                ->sum('skip_status');
                
                $monday['skip_status']= $skip_status_monday;
                
                // dd($skip_status_monday);
         
    
            $prev_status_monday = DB::table('event_exercise_completion_status')
                ->where('user_id', $user_id)
                ->where('user_day', $day)
                ->where('exercise_status', 'completed')
                ->whereNotNull('prev_status')
                ->sum('prev_status');
                
                $monday['prev_status']= $prev_status_monday;
    
                $next_status_monday = DB::table('event_exercise_completion_status')
                ->where('user_id', $user_id)
                ->where('user_day', $day)
                ->where('exercise_status', 'completed')
                ->whereNotNull('next_status')
                ->sum('next_status');
                $monday['next_status']= $next_status_monday;
                
                
                $cardio_data = DB::table('cardio_exercise_complete_status')
                    ->where('user_id', $user_id)
                     ->first();
                     
                 $cardio_coins= $cardio_data->fit_coins;
                
                $cardio_status_monday = DB::table('cardio_exercise_complete_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $day)
                    ->where('final_status', 'allcompleted')
                    // ->where('cardio_status','done')
                     ->first();
                     
                    //  dd($cardio_status_monday);
                     
                     $total_cardio_points =0;
                    if($cardio_status_monday){
                        // 
                          $cardio = [];
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
                     $monday['cardio']= $total_cardio_points;
    
                    
                $countSessionA = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_a', 'done')->count();
                $countSessionB = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_b', 'done')->count();
                $countSessionC = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_c', 'done')->count();
                $countSessionD = DB::table('breathin_session')->where('user_id',$user_id)->where('day', $day)->where('session_d', 'done')->count();
                
                $total_coins= ($countSessionA+$countSessionB+$countSessionC+$countSessionD)*$get_coins;
                $monday['breath_in']=0;
                if($countSessionA || $countSessionB || $countSessionC || $countSessionD){
                      $total_coins= ($countSessionA+$countSessionB+$countSessionC+$countSessionD)*$get_coins;
                     $monday['breath_in']=$total_coins;
                }
              
              
              
               $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    
                $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
                $currentDay = $indiaTime->format('l');
                $currentDayIndex = array_search($currentDay, $weekdays);
           
                $existingDays = DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->pluck('user_day')
                    ->toArray();
                
                $missedDays = [];
                for ($i = 0; $i < $currentDayIndex; $i++) {
                    $dayToCheck = $weekdays[$i];
                    if (!in_array($dayToCheck, $existingDays)) {
                        $missedDays[] = $dayToCheck;
                    }
                }
                
                $point_deduction =[];
                // point deduction 
                
                   $skip_status_monday = DB::table('event_exercise_completion_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $day)
                    ->where('exercise_status', 'completed')
                    ->whereNotNull('skip_status')
                    ->sum('skip_status');
                    
                    $point_deduction['skip_status']= $skip_status_monday;
                    
                    // dd($skip_status_monday);
             
        
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
                    
                    
                    
                    
                    
                    // cardio 
                     $cardio =[];
                    
                    
                   $cardio_status_monday = DB::table('cardio_exercise_complete_status')
                    ->where('user_id', $user_id)
                    ->where('user_day', $day)
                    // ->where('final_status', 'allcompleted')
                    // ->where('cardio_status','done')
                     ->first();
                     
                    //  dd($cardio_status_monday);
                    
                     
                     $total_cardio_points =0;
                    if($cardio_status_monday){
                        // 

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
                    
                   
                    
                    
                    
                     
                    

         $register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','referral_registerd')->count();
         $event_register_data = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','event_registerd')->count();
         $late = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','late')->count();
         $day_missed = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','day_missed')->count();
         $custom_wokrout_coins = DB::table('fitcoin_history')->where('user_id',$user_id)->where('info','create_custom_wokrout')->count();
         $point_deduction['delay']= $late;
         $register_object = (object) $register_data;
         $response = [
            $day => $monday,
            'overview'=>$point_deduction,
            'workout'=>$cardio,
            // 'tuesday' => $tuesday,
            // 'wednesday' => $wednesday,
            // 'thursday' => $thursday,
            // 'friday' => $friday,
            'total_registerd' => $register_data,
            'total_event_registerd'=>$event_register_data,
            'late_exercises'=>$late,
            'custom_wokrout_coins' =>$custom_wokrout_coins,
            // 'missing_days_count'=>$missingDaysCount,
            'missing_days'=>$missedDays,
        ];

        return response()->json($response);
    // }

    // return response()->json([
    //     'msg' => 'no data found'
    // ]);
 }
  public function cardio_status(Request $request){
      
        $user_id = $request->input('user_id');
        if(empty($user_id)){
            return response()->json([
                    'msg' => 'user id is required'
                 ]);
        }
        $data = DB::table('cardio_exercise_complete_status')
                    ->where('user_id', $user_id)
                    ->first();
        if(!$data){
             return response()->json([
                'msg' => 'data not found'
            ]);
        }
            
        $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        $currentDay = $indiaTime->format('l');
        $check_status = DB::table('cardio_exercise_complete_status')
            ->where('user_id', $user_id)
            ->where('user_day', $currentDay)
            ->whereNotNull('today_earning')
            ->first();
            
        if($check_status){
            $status = true;
        }else{
             $status = false;
        }
       
        return response()->json([
            'status' => $status
        ]);
    }
      public function breathinout_status(Request $request) {
            $user_id = $request->input('user_id');
            $status = $request->input('status');
        
            if (empty($user_id)) {
                return response()->json([
                    'msg' => 'User ID is required'
                ]);
            }
        
            if (empty($status) || $status !== "done") {
                return response()->json([
                    'msg' => 'Status must be "done"'
                ]);
            }
        
            $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
            $currentHour24 = $indiaTime->format('H:i');
            $currentDay = $indiaTime->format('l');
        
            $sessions = [
                ['start' => '5:59AM', 'end' => '5:59AM', 'message' => 'Breath in session start', 'title' => 'A'],
                ['start' => '10:59AM', 'end' =>'11:59AM', 'message' =>'Breath in session start', 'title' => 'B'],
                ['start' => '3:59PM', 'end' => '4:59PM', 'message' => 'Breath in session start', 'title' => 'C'],
                ['start' => '8:59PM', 'end' => '9:59PM', 'message' => 'Breath in session start', 'title' => 'D']
            ];
        
            function convertTo24Hour($time) {
                return Carbon::createFromFormat('g:iA', $time)->format('H:i');
            }
        
            $sessionOpen = false;
            foreach ($sessions as $session) {
                $sessionStart = convertTo24Hour($session['start']);
                $sessionEnd = convertTo24Hour($session['end']);
        
                if ($currentHour24 >= $sessionStart && $currentHour24 < $sessionEnd) {
                    $sessionOpen = true;
                    $lowercaseTitle = Str::lower($session['title']);
                    $sessionRecord = DB::table('breathin_session')
                        ->where('user_id', $user_id)
                        ->where('day', $currentDay)
                        // ->whereNull('session_' . $lowercaseTitle)
                        ->first();
                    
        
                    if ($sessionRecord) {
                        
                        if ($sessionRecord->{'session_' . $lowercaseTitle} !== 'done') {
                            DB::table('breathin_session')
                                ->where('user_id', $user_id)
                                ->where('day', $currentDay)
                                ->update([
                                    'session_' . $lowercaseTitle => 'done'
                                ]);
                                
        
                            DB::table('users')->where('id', $user_id)->increment('fit_coins', 1);
                            
                            DB::table('all_history')->insert([
                                    'user_id' =>$user_id,
                                    'breathinout_status' =>'session_' . $lowercaseTitle,
                                    'day'=> $currentDay,
                                    'fit_coins'  =>1
                                    ]);
        
                            return response()->json([
                                'msg' => 'Coin added successfully'
                            ]);
                        } else {
                            return response()->json([
                                'msg' => 'Session already marked as done'
                            ]);
                        }
                    } else {
                        DB::table('breathin_session')->insert([
                            'user_id' => $user_id,
                            'day' => $currentDay,
                            'session_' . $lowercaseTitle => 'done'
                        ]);
                          DB::table('users')->where('id', $user_id)->increment('fit_coins', 1);
                          
                          DB::table('all_history')->insert([
                                    'user_id' =>$user_id,
                                    'breathinout_status' =>'session_' . $lowercaseTitle,
                                    'day'=> $currentDay,
                                    'fit_coins'  =>1
                                    ]);
        
                        return response()->json([
                           'msg' => 'Coin added successfully'
                        ]);
                    }
                }
            }
        
            if (!$sessionOpen) {
                return response()->json([
                    'msg' => 'No active sessions available'
                ]);
            }
        }
       public function add_cardio_coins(Request $request)
        {
            $user_id = $request->input('user_id');
            $status = $request->input('status');
            $current_date = Carbon::today()->toDateString();
            $current_day = Carbon::now()->setTimezone('Asia/Kolkata')->format('l');
            $check_status = DB::table('cardio_session')
                ->where('user_id', $user_id)
                ->where('day', $current_day)
                ->whereDate('created_at', $current_date)
                ->first();
        
            if(!empty($check_status)){
                   return response()->json([
                    'msg' => 'cardio session allrady completed'
                ]);
            }else{
        
                    $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
                    $current_Day = $indiaTime->format('l');
                
                  DB::table('cardio_session')->insert([
                      'user_id' =>$user_id,
                      'day' => $current_day,
                      'cardio_status'=>'done'
                      ]);
                      $total_coin_add =5;
                    DB::table('fitcoin_history')->insert([
                      'user_id' =>$user_id,
                      'info' => 'cardio_coins',
                      'current_Day' =>$current_Day,
                      'fit_coins' =>'+'.$total_coin_add,
                      
                      ]);
                  
                  DB::table('users')->where('id',$user_id)->update([
                        'fit_coins' => DB::raw('fit_coins + ' . $total_coin_add),
                        ]);
                        
                        return response()->json([
                    'msg' => 'coin added successfully'
                ]);
            }
    
        }
    public function get_breathinout_session(Request $request)
    {

        $user_id = $request->input('user_id');
        
        // if(!empty($user_id )){
        //     // dd("adfasf");
        //      $check_country  = DB::table('users')->where('id',$user_id)->first();  //country
        //     $timezone = 'UTC'; 
        //     if ($check_country->country === 'India') {
        //         $timezone = 'Asia/Kolkata';
        //     } elseif ($check_country->country === 'United States') {
        //         $timezone = 'America/New_York';
        //     }
        // }
       
        $indiaTime = Carbon::now()->setTimezone('Asia/Kolkata');
        $currentHour = $indiaTime->format('g:i a');
        $currentHour24 = $indiaTime->format('H:i');
        $current_day = $indiaTime->format('l');
    
  
        $sessions = [
            ['start' => '6:00AM', 'end' => '7:00AM', 'message' => 'Breath in session start', 'title' => 'A'],
            ['start' => '11:00AM', 'end' => '12:00PM', 'message' => 'Breath in session start', 'title' => 'B'],
            ['start' => '4:00PM', 'end' => '5:00PM', 'message' => 'Breath in session start', 'title' => 'C'],
            ['start' => '9:00PM', 'end' => '10:00PM', 'message' => 'Breath in session start', 'title' => 'D'],
          
        ];
    
       
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

public function app_crash_rec(Request $request)
{
      
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
              
                    print_r($user_data);
  
       }
      
    }
    public function past_winners(Request $request)
    {
        
         $version = $request->input('version');
         
         if(!$version){
              return response()->json([
                'msg' => 'version is required'
            ]);
         }
        
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        if ($versions_current) {
        
            $data = DB::table('winner_history')
                ->join('users', 'users.id', '=', 'winner_history.user_id')
                ->select('users.name', 'users.fit_coins', 'users.id', 'users.image', 'winner_history.price', 'winner_history.week')
                ->orderBy('winner_history.week', 'desc')
                ->get();
                
                $winner_data=[];
            if($data){
             
              foreach ($data as $winners)
              {
                    // Determine the week suffix
                    if ($winners->week == 1) 
                    {
                        $week_suffix = "st";
                    } elseif ($winners->week == 2)
                    {
                        $week_suffix = "nd";
                    } elseif ($winners->week == 3)
                    {
                        $week_suffix = "rd";
                    } else
                    {
                        $week_suffix = "th";
                    }
                  if (!isset($winners->image) || empty($winners->image)) {
                        $image_link = null;
                    } else {
                        $image_link =$this->base_url . "/adserver/public/profile_image/" . $winners->image;
                    }
                    
                    
                    $winner_data[] = [
                        'name' => $winners->name,
                        'fit_coins' => $winners->fit_coins,
                        'user_id' => $winners->id,
                        'image' => $image_link,
                        'price' => $winners->price,
                        'week' => $winners->week . $week_suffix,
                    ];
            }
    
                  return response()->json([
                        'data' => $winner_data
                    ]);
            }
            return response()->json([
                        'msg' => "no data found"
                    ]);
                    
            }       
        if ($versions_middle) {
        
             $data = DB::table('winner_history')
                ->join('users', 'users.id', '=', 'winner_history.user_id')
                ->select('users.name', 'users.fit_coins', 'users.id', 'users.image', 'winner_history.price', 'winner_history.week')
                ->orderBy('winner_history.week', 'desc')
                ->get();
                
                $winner_data=[];
            if($data){
             
              foreach ($data as $winners)
              {
                    // Determine the week suffix
                    if ($winners->week == 1) 
                    {
                        $week_suffix = "st";
                    } elseif ($winners->week == 2)
                    {
                        $week_suffix = "nd";
                    } elseif ($winners->week == 3)
                    {
                        $week_suffix = "rd";
                    } else
                    {
                        $week_suffix = "th";
                    }
                    
                    
                    
                   if (!isset($winners->image) || empty($winners->image)) {
                        $image_link = null;
                    } else {
                        $image_link = $this->base_url . "/adserver/public/profile_image/" . $winners->image;
                    }
                    
                    
                    $winner_data[] = [
                        'name' => $winners->name,
                        'fit_coins' => $winners->fit_coins,
                        'user_id' => $winners->id,
                        'image' => $image_link,
                        'price' => $winners->price,
                        'week' => $winners->week . $week_suffix,
                    ];
            }
    
                  return response()->json([
                        'data' => $winner_data
                    ]);
            }
            return response()->json([
                        'msg' => "no data found"
                    ]);
                    
                    
            }
        if ($versions_past)
        {
        
            $data = DB::table('winner_history')
                ->join('users', 'users.id', '=', 'winner_history.user_id')
                ->select('users.name', 'users.fit_coins', 'users.id', 'users.image', 'winner_history.price', 'winner_history.week')
                ->orderBy('winner_history.week', 'desc')
                ->get();
                
                $winner_data=[];
            if($data){
             
              foreach ($data as $winners)
              {
                    // Determine the week suffix
                    if ($winners->week == 1) 
                    {
                        $week_suffix = "st";
                    } elseif ($winners->week == 2)
                    {
                        $week_suffix = "nd";
                    } elseif ($winners->week == 3)
                    {
                        $week_suffix = "rd";
                    } else
                    {
                        $week_suffix = "th";
                    }
                    
                    $winner_data[] = [
                        'name' => $winners->name,
                        'fit_coins' => $winners->fit_coins,
                        'user_id' => $winners->id,
                        'image' => $this->base_url . "/adserver/public/profile_image/".$winners->image,
                        'price' => $winners->price,
                        'week' => $winners->week . $week_suffix,
                    ];
            }
    
                  return response()->json([
                        'data' => $winner_data
                    ]);
            }
          
                    
       }
        return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
    }


public function cardio_exercise_status(Request $request){
         
         $version = $request->input('version');
        $user_id = $request->input('user_id');
        $day = $request->input('day');
    
        if(!$version){
            return response()->json([
                'msg' => 'version is required'
            ]);
        }
    
        // Check if the version is valid
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        if (!$versions_current && !$versions_middle && !$versions_past) {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'
            ]);
        }
        
         // ture = complete 
         //false =  incomplete
  
        $check_user = DB::table('cardio_exercise_complete_status')->where('user_id', $user_id) ->where('user_day', $day)->first();
        if (!$check_user) {
            return response()->json([
                'status' => false
            ]);
        }
    
    
        $check_status_undone = DB::table('cardio_exercise_complete_status')
            ->where('user_id', $user_id)
            ->where('user_day', $day)
            ->where('exercise_status', 'undone')
            ->exists();
    
        if ($check_status_undone) {
            return response()->json([
                'status' => false
            ]);
        }
    
        $all_status_completed = DB::table('cardio_exercise_complete_status')
            ->where('user_id', $user_id)
            ->where('user_day', $day)
            ->where('exercise_status', 'completed')
            ->count();

        $total_records = DB::table('cardio_exercise_complete_status')
            ->where('user_id', $user_id)
            ->where('user_day', $day)
            ->count();
    
        if ($total_records > 0 && $total_records === $all_status_completed) {
            return response()->json([
                'status' => true
            ]);
        }
    
        return response()->json([
            'status' => false
        ]);
     
   }
   
   public function delete_weekly_data(Request $request){
       
    //   DB::table('event_exercise_completion_status')->truncate();
    //   DB::table('cardio_exercise_complete_status')->truncate();
    //   DB::table('breathin_session')->truncate();
           DB::table('users')->update([
                   'weekly_custom_workout_status' =>0
               ]);
       
       
       
   }
   
}






