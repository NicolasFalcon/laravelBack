<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class User_deleteController extends Controller
{

    protected $base_url;

    public function __construct()
    {
        $this->base_url = url('/'); // or config('app.url')
    }
       public function user_delete(Request $request)
    {
      
        $token = request('login_token');
        $email = request('email');
        $sqlquery = DB::table('users')->where('email', $email)->first();
        $dbtoken = $sqlquery->login_token;
        if ($token === $dbtoken) {
        $email = $request->get('email');
        $user = DB::table('users')->where('email', $email)->first();
        if ($user) {
            $deletedRows = DB::table('users')->where('email', $email)->delete();
            $msg[] = [
                'msg' => "Your account has been deleted successfully"
            ];
            echo json_encode($msg, JSON_NUMERIC_CHECK);
        } else {
            $msg[] = [
                'msg' => "User not found"
            ];
            echo json_encode($msg, JSON_NUMERIC_CHECK);
        }
    }else{
        $msg[] = [
            'msg' => "invalid token"
        ];
        echo json_encode($msg, JSON_NUMERIC_CHECK);
        
    }
    }
    public function removefavorite(Request $request)
    {
        $temp = request('temp');

        if ($temp == 1) {

            $email = request('email');
            $diet_id = request('diet_id');

            $deletedRows = DB::table('favorite')
                ->where('email', $email)
                ->where('diet_id', $diet_id)
                ->delete();

            if ($deletedRows > 0) {
                $msg = "Diet removed from favourites";
                $response = [
                    'msg' => $msg,
                ];
                return response()->json($response);
            } else {
                return response()->json(['msg' => 'Error: Diet not found in favourites']);
            }
        } elseif ($temp == 2) {
            $email = request('email');
            $workout_id = request('workout_id');

            $deletedRows = DB::table('favorite')
                ->where('email', $email)
                ->where('workout_id', $workout_id)
                ->delete();

            if ($deletedRows > 0) {
                $msg = "Workout removed from favourites";
                $response = [
                    'msg' => $msg,
                ];
                return response()->json($response);
            } else {
                return response()->json(['msg' => 'Error: Workout not found in favourites']);
            }
        } else {
            return response()->json(['msg' => 'Error']);
        }
    }
    public function resendotp()
    {
        $email = request('email');

        $otpTime = now();
        $status = 0;
        $randomNumber = rand(1000, 9999);
        $newtime = time();
        DB::table('users')
            ->where('email', $email)
            ->update([
                'otp_token' => $randomNumber,
                'otp_time' => $otpTime,
            ]);
        $data = [
            'email' => $email,
            'randomNumber' => $randomNumber,
        ];

        Mail::send('email_body', ['data' => $data], function ($message) use ($email) {
            $message->to($email)
                ->subject('Verification Code');
        });

        $msg = "OTP Resent";
        $response = [
            'msg' => html_entity_decode($msg)

        ];

        return response()->json($response);
    }
    

    public function getfocusarea()
    {
        $sentences = DB::table('bodyparts')
            ->get();

        foreach ($sentences as $val) {
            $bodypart_id = $val->bodypart_id;
            $bodypart_title = $val->bodypart_title;
            $bodypart_image = $val->bodypart_image;
            $arr[] = [
                'bodypart_id' => $bodypart_id,
                'bodypart_title' => $bodypart_title,
                'bodypart_image' => $this->base_url . "/images/" . $bodypart_image
            ];
        }
        echo json_encode($arr, JSON_NUMERIC_CHECK);
    }
    public function getcategorydiet()
    {
        $id = request('id');
        $sentences = DB::table('diets')
            ->where('diet_category', $id)
            ->get();

        echo json_encode($sentences, JSON_NUMERIC_CHECK);
    }

    public function dietdetails()
    {
      
        $diet_id = request('id');
        $sentences = DB::table('diets')
            ->where('diet_id', $diet_id)
            ->get();

        echo json_encode($sentences, JSON_NUMERIC_CHECK);
    }
    public function goals_levels_focusarea_data()
    {
        // Fetch goals
        $sentences = DB::table('goals')->get();
        $arr = [];
        foreach ($sentences as $val) {
            $goal_id = $val->goal_id;
            $goal_gender = $val->gender;
            $goal_title = $val->goal_title;
            $goal_image = $val->goal_image;
            $arr[] = [
                'goal_id' => $goal_id,
                'goal_gender' => $goal_gender,
                'goal_title' => $goal_title,
                'goal_image' =>$this->base_url . "/images/" . $goal_image
            ];
        }
         // Fetch levels
        $sentences = DB::table('levels')->get();
        $arr1 = [];

        foreach ($sentences as $val) {
            $level_id = $val->level_id;
            $level_title = $val->level_title;
            $level_rate = $val->level_rate;
            $level_image = $val->level_image;
            $arr1[] = [
                'level_id' => $level_id,
                'level_title' => $level_title,
                'level_rate' => $level_rate,
                'level_image' => $this->base_url . "/images/" . $level_image
            ];
        }

        // Fetch bodyparts
        $sentences = DB::table('bodyparts')->get();
        $arr2 = [];

        foreach ($sentences as $val) {
            $bodypart_id = $val->bodypart_id;
            $bodypart_title = $val->bodypart_title;
            $bodypart_image = $val->bodypart_image;
            $arr2[] = [
                'bodypart_id' => $bodypart_id,
                'bodypart_title' => $bodypart_title,
                'bodypart_image' => $this->base_url . "/images/" . $bodypart_image
            ];
        }

        // Return the response
        return response()->json([
            'goal' => $arr,
            'level' => $arr1,
            'focusarea' => $arr2
        ]);
    }
    public function equipment_data()
    {
        $sentences = DB::table('equipments')
            ->get();

        foreach ($sentences as $val) {
            $equipment_id = $val->equipment_id;
            $equipment_title = $val->equipment_title;
            $equipment_image = $val->equipment_image;
            $arr[] = [
                'equipment_id' => $equipment_id,
                'equipment_title' => $equipment_title,
                'equipment_image' => $this->base_url . "/images/" . $equipment_image
            ];
        }
        echo json_encode($arr, JSON_NUMERIC_CHECK);
    }

    public function no_equipment()
    {
        $sentences = DB::table('exercises')
            ->where('exercise_equipment', 4)
            ->get();
        foreach ($sentences as $val) {
            $exercise_id = $val->exercise_id;
            $exercise_title = $val->exercise_title;
            $exercise_reps = $val->exercise_reps;
            $exercise_sets = $val->exercise_sets;
            $exercise_equipment = $val->exercise_equipment;
            $exercise_rest = $val->exercise_rest;
            $exercise_level = $val->exercise_level;
            $exercise_image = $val->exercise_image;
            $exercise_video = $val->exercise_video;
            $exercise_tips = $val->exercise_tips;
            $exercise_instructions = $val->exercise_instructions;
            $arr[] = [
                'exercise_id' => $exercise_id,
                'exercise_title' => $exercise_title,
                'exercise_reps' => $exercise_reps,
                'exercise_equipment' => $exercise_equipment,
                'exercise_sets' => $exercise_sets,
                'exercise_rest' => $exercise_rest,
                'exercise_level' => $exercise_level,
                'exercise_image' =>$this->base_url . "/images/" . $exercise_image,
                'exercise_video' => $exercise_video,
                'exercise_tips' => $exercise_tips,
                'exercise_instructions' => $exercise_instructions,
            ];
        }
        echo json_encode($arr, JSON_NUMERIC_CHECK);
    }
    public function equipment_workout()
    {
        $equipment_id = request('id');
        $sentences = DB::table('workouts')
            ->where('workout_equipment', $equipment_id)
            ->get();
        foreach ($sentences as $val) {
            $workout_id = $val->workout_id;
            $workout_title = $val->workout_title;
            $workout_description = $val->workout_description;
            $workout_goal = $val->workout_goal;
            $workout_level = $val->workout_level;
            $workout_bodypart = $val->workout_bodypart;
            $workout_gender = $val->workout_gender;
            $workout_minage = $val->workout_minage;
            $workout_maxage = $val->workout_maxage;
            $workout_equipment = $val->workout_equipment;
            $workout_duration = $val->workout_duration;
            $workout_status = $val->workout_status;
            $workout_price = $val->workout_price;
            $workout_image = $val->workout_image;
            $arr[] = [
                'workout_id' => $workout_id,
                'workout_title' => $workout_title,
                'workout_description' => $workout_description,
                'workout_goal' => $workout_goal,
                'workout_level' => $workout_level,
                'workout_bodypart' => $workout_bodypart,
                'workout_gender' => $workout_gender,
                'workout_minage' => $workout_minage,
                'workout_maxage' => $workout_maxage,
                'workout_equipment' => $workout_equipment,
                'workout_duration' => $workout_duration,
                'workout_status' => $workout_status,
                'workout_price' => $workout_price,
                'workout_image' =>$this->base_url . "/images/" . $workout_image
            ];
        }
        echo json_encode($arr, JSON_NUMERIC_CHECK);
    }
    public function levels_data()
    {
        $sentences = DB::table('levels')
            ->get();
        foreach ($sentences as $val) {

            $level_id = $val->level_id;
            $level_title = $val->level_title;
            $level_rate = $val->level_rate;
            $level_image = $val->level_image;
            $arr[] = [
                'level_id' => $level_id,
                'level_title' => $level_title,
                'level_rate' => $level_rate,
                'level_image' =>$this->base_url . "/images/" . $level_image
            ];
        }
        echo json_encode($arr, JSON_NUMERIC_CHECK);
    }

    public function get_profile_image(Request $request)
    {
        $token = request('login_token');
        $email = request('email');
        $sqlquery = DB::table('users')->where('email', $email)->first();
        $dbtoken = $sqlquery->login_token;
        if ($token === $dbtoken) {
            $result_login = DB::table('users')
                ->where('email', $email)
                ->first();
            if ($result_login !== null) {
                $dbname = $result_login->name;
                $dbimage = $result_login->image;
                $status = "image updated";
                $data[] = [
                    'name'   => $dbname,
                    'email'  => $email,
                    'image'  => $this->base_url . "/json/profile_img/" . $dbimage,
                    'status' => $status,
                ];
                return response()->json($data, 200, [], JSON_NUMERIC_CHECK);
            } else {
                $msg = "error";
                $data = [
                    'msg' => html_entity_decode($msg),
                ];
                return response()->json($data, 200, [], JSON_NUMERIC_CHECK);
            }
        } else {
            $msg[] = [
                'msg' => "invalid token"
            ];
            echo json_encode($msg, JSON_NUMERIC_CHECK);
        }
    }
    public function update_profile_image(Request $request)
    {
         $version = $request->input('version');
        $versions = DB::table('versions')->where('versions', $version)->first();
    
        $user_id = $request->input('user_id');
        $token = $request->input('token');
        $sqlquery = DB::table('users')->where('id', $user_id)->get();
        $dbtoken = $sqlquery[0]->login_token;
        if ($versions) {
            if ($token === $dbtoken) {
                   if($request->hasfile('image')){
                        $image = $request->file('image');
                        $ext = $image->extension();
                        $myfile = time() . '.' . $ext;
                        //   $image->storeAs('/public/profile_image', $myfile);
                        // $image->storeAs('public/profile_image', $myfile);
                        // $imagedata = DB::table('users')->select('image')->where('id', $user_id)->first();
                        // $privious_img =$imagedata->image;
                        //   $filePath = public_path('storage/image/' . $privious_img);
                        //         if (file_exists($filePath))
                        //            {
                        //              unlink($filePath);
                        //            }
                        DB::table('users')
                            ->where('id', $user_id)
                            ->update(['image' => $myfile]);
                            $image->move(public_path('profile_image'), $myfile);
                        $msg[] = [
                            'msg' => "profile updated"
                        ];
                        echo json_encode($msg, JSON_NUMERIC_CHECK);
                   }
           }else{
                   return response()->json(['msg' => 'invalid token']);
            }
        } else {
             return response()->json(['msg' => 'Please update the app to the latest version.']);
        }
    
    }
    
    public function update_profile(Request $request)
    {
        $token = request('login_token');
        $email = request('email');
        $sqlquery = DB::table('users')->where('email', $email)->first();
        $dbtoken = $sqlquery->login_token;
        if ($token === $dbtoken) {
            $email = $request->input('email');
            $image = $request->input('image');
            $name = $request->input('name');
            $gender = $request->input('gender');
            $goal = $request->input('goal');
            $age = $request->input('age');
            $focus_area = $request->input('focus_area');
            $fitness_level = $request->input('fitness_level');
            $gender = $request->input('gender');

            if ($request->hasFile('image')) {
                $fileName = $request->file('image')->getClientOriginalName();
                $targetDir = 'profile_img/';
                $targetFile = $targetDir . basename($fileName);
                $uploadOk = 1;
                $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                $check = getimagesize($request->file('image')->getPathName());

                if ($request->file('image')->move($targetDir, $fileName)) {
                    // Update the user's profile image in the database
                    DB::table('users')
                        ->where('email', $email)
                        ->update([
                            'name' => $name,
                            'image' => $fileName, // Update with the new image value
                            'gender' => $gender,
                            'goal' => $goal,
                            'age' => $age,
                            'focus_area' => $focus_area,
                            'fitness_level' => $fitness_level,
                        ]);
                    $msg[] = [
                        'msg' => "profile updated"
                    ];
                    echo json_encode($msg, JSON_NUMERIC_CHECK);
                } else {
                    $msg[] = [
                        'msg' => "File is not an image"
                    ];
                    echo json_encode($msg, JSON_NUMERIC_CHECK);
                }
            } else {
                // Update the user's profile image in the database
                DB::table('users')
                    ->where('email', $email)
                    ->update([
                        'name' => $name,
                        'gender' => $gender,
                        'goal' => $goal,
                        'age' => $age,
                        'focus_area' => $focus_area,
                        'fitness_level' => $fitness_level,
                    ]);
                $msg[] = [
                    'msg' => "profile updated"
                ];
                echo json_encode($msg, JSON_NUMERIC_CHECK);
            }
        } else {
            $msg[] = [
                'msg' => "invalid token"
            ];
            echo json_encode($msg, JSON_NUMERIC_CHECK);
        }
    }
        public function workout_category()
    {
        $level_id = request('id');
        $sentences = DB::table('workouts')
            ->where('workout_level', $level_id)
            ->get();
        foreach ($sentences as $val) {
            $workout_id = $val->workout_id;
            $workout_title = $val->workout_title;
            $workout_description = $val->workout_description;
            $workout_goal = $val->workout_goal;
            $workout_level = $val->workout_level;
            $workout_bodypart = $val->workout_bodypart;
            $workout_gender = $val->workout_gender;
            $workout_minage = $val->workout_minage;
            $workout_maxage = $val->workout_maxage;
            $workout_equipment = $val->workout_equipment;
            $workout_duration = $val->workout_duration;
            $workout_status = $val->workout_status;
            $workout_price = $val->workout_price;
            $workout_image = $val->workout_image;
            $arr[] = [
                'workout_id' => $workout_id,
                'workout_title' => $workout_title,
                'workout_description' => $workout_description,
                'workout_goal' => $workout_goal,
                'workout_level' => $workout_level,
                'workout_bodypart' => $workout_bodypart,
                'workout_gender' => $workout_gender,
                'workout_minage' => $workout_minage,
                'workout_maxage' => $workout_maxage,
                'workout_equipment' => $workout_equipment,
                'workout_duration' => $workout_duration,
                'workout_status' => $workout_status,
                'workout_price' => $workout_price,
                'workout_image' =>$this->base_url . "/images/" . $workout_image
            ];
        }
        echo json_encode($arr, JSON_NUMERIC_CHECK);
    }
     public function days()
    {
        $workout_id = request('workout_id');
        $day = request('day');

        if ($day == 1) {
            $day1 = DB::table('we_day1')
                ->where('workout_id', $workout_id)
                ->where('day_1', $day)
                ->get();
            foreach ($day1 as $val) {
                $exercise_id = $val->exercise_id;

                $exercise = DB::table('exercises')
                    ->where('exercise_id', $exercise_id)
                    ->get();
                foreach ($exercise as $val) {
                    $exercise_id = $val->exercise_id;
                    $exercise_title = $val->exercise_title;
                    $exercise_reps = $val->exercise_reps;
                    $exercise_sets = $val->exercise_sets;
                    $exercise_rest = $val->exercise_rest;
                    $exercise_equipment = $val->exercise_equipment;
                    $exercise_level = $val->exercise_level;
                    $exercise_image_link = $val->exercise_image_link;
                    $exercise_video = $val->exercise_video;
                    $video = $val->video;
                    $exercise_instructions = $val->exercise_instructions;
                    $day_1[] = [
                        'exercise_id' => $exercise_id,
                        'exercise_title' => $exercise_title,
                        'exercise_reps' => $exercise_reps,
                        'exercise_sets' => $exercise_sets,
                        'exercise_rest' => $exercise_rest,
                        'exercise_equipment' => $exercise_equipment,
                        'exercise_level' => $exercise_level,
                        'exercise_image' => $exercise_image_link,
                        'exercise_video' => $exercise_video,
                        'exercise_instructions' => $exercise_instructions,
                       'video' =>$this->base_url . "/images/" . $video,
                    ];
                }
            }
            // echo json_encode($arr, JSON_NUMERIC_CHECK);
            return response()->json($day_1);
        } elseif ($day == 2) {
            $day2 = DB::table('we_day2')
                ->where('workout_id', $workout_id)
                ->where('day_2', $day)
                ->get();
            if (isset($day2[0])) {
                foreach ($day2 as $val) {
                    $exercise_id = $val->exercise_id;
                    $exercise = DB::table('exercises')
                        ->where('exercise_id', $exercise_id)
                        ->get();
                    foreach ($exercise as $val) {
                        $exercise_id = $val->exercise_id;
                        $exercise_title = $val->exercise_title;
                        $exercise_reps = $val->exercise_reps;
                        $exercise_sets = $val->exercise_sets;
                        $exercise_rest = $val->exercise_rest;
                        $exercise_equipment = $val->exercise_equipment;
                        $exercise_level = $val->exercise_level;
                        $exercise_image_link = $val->exercise_image_link;
                        $exercise_video = $val->exercise_video;
                        $video = $val->video;
                        $exercise_instructions = $val->exercise_instructions;
                        $day_2[] = [
                            'exercise_id' => $exercise_id,
                            'exercise_title' => $exercise_title,
                            'exercise_reps' => $exercise_reps,
                            'exercise_sets' => $exercise_sets,
                            'exercise_rest' => $exercise_rest,
                            'exercise_equipment' => $exercise_equipment,
                            'exercise_level' => $exercise_level,
                            'exercise_image' => $exercise_image_link,
                            'exercise_video' => $exercise_video,
                            'exercise_instructions' => $exercise_instructions,
                            'video' =>$this->base_url . "/images/" . $video,
                        ];
                    }
                    return response()->json($day_2);
                }
            } else {
               
                   return response()->json([
            'msg' => 'no data found.'
          
        ]);
            }
        } elseif ($day == 3) {
            $day3 = DB::table('we_day3')
                ->where('workout_id', $workout_id)
                ->where('day_3', $day)
                ->get();
            if (isset($day3[0])) {
                foreach ($day3 as $val) {
                    $exercise_id = $val->exercise_id;
                    $exercise = DB::table('exercises')
                        ->where('exercise_id', $exercise_id)
                        ->get();
                    foreach ($exercise as $val) {
                        $exercise_id = $val->exercise_id;
                        $exercise_title = $val->exercise_title;
                        $exercise_reps = $val->exercise_reps;
                        $exercise_sets = $val->exercise_sets;
                        $exercise_rest = $val->exercise_rest;
                        $exercise_equipment = $val->exercise_equipment;
                        $exercise_level = $val->exercise_level;
                        $exercise_image_link = $val->exercise_image_link;
                        $exercise_video = $val->exercise_video;
                        $video = $val->video;
                        $exercise_instructions = $val->exercise_instructions;
                        $day_3[] = [
                            'exercise_id' => $exercise_id,
                            'exercise_title' => $exercise_title,
                            'exercise_reps' => $exercise_reps,
                            'exercise_sets' => $exercise_sets,
                            'exercise_rest' => $exercise_rest,
                            'exercise_equipment' => $exercise_equipment,
                            'exercise_level' => $exercise_level,
                            'exercise_image' => $exercise_image_link,
                            'exercise_video' => $exercise_video,
                            'exercise_instructions' => $exercise_instructions,
                             'video' =>$this->base_url . "/images/" . $video,
                        ];
                    }
                }
                  return response()->json($day_3);
            } else {
               
                   return response()->json([
            'msg' => 'no data found.'
          
        ]);
            }
        } elseif ($day == 4) {
            $day4 = DB::table('we_day4')
                ->where('workout_id', $workout_id)
                ->where('day_4', $day)
                ->get();
            if (isset($day4[0])) {
                foreach ($day4 as $val) {
                    $exercise_id = $val->exercise_id;
                    $exercise = DB::table('exercises')
                        ->where('exercise_id', $exercise_id)
                        ->get();
                    foreach ($exercise as $val) {
                        $exercise_id = $val->exercise_id;
                        $exercise_title = $val->exercise_title;
                        $exercise_reps = $val->exercise_reps;
                        $exercise_sets = $val->exercise_sets;
                        $exercise_rest = $val->exercise_rest;
                        $exercise_equipment = $val->exercise_equipment;
                        $exercise_level = $val->exercise_level;
                        $exercise_video = $val->video;
                        $exercise_image_link = $val->exercise_image_link;
                        $exercise_video = $val->exercise_video;
                        $video = $val->video;
                        $exercise_instructions = $val->exercise_instructions;
                        $day_4[] = [
                            'exercise_id' => $exercise_id,
                            'exercise_title' => $exercise_title,
                            'exercise_reps' => $exercise_reps,
                            'exercise_sets' => $exercise_sets,
                            'exercise_rest' => $exercise_rest,
                            'exercise_equipment' => $exercise_equipment,
                            'exercise_level' => $exercise_level,
                            'exercise_image' => $exercise_image_link,
                            'exercise_video' => $exercise_video,
                            'exercise_instructions' => $exercise_instructions,
                            'video' =>$this->base_url . "/images/" . $video,
                        ];
                    }
                }
                    return response()->json($day_4);
            } else {
              
                   return response()->json([
            'msg' => 'no data found.'
          
        ]);
            }
        } elseif ($day == 5) {
            $day5 = DB::table('we_day5')
                ->where('workout_id', $workout_id)
                ->where('day_5', $day)
                ->get();
            if (isset($day5[0])) {
                foreach ($day5 as $val) {
                    $exercise_id = $val->exercise_id;
                    $exercise = DB::table('exercises')
                        ->where('exercise_id', $exercise_id)
                        ->get();
                    foreach ($exercise as $val) {
                        $exercise_id = $val->exercise_id;
                        $exercise_title = $val->exercise_title;
                        $exercise_reps = $val->exercise_reps;
                        $exercise_sets = $val->exercise_sets;
                        $exercise_rest = $val->exercise_rest;
                        $exercise_equipment = $val->exercise_equipment;
                        $exercise_level = $val->exercise_level;
                        $exercise_image_link = $val->exercise_image_link;
                        $video = $val->video;
                        $exercise_video = $val->exercise_video;
                        $exercise_instructions = $val->exercise_instructions;
                        $day_5[] = [
                            'exercise_id' => $exercise_id,
                            'exercise_title' => $exercise_title,
                            'exercise_reps' => $exercise_reps,
                            'exercise_sets' => $exercise_sets,
                            'exercise_rest' => $exercise_rest,
                            'exercise_equipment' => $exercise_equipment,
                            'exercise_level' => $exercise_level,
                            'exercise_image' => $exercise_image_link,
                            'exercise_video' => $exercise_video,
                            'exercise_instructions' => $exercise_instructions,
                           'video' =>$this->base_url . "/images/" . $video,
                        ];
                    }
                }
               return response()->json($day_5);
            } else {
               
                   return response()->json([
            'msg' => 'no data found.'
          
        ]);
            }
        } elseif ($day == 6) {
            $day6 = DB::table('we_day6')
                ->where('workout_id', $workout_id)
                ->where('day_6', $day)
                ->get();
            if (isset($day6[0])) {
                foreach ($day6 as $val) {
                    $exercise_id = $val->exercise_id;
                    $exercise = DB::table('exercises')
                        ->where('exercise_id', $exercise_id)
                        ->get();
                    foreach ($exercise as $val) {
                        $exercise_id = $val->exercise_id;
                        $exercise_title = $val->exercise_title;
                        $exercise_reps = $val->exercise_reps;
                        $exercise_sets = $val->exercise_sets;
                        $exercise_rest = $val->exercise_rest;
                        $exercise_equipment = $val->exercise_equipment;
                        $exercise_level = $val->exercise_level;
                        $exercise_image_link = $val->exercise_image_link;
                        $video = $val->video;
                        $exercise_video = $val->exercise_video;
                        $exercise_instructions = $val->exercise_instructions;
                        $day_6[] = [
                            'exercise_id' => $exercise_id,
                            'exercise_title' => $exercise_title,
                            'exercise_reps' => $exercise_reps,
                            'exercise_sets' => $exercise_sets,
                            'exercise_rest' => $exercise_rest,
                            'exercise_equipment' => $exercise_equipment,
                            'exercise_level' => $exercise_level,
                            'exercise_image' => $exercise_image_link,
                            'exercise_video' => $exercise_video,
                            'exercise_instructions' => $exercise_instructions,
                            'video' =>$this->base_url . "/images/" . $video,
                        ];
                    }
                }
                 return response()->json($day_6);
            } else {
               
                   return response()->json([
            'msg' => 'no data found.'
          
        ]);
            }
        } elseif ($day == 7) {
            $day7 = DB::table('we_day7')
                ->where('workout_id', $workout_id)
                ->where('day_7', $day)
                ->get();
            if (isset($day7[0])) {
                foreach ($day7 as $val) {
                    $exercise_id = $val->exercise_id;
                    $exercise = DB::table('exercises')
                        ->where('exercise_id', $exercise_id)
                        ->get();
                    foreach ($exercise as $val) {
                        $exercise_id = $val->exercise_id;
                        $exercise_title = $val->exercise_title;
                        $exercise_reps = $val->exercise_reps;
                        $exercise_sets = $val->exercise_sets;
                        $exercise_rest = $val->exercise_rest;
                        $exercise_equipment = $val->exercise_equipment;
                        $exercise_level = $val->exercise_level;
                        $exercise_image_link = $val->exercise_image_link;
                        $exercise_video = $val->exercise_video;
                        $video = $val->video;
                        $exercise_instructions = $val->exercise_instructions;
                        $day_7[] = [
                            'exercise_id' => $exercise_id,
                            'exercise_title' => $exercise_title,
                            'exercise_reps' => $exercise_reps,
                            'exercise_sets' => $exercise_sets,
                            'exercise_rest' => $exercise_rest,
                            'exercise_equipment' => $exercise_equipment,
                            'exercise_level' => $exercise_level,
                            'exercise_image' => $exercise_image_link,
                            'exercise_video' => $exercise_video,
                            'exercise_instructions' => $exercise_instructions,
                            'video' =>$this->base_url . "/images/" . $video,
                        ];
                    }
                }
                 return response()->json($day_7);
            } else {
              
                   return response()->json([
            'msg' => 'no data found.'
          
        ]);
            }
        }
    }
    
    
   public function transactions(Request $request)
    {
        $user_id = $request->user_id;
        $user = DB::table('transaction')->where('user_id', $user_id)->first();
    
        $planname = $request->planname;
        $transaction_id = $request->transaction_id;
        $planid = $request->planid;
        $planstatus = $request->planstatus;
        $platform = $request->platform;

        if($planname=="Monthly"){
            $startdate = date('Y-m-d');
            $enddate = date('Y-m-d', strtotime($startdate . ' +30 days'));
        }elseif($planname=="Quarterly"){
            $startdate = date('Y-m-d');
            $enddate = date('Y-m-d', strtotime($startdate . ' +90 days'));
        }elseif ($planname == "Yearly" && $platform == "android") {
            $startdate = date('Y-m-d');
            $enddate = date('Y-m-d', strtotime($startdate . ' +368 days'));
        }else{
             $startdate = date('Y-m-d');
            $enddate = date('Y-m-d', strtotime($startdate . ' +365 days'));
        }
    
        if ($user) {
            // Update existing row to 'Not Active'
            DB::table('transaction')->where('user_id', $user_id)->update([
                'plan_status' => 'Not Active',
            ]);
    
            // Insert a new row with the updated data
            DB::table('transaction')->insert([
                'user_id' => $user_id,
                'plan_name' => $planname,
                'transaction_id' => $transaction_id,
                'plan_id' => $planid,
                'plan_start_date' => $startdate,
                'plan_end_date' => $enddate,
                'plan_status' => $planstatus,
                'platform' => $platform,
            ]);
            return response()->json(['status' => "transaction completed"]);
        } else {
            // Insert a new row if the user doesn't exist
            DB::table('transaction')->insert([
                'user_id' => $user_id,
                'plan_name' => $planname,
                'transaction_id' => $transaction_id,
                'plan_id' => $planid,
                'plan_start_date' => $startdate,
                'plan_end_date' => $enddate,
                 'plan_status' => $planstatus,
                'platform' => $platform,
            ]);
    
            return response()->json(['status' => "transaction completed"]);
        }
    }
    public function transactionsdetails(Request $request){
       $id= $request->id;
       $token= $request->token;
       $user = DB::table('users')->where('login_token', $token)->first();
       if($user){
       $transaction_user = DB::table('transaction')
       ->select('*')
       ->where('user_id',$id)
       ->where('plan_status','Active')
       ->get();

       return response()->json(['data' => $transaction_user]);
    }else{
        return response()->json(['msg' => 'Invalid Token']);
    }
}
    
    
    public function popularWorkout(){

        $popularWorkouts = DB::table('favorite')
            ->select('workout_id', DB::raw('count(workout_id) as count'))
            ->groupBy('workout_id')
            ->orderByDesc('count')
            ->limit(2)
            ->get();
    
        $workoutIds = $popularWorkouts->pluck('workout_id'); // Extract workout IDs from the result
    
        // Fetch workout details for the top two popular workout IDs
        $workoutsdata = DB::table('workouts')
            ->whereIn('workout_id', $workoutIds)
            ->get();
                foreach ($workoutsdata as $val) {
                    $workout_id = $val->workout_id;
                    $workout_title = $val->workout_title;
                    $workout_description = $val->workout_description;
                    $workout_goal = $val->workout_goal;
                    $workout_level = $val->workout_level;
                    $workout_bodypart = $val->workout_bodypart;
                    $workout_gender = $val->workout_gender;
                    $workout_minage = $val->workout_minage;
                    $workout_maxage = $val->workout_maxage;
                    $workout_injury = $val->workout_injury;
                    $workout_injury = $val->workout_injury;
                    $workout_equipment = $val->workout_equipment;
                    $workout_duration = $val->workout_duration;
                    $workout_status = $val->workout_status;
                    $workout_price = $val->workout_price;
                    $workout_image = $val->workout_image;
                $workouts[] = [
                    'workout_id' => $workout_id,
                    'workout_title' => $workout_title,
                    'workout_description' => $workout_description,
                    'workout_goal' => $workout_goal,
                    'workout_level' => $workout_level,
                    'workout_bodypart' => $workout_bodypart,
                    'workout_gender' => $workout_gender,
                    'workout_minage' => $workout_minage,
                    'workout_maxage' => $workout_maxage,
                    'workout_equipment' => $workout_equipment,
                    'workout_duration' => $workout_duration,
                    'workout_status' => $workout_status,
                    'workout_price' => $workout_price,
                    'workout_image' => $this->base_url . "/images/" . $workout_image

                ];
                // $exersicedata = DB::table('exercises')
                // ->where('workout_level', $workout_level)
                // ->get();
            
         
                  $exersicedata = DB::table('exercises')
                  ->where('exercise_level', $workout_level)
                  ->whereNotNull('exercise_gender')
                //   ->where('exercise_goal',$workout_goal)
                  ->where('exercise_gender', $workout_gender)
                  ->get();
            }   
        return response()->json(['workouts' => $workouts,'data' => $exersicedata]);
    }
    public function getdiet(Request $request){
       $token = request('login_token');
       $version = $request->input('version');
       $user_id = request('user_id');
       $diet_id = request('diet_id');;
       $sqlquery = DB::table('users')->where('id', $user_id)->first();
       $dbtoken = $sqlquery->login_token;
       $version = $request->input('version');
       
          $versions = DB::table('versions')->where('versions', $version)->first();
    if($versions){
        if ($token === $dbtoken) {
            
           
                $dietdata = DB::table('diets')
            ->where('diet_id', $diet_id)
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
                'diet_image' => $this->base_url . "/images/" . $diet_image,
            ];
      echo json_encode($diets, JSON_NUMERIC_CHECK);
}
            }else{
              $msg[] = [
            'msg' => "invalid token"
        ];
        echo json_encode($msg, JSON_NUMERIC_CHECK);
            }
            
    }else{
         return response()->json([
            'msg' => 'Please update the app to the latest version.'
          
        ]);
    }
    }
    public function categorydata(){
        
        // $user = DB::table('users')->where('login_token', $token)->first();
        // $user_device_id = DB::table('users')->where('device_id', $deviceid)->first();
        
      
            $typesdata = DB::table('types')->get();
        
            return response()->json(['data' =>$typesdata, 'status'=>'Invalid token']);
        }
        
       
    
public function productdata(Request $request){
    
   $type_id = $request->type_id;
         $productdata = DB::table('products')
            ->where('product_type', $type_id)
            ->get();
            
            if ($productdata->isEmpty()) {
        return response()->json(['status' => 'data not found']);
} else {
    $status = "data found";
  return response()->json(['data' => $productdata,'status'=>$status]);
    }
}
              
public function mindsetdata(Request $request){
      $workout_mindset_id = $request->workout_mindset_id;
      $version = $request->input('version');
      $versions_current = DB::table('versions')->where('versions', $version)->where('type','current')->first();
      $versions_middle = DB::table('versions')->where('versions', $version)->where('type','middle')->first();
      $versions_past = DB::table('versions')->where('versions', $version)->where('type','past')->first();
    //   $health_level = $request->health_level;
    if($versions_current){
         $mindsetdata = DB::table('exercise_mindset')
        //   ->where('exercise_mindset_healthlevel', $health_level)
           ->where('workout_mindset_id', $workout_mindset_id)
            ->get();        
        
             
    if ($mindsetdata->isNotEmpty()) {
    $status = "data found";
    return response()->json(['data' => $mindsetdata, 'status' => $status]);
        } else {
            $status = "data not found";
            return response()->json(['status' => $status]);
        }
    }elseif($versions_middle){
        $mindsetdata = DB::table('exercise_mindset')
        //   ->where('exercise_mindset_healthlevel', $health_level)
           ->where('workout_mindset_id', $workout_mindset_id)
            ->get();        
        
             
    if ($mindsetdata->isNotEmpty()) {
    $status = "data found";
    return response()->json(['data' => $mindsetdata, 'status' => $status]);
        } else {
            $status = "data not found";
            return response()->json(['status' => $status]);
        }
    }elseif($versions_past){
        $mindsetdata = DB::table('exercise_mindset')
        //   ->where('exercise_mindset_healthlevel', $health_level)
           ->where('workout_mindset_id', $workout_mindset_id)
            ->get();        
        
             
    if ($mindsetdata->isNotEmpty()) {
    $status = "data found";
    return response()->json(['data' => $mindsetdata, 'status' => $status]);
        } else {
            $status = "data not found";
            return response()->json(['status' => $status]);
        }
    }else{
        return response()->json([
            'msg' => 'Please update the app to the latest version.'
          
        ]);
    }

            
           
}
public function cronmail(Request $request){
      $emails = $request->input('email');
    
    Mail::send('sendmail', [], function ($message) use ($emails) {
        $message->to($emails)->subject('testing cron');
    });
    echo "done";
    
    
}
public function push_notification(Request $request){
         
        
        $inputData = $request->input('data');
        $firebaseToken = $request->input('token');
        
        $SERVER_API_KEY = 'AAAADgoThgU:APA91bHFusexjj2_BQ8hggO6eJgVRojGLLlk4rsWELZMf-49GO9mBW5tGxLNiFsFqC8rG15SCgOTzC8yVkQvYnK0vHFT9kx9N5UuMCf10u08KNZF6HFv9O6szfXADVHucZsVx0mOd_Xb';
        
        $message = [
            "to" => $firebaseToken,
            "notification" => [
                "title" => $request->title,
                "body" => $request->body,
            ]
        ];
        
        $dataString = json_encode($message);
        
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }
        
        curl_close($ch);
        
        print_r($response);

   }
   
   public function setp_count(Request $request){
        $version = $request->input('version');
        $versions = DB::table('versions')->where('versions', $version)->get();
        $user_id = $request->input('user_id');
        $token = $request->input('token');
         $user = DB::table('users')
        ->where('login_token', $token)
        ->where('id', $user_id)
        ->first();
        
        if ($versions) {
            if($user){
                   $user_stepcount = DB::table('user_stepcounts_details')->get();
                   return response()->json($user_stepcount);
            }else{
                   return response()->json(['msg' => 'invalid token']);
            }
        } else {
             return response()->json(['msg' => 'Please update the app to the latest version.']);
        }
    
    }
    public function update_details(Request $request){
         
       $version = $request->input('version');
       $versions_current = DB::table('versions')->where('versions', $version)->where('type','current')->first();
       $versions_middle = DB::table('versions')->where('versions', $version)->where('type','middle')->first();
       $versions_past = DB::table('versions')->where('versions', $version)->where('type','past')->first();
       $versions = DB::table('versions')->where('versions', $version)->first();
       $user_id = $request->input('id');
        $token = $request->input('token');
         $user = DB::table('users')
        ->where('login_token', $token)
        ->where('id', $user_id)
        ->first();
        
        
        
        if ($versions_current) {
            if($user){
                $name = $request->input('name');
                $email = $request->input('email');
                $goal = $request->input('goal');
                $injury = $request->input('injury');
                $target_weight = $request->input('target_weight');
                $equipment = $request->input('equipment_type');
                $focus_area = $request->input('focusarea');
                $workoutarea = $request->input('place');
                $gender = $request->input('gender');
                $experience = $request->input('experience');
                $workout_plans = $request->input('workout_plans');
                
                if(empty($goal)){
                    $db_goal = null;
                }else{
                    $goal_id = DB::table('goals')->select('goal_id','gender')->where('goal_title',$goal)->where('gender',$gender)->first();
                 $db_goal = $goal_id->goal_id;
                    
                }
                  DB::table('users')
                        ->where('id', $user_id)
                        ->update([
                            'name' => $name,
                            'email' => $email,
                            'goal' => $db_goal,
                            'injury' => $injury ?? null,
                            'target_weight' => $target_weight,
                            'gender' => $gender,
                            'focus_area' => $focus_area,
                            'equipment' => $equipment,
                            'workoutarea' => $workoutarea,
                            'experience' => $experience,
                            'workout_plans' => $workout_plans
                            
                         
                        ]);
                        $userData = DB::table('users')->select('id', 'goal', 'age', 'height', 'weight', 'fitness_level', 'injury', 'target_weight', 'workoutarea', 'equipment', 'experience', 'focus_area', 'gender', 'name', 'email', 'image', 'login_token')
                      ->where('id', $user_id)->first(); 
                      
                    
    
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
                    $userData->goal_title = $goal_title->goal_title??null;
                    $userData->level_title = $level_title->level_title??null;
                    $userData->focusarea_title = $focusarea_title->bodypart_title??null;
                    //   $userData->experience = $experience->experience??null;
                
                            
                }
                        $userdetails = DB::table('users')->select('*')->where('id', $user_id)->first();
                       
                         // && $userdetails->age && $userdetails->focus_area  && $userdetails->goal  && $userdetails->fitness_level && $userdetails->workoutarea && $userdetails->gender
                        if ($userdetails) {
                            $focusAreas = explode(',', $userdetails->focus_area);
                            // dd("userdata");
                            // $workoutarea = explode(',', $userdetails->workoutarea);
                
                            $workoutQuery = DB::table('workouts')->select('*')
                                ->where('workout_gender', $userdetails->gender)
                                ->where('workout_level', $userdetails->fitness_level)
                                // ->whereIn('workout_area', $userdetails->workoutarea)
                                ->where('workout_goal', $userdetails->goal)
                                ->whereRaw('? BETWEEN workout_minage AND workout_maxage', [$userdetails->age])
                                ->whereIn('workout_bodypart', $focusAreas);
                    
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
                    
                            // return response()->json([
                            //     'allworkouts' => [
                            //         'workout' => $response,
                            //         'minset_workout' => $mindsetworkout,
                            //     ],
                            //     'profile' => $userData,
                            //     'msg' => 'User Updated Successfully'
                            // ]);
                             return response()->json([
                                // 'allworkouts' => [
                                //     'workout' => $response,
                                //     'minset_workout' => $mindsetworkout,
                                // ],
                                'profile' => $userData,
                                'msg' => 'User Updated Successfully'
                            ]);
                            
                            
                        } 
            }else{
                   return response()->json(['msg' => 'invalid token']);
            }
        } elseif($versions_middle){
            if($user){
                $name = $request->input('name');
                $goal = $request->input('goal');
                $injury = $request->input('injury');
                $target_weight = $request->input('target_weight');
                $equipment = $request->input('equipment_type');
                $focus_area = $request->input('focusarea');
                $workoutarea = $request->input('place');
                $gender = $request->input('gender');
            $goal_id = DB::table('goals')->select('goal_id','gender')->where('goal_title',$goal)->where('gender',$gender)->first();
                  DB::table('users')
                        ->where('id', $user_id)
                        ->update([
                            'name' => $name,
                            'goal' => $goal_id->goal_id,
                            'injury' => $injury ?? null,
                            'target_weight' => $target_weight,
                            'gender' => $gender,
                            'focus_area' => $focus_area,
                            'equipment' => $equipment,
                            'workoutarea' => $workoutarea,
                        ]);
                        $userData = DB::table('users')->select('id', 'goal', 'age', 'height', 'weight', 'fitness_level', 'injury', 'target_weight', 'workoutarea', 'equipment', 'focus_area', 'gender', 'name', 'email', 'image', 'login_token')
                      ->where('id', $user_id)->first(); 
    
                if ($userData) {
                    // Assuming the images are stored in the public/profile_img directory
                    $baseUrl =$this->base_url . '/adserver/public/profile_image/'; // Base URL for images
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
                    $userData->goal_title = $goal_title->goal_title??null;
                    $userData->level_title = $level_title->level_title??null;
                    $userData->focusarea_title = $focusarea_title->bodypart_title??null;
                
                          
                }
                        $userdetails = DB::table('users')->select('*')->where('id', $user_id)->first();
    
                        if ($userdetails && $userdetails->gender && $userdetails->goal && $userdetails->age && $userdetails->focus_area && $userdetails->fitness_level && $userdetails->workoutarea) {
                            $focusAreas = explode(',', $userdetails->focus_area);
                            // $workoutarea = explode(',', $userdetails->workoutarea);
                
                            $workoutQuery = DB::table('workouts')->select('*')
                                ->where('workout_gender', $userdetails->gender)
                                ->where('workout_level', $userdetails->fitness_level)
                                // ->whereIn('workout_area', $userdetails->workoutarea)
                                ->where('workout_goal', $userdetails->goal)
                                ->whereRaw('? BETWEEN workout_minage AND workout_maxage', [$userdetails->age])
                                ->whereIn('workout_bodypart', $focusAreas);
                    
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
                    
                            return response()->json([
                                'allworkouts' => [
                                    'workout' => $response,
                                    'minset_workout' => $mindsetworkout,
                                ],
                                'profile' => $userData,
                                'msg' => 'User Updated Successfully'
                            ]);
                            
                            
                        } 
            }else{
                   return response()->json(['msg' => 'invalid token']);
            }
        }elseif($versions_past){
            if($user){
                $name = $request->input('name');
                $goal = $request->input('goal');
                $injury = $request->input('injury');
                $target_weight = $request->input('target_weight');
                $equipment = $request->input('equipment_type');
                $focus_area = $request->input('focusarea');
                $workoutarea = $request->input('place');
                $gender = $request->input('gender');
            $goal_id = DB::table('goals')->select('goal_id','gender')->where('goal_title',$goal)->where('gender',$gender)->first();
                  DB::table('users')
                        ->where('id', $user_id)
                        ->update([
                            'name' => $name,
                            'goal' => $goal_id->goal_id,
                            'injury' => $injury ?? null,
                            'target_weight' => $target_weight,
                            'gender' => $gender,
                            'focus_area' => $focus_area,
                            'equipment' => $equipment,
                            'workoutarea' => $workoutarea,
                        ]);
                        $userData = DB::table('users')->select('id', 'goal', 'age', 'height', 'weight', 'fitness_level', 'injury', 'target_weight', 'workoutarea', 'equipment', 'focus_area', 'gender', 'name', 'email', 'image', 'login_token')
                      ->where('id', $user_id)->first(); 
    
                if ($userData) {
                    // Assuming the images are stored in the public/profile_img directory
                    $baseUrl =$this->base_url . '/adserver/public/profile_image/'; // Base URL for images
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
                    $userData->goal_title = $goal_title->goal_title??null;
                    $userData->level_title = $level_title->level_title??null;
                    $userData->focusarea_title = $focusarea_title->bodypart_title??null;
                
                          
                }
                        $userdetails = DB::table('users')->select('*')->where('id', $user_id)->first();
    
                        if ($userdetails && $userdetails->gender && $userdetails->goal && $userdetails->age && $userdetails->focus_area && $userdetails->fitness_level && $userdetails->workoutarea) {
                            $focusAreas = explode(',', $userdetails->focus_area);
                            // $workoutarea = explode(',', $userdetails->workoutarea);
                
                            $workoutQuery = DB::table('workouts')->select('*')
                                ->where('workout_gender', $userdetails->gender)
                                ->where('workout_level', $userdetails->fitness_level)
                                // ->whereIn('workout_area', $userdetails->workoutarea)
                                ->where('workout_goal', $userdetails->goal)
                                ->whereRaw('? BETWEEN workout_minage AND workout_maxage', [$userdetails->age])
                                ->whereIn('workout_bodypart', $focusAreas);
                    
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
                    
                            return response()->json([
                                'allworkouts' => [
                                    'workout' => $response,
                                    'minset_workout' => $mindsetworkout,
                                ],
                                'profile' => $userData,
                                'msg' => 'User Updated Successfully'
                            ]);
                            
                            
                        } 
            }else{
                   return response()->json(['msg' => 'invalid token']);
            }
        }else {
             return response()->json(['msg' => 'Please update the app to the latest version.']);
        }
        
    }

      public function exercisecalo(Request $request)
        {
            $user_id = $request->user_id;
            // echo(Carbon::today());
            // die();

            // Retrieve all completed exercises for the given user, workout, and exercise status
              $completedExercises = DB::table('user_exercise_complete_status')
                ->join('exercises', 'user_exercise_complete_status.user_exercise_id', '=', 'exercises.exercise_id')
                ->where('user_id', $user_id)
                ->where('exercise_status', 'completed')
                ->whereDate('user_exercise_complete_status.created_at', '=', Carbon::today()->toDateString())
                ->select(
                    'workout_id',
                    'user_exercise_complete_status.user_day',
                    
                    // Adjust based on your actual field name
                    DB::raw('SUM(exercises.exercise_calories) as total_calories'),
                    DB::raw('SUM(CAST(SUBSTRING_INDEX(exercises.exercise_rest, " ", 1) AS UNSIGNED)) as total_rest')
                )->groupBy('workout_id', 'user_exercise_complete_status.user_day') // Group by workout_id and user_day
                ->get(); // Use get() to retrieve multiple rows
                
              
               
                
                //   $completedExercises = DB::table('user_exercise_complete_status')
                // ->join('exercises', 'user_exercise_complete_status.user_exercise_id', '=', 'exercises.exercise_id')
                // ->where('user_id', $user_id)
                // ->where('exercise_status', 'completed')
                // ->select(
                //     'workout_id',
                //     'user_exercise_complete_status.user_day', // Adjust based on your actual field name
                //     DB::raw('SUM(exercises.exercise_calories) as total_calories'),
                //     DB::raw('SUM(CAST(SUBSTRING_INDEX(exercises.exercise_rest, " ", 1) AS UNSIGNED)) as total_rest')
                // )->groupBy('workout_id', 'user_exercise_complete_status.user_day') // Group by workout_id and user_day
                // ->get(); // Use get() to retrieve multiple rows
    
    
             
             $stepscalories = DB::table('user_stepcounts_details')
                ->where('user_id', $user_id)
                 ->whereDate('created_at', '=', Carbon::today()->toDateString())
                ->sum('calories');
            //  if($stepscalories->isEmpty()){

            //   $stepscalories=0;
            // Iterate through each row and insert data into weight_burn_status table
            // dd($stepscalories);
            if ($completedExercises->isEmpty()) {
                     $totalCalories = 0 + $stepscalories;
                     // Calculate total burn calories for the current exercise
                      $total_burn_calories = (0 + $stepscalories) / 500 * 0.3;
                      $checkdata = DB::table('weight_burn_status')
                         ->where('user_id',$user_id)
                        ->whereDate('created_at', '=', Carbon::today()->toDateString())
                        ->get();
                        if ($checkdata->isEmpty()) {
                            $totalCaloriesSum = 0;
                            $totalBurnCaloriesSum =0;
                            DB::table('weight_burn_status')->insert([
                                'user_id' => $user_id,
                                'total_burn_weight' => $total_burn_calories,
                                'total_calories' => $totalCalories,
                                 'user_day' => date('N'),
                                'total_duration' => 0, // Use total_rest or adjust based on your requirements
                            ]);
                            // echo "user_exercise_complete_status table data not exist";
                          return response()->json(['data' => 'Calories calculated and inserted successfully']);
                        }else{
                               DB::table('weight_burn_status')
                              ->where('user_id',$user_id)
                            ->whereDate('created_at', '=', Carbon::today()->toDateString())
                              ->update([
                                'user_id' => $user_id,
                                'total_burn_weight' => $total_burn_calories,
                                'total_calories' => $totalCalories,
                                'user_day' => date('N'),
                                'total_duration' => 0, // Use total_rest or adjust based on your requirements
                            ]);
                                return response()->json(['data' => 'Calories calculated and updated successfully']);
                        }
            }
            $totalCaloriesArray = []; // Array to store total calories for each exercise
            $totalBurnCaloriesArray = []; // Array to store total burn calories for each exercise
            
            foreach ($completedExercises as $exercise) {
                // Calculate total calories for the current exercise
                $totalCalories = $exercise->total_calories + $stepscalories;
                // Calculate total burn calories for the current exercise
                $total_burn_calories = ($exercise->total_calories + $stepscalories) / 500 * 0.3;
                
                // Add the calculated values to their respective arrays
                $totalCaloriesArray[] = $totalCalories;
                $totalBurnCaloriesArray[] = $total_burn_calories;
            }
             $totalCaloriesSum = array_sum($totalCaloriesArray);
             $totalBurnCaloriesSum = array_sum($totalBurnCaloriesArray);
             
             
         
             
             $check_data = DB::table('weight_burn_status')
                         ->where('user_id',$user_id)
                        ->whereDate('created_at', '=', Carbon::today()->toDateString())
                        ->get();
                        
                        if ($check_data->isEmpty()) {
                            
                        DB::table('weight_burn_status')->insert([
                            'user_id' => $user_id,
                            'total_burn_weight' => $totalBurnCaloriesSum,
                            'total_calories' => $totalCaloriesSum,
                             'user_day' => date('N'),
                            'total_duration' => $exercise->total_rest, // Use total_rest or adjust based on your requirements
                        ]);
                        return response()->json(['data' => 'Calories calculated and inserted successfully']);
                        } else {
                              DB::table('weight_burn_status')
                              ->where('user_id',$user_id)
                            ->whereDate('created_at', '=', Carbon::today()->toDateString())
                              ->update([
                                'user_id' => $user_id,
                                'total_burn_weight' => $totalBurnCaloriesSum,
                                'total_calories' => $totalCaloriesSum,
                                 'user_day' => date('N'),
                                'total_duration' => $exercise->total_rest, // Use total_rest or adjust based on your requirements
                                
                            ]);
                            return response()->json(['data' => 'Calories calculated and updated successfully']);
            
                        }
    

            // return response()->json(['data' => 'Calories calculated and inserted successfully']);
        }


        public function exercise_total_calo(Request $request)
        {
            $user_id = $request->user_id;
        
            $checkcustom_workout = DB::table('workout_history')
                ->select('workout_id', 'user_id')
                ->where('user_id', $user_id)
                ->get();
        
            $result = [];
        
            foreach ($checkcustom_workout as $workout) {
                $completedExercises = DB::table('user_exercise_complete_status')
                    ->join('exercises', 'user_exercise_complete_status.user_exercise_id', '=', 'exercises.exercise_id')
                    ->where('user_id', $user_id)
                    ->where('exercise_status', 'completed')
                    ->where('workout_id', $workout->workout_id)
                    ->select(
                        'workout_id',
                        DB::raw('SUM(exercises.exercise_calories) as total_calories'),
                        DB::raw('SUM(CAST(SUBSTRING_INDEX(exercises.exercise_rest, " ", 1) AS UNSIGNED)) as total_rest'),
                        DB::raw('COUNT(exercises.exercise_id) as exercise_count') // Add this line to count completed exercises
                    )
                    ->groupBy('workout_id')
                    ->first();
        
                if (!$completedExercises) {
                    // If $completedExercises is not found for the current workout, add a message to the result
                    $result[] = [
                        'workout_id' => $workout->workout_id,
                        'totalCalories' => $completedExercises->total_calories ?? 0,
                        'totalRestTime' => $completedExercises->total_rest ?? 0,
                        'exerciseCount' => 0, // Set exercise count to 0 if no exercises are completed
                    ];
                } else {
                    $result[] = [
                        'workout_id' => $completedExercises->workout_id,
                        'totalCalories' => $completedExercises->total_calories ?? 0,
                        'totalRestTime' => $completedExercises->total_rest ?? 0,
                        'exerciseCount' => $completedExercises->exercise_count ?? 0,
                    ];
                }
            }
        
            return response()->json(['results' => $result]);
        }
        
        public function get_exercise(Request $request){
            
        $version = $request->input('version');
        $user_id = $request->input('user_id');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        
        if($versions_current!==null || $versions_middle!==null || $versions_past!==null){
            
            if($user_id){
                $user_data = DB::table('users')->where('id',$user_id)->first();
                $gender = $user_data->gender;
                $exercise_data = DB::table('exercises')
                ->join('exercises_bodyparts', 'exercises.exercise_id', '=', 'exercises_bodyparts.exercise_id')
                 ->join('bodyparts', 'exercises_bodyparts.bodypart_id', '=', 'bodyparts.bodypart_id')
                 ->join('equipments', 'exercises.exercise_equipment', '=', 'equipments.equipment_id')
                  ->where('exercise_gender',$gender)
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
                'video' => $data->video
            ];
        }
        
        $json_data = json_encode($exercise_json); // Encode the array after the loop completes
        echo $json_data;
    
        }else{
               return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
            
        }
        public function get_all_days(Request $request){
            
        
        $user_id = $request->input('user_id');    
        $version = $request->input('version');
        $day = $request->input('day');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        
       if($day!==null){
        
        if($versions_current!==null || $versions_middle!==null || $versions_past!==null){
           $user_edit_exercises = DB::table('user_edit_exercise')
           ->where('day', $day)
           ->where('user_id', $user_id)
           ->get();
          if (!$user_edit_exercises->isEmpty()) {
                 $exercise_data = $user_edit_exercises;
            } else {
              
                 $exercise_data = DB::table('week_day_exercise')->where('day', $day)->get();  
            }
        //   dd($exercise_data);
          

        if ($exercise_data->isEmpty()) {
            return response()->json(['msg' => 'No data found.']);
        }
           $check_user = DB::table('users')->where('id', $user_id)->get(); 
            if ($check_user->isEmpty()) {
            return response()->json(['msg' => 'User not exist.']);
        }
        
        $gender = $check_user[0]->gender;
        if($gender =="Male"){
            
            
        $arr = $exercise_data[0]->male_exercise_id;
        // dd($arr);
        $title = $exercise_data[0]->title;
        $image = $exercise_data[0]->image;
        $total_coins = $exercise_data[0]->total_coins;
        $exercise_ids = json_decode($arr, true);
        $unique_exercise_ids = array_unique($exercise_ids);
        $exercise_arr = [];
        
        foreach ($unique_exercise_ids as $exercise_id) {
            $exercise_data = DB::table('exercises')->where('exercise_id', $exercise_id)->get();
            // print_r($exercise_data[0]->exercise_id);
                           $exercise_id = $exercise_data[0]->exercise_id;
                    $exercise_title = $exercise_data[0]->exercise_title;
                    $exercise_gender = $exercise_data[0]->exercise_gender;
                    $exercise_goal = $exercise_data[0]->exercise_goal;
                    $exercise_workoutarea = $exercise_data[0]->exercise_workoutarea;
                    $exercise_minage = $exercise_data[0]->exercise_minage;
                    $exercise_maxage = $exercise_data[0]->exercise_maxage;
                    $exercise_calories = $exercise_data[0]->exercise_calories;
                    $exercise_injury = $exercise_data[0]->exercise_injury;
                    $week_day = $exercise_data[0]->week_day;
                    $exercise_image = $exercise_data[0]->exercise_image;
                    $exercise_tips = $exercise_data[0]->exercise_tips;
                    $exercise_instructions = $exercise_data[0]->exercise_instructions;
                    $exercise_reps = $exercise_data[0]->exercise_reps;
                    $exercise_sets = $exercise_data[0]->exercise_sets;
                    $exercise_rest = $exercise_data[0]->exercise_rest;
                    $exercise_equipment = $exercise_data[0]->exercise_equipment;
                    $exercise_level = $exercise_data[0]->exercise_level;
                    $exercise_image_link = $exercise_data[0]->exercise_image_link;
                    $exercise_video = $exercise_data[0]->exercise_video;
                    $video = $exercise_data[0]->video;
                    $exercise_instructions = $exercise_data[0]->exercise_instructions;
                    $exercise_json[] = [
                                         'exercise_id' => $exercise_id,
                                        'exercise_title' => $exercise_title,
                                        'exercise_gender' => $exercise_gender,
                                        'exercise_goal' => $exercise_goal,
                                        'exercise_workoutarea' => $exercise_workoutarea,
                                        'exercise_minage' => $exercise_minage,
                                        'exercise_maxage' => $exercise_maxage,
                                        'exercise_calories' => $exercise_calories,
                                        'exercise_injury' => $exercise_injury,
                                        'week_day' => $week_day,
                                        'exercise_image' => $exercise_image,
                                        'exercise_tips' => $exercise_tips,
                                        'exercise_instructions' => $exercise_instructions,
                                        'exercise_reps' => $exercise_reps,
                                        'exercise_sets' => $exercise_sets,
                                        'exercise_rest' => $exercise_rest,
                                        'exercise_equipment' => $exercise_equipment,
                                        'exercise_level' => $exercise_level,
                                        'exercise_image_link' => $exercise_image_link,
                                        'exercise_video' => $exercise_video,
                                        'video' => $video
                                    ];
                                    
                                    $json_data = json_encode($exercise_json);
        
        }
        // echo $json_data;
          return response()->json([
              'title' => $title,
              'image' => $image,
              'total_coins'=>$total_coins,
              'exercises' =>$exercise_json               
           ]);
     
        // return response()->json([$exercise_arr]); // Corrected response structure
            
            
        }else{
        
         $user_female_edit_exercises = DB::table('user_edit_exercise')
           ->where('day', $day)
           ->where('user_id', $user_id)
           ->get();
          if (!$user_edit_exercises->isEmpty()) {
                 $female_exercise_data = $user_female_edit_exercises;
            } else {
              
                 $female_exercise_data = DB::table('week_day_exercise')->where('day', $day)->get();   
            }
            
            // dd($female_exercise_data);
        // $female_exercise_data = DB::table('week_day_exercise')->where('day', $day)->get();  
        $female_arr = $female_exercise_data[0]->female_exercise_id; // Corrected variable name
         $title = $female_exercise_data[0]->title;
        $image = $female_exercise_data[0]->image;
        $total_coins_female = $exercise_data[0]->total_coins;
    //   dd($female_arr);
        $female_exercise_ids = json_decode($female_arr, true); // Corrected variable name
        // dd($female_exercise_ids);
        $female_unique_exercise_ids = array_unique($female_exercise_ids); // Corrected variable name
        $female_exercise_arr = [];
        
        foreach ($female_unique_exercise_ids as $female_id) { // Corrected variable name
            $exercise_data = DB::table('exercises')->where('exercise_id', $female_id)->get();
            // $female_exercise_arr[] = $exercise_data;
                    $exercise_id = $exercise_data[0]->exercise_id;
                    $exercise_title = $exercise_data[0]->exercise_title;
                    $exercise_gender = $exercise_data[0]->exercise_gender;
                    $exercise_goal = $exercise_data[0]->exercise_goal;
                    $exercise_workoutarea = $exercise_data[0]->exercise_workoutarea;
                    $exercise_minage = $exercise_data[0]->exercise_minage;
                    $exercise_maxage = $exercise_data[0]->exercise_maxage;
                    $exercise_calories = $exercise_data[0]->exercise_calories;
                    $exercise_injury = $exercise_data[0]->exercise_injury;
                    $week_day = $exercise_data[0]->week_day;
                    $exercise_image = $exercise_data[0]->exercise_image;
                    $exercise_tips = $exercise_data[0]->exercise_tips;
                    $exercise_instructions = $exercise_data[0]->exercise_instructions;
                    $exercise_reps = $exercise_data[0]->exercise_reps;
                    $exercise_sets = $exercise_data[0]->exercise_sets;
                    $exercise_rest = $exercise_data[0]->exercise_rest;
                    $exercise_equipment = $exercise_data[0]->exercise_equipment;
                    $exercise_level = $exercise_data[0]->exercise_level;
                    $exercise_image_link = $exercise_data[0]->exercise_image_link;
                    $exercise_video = $exercise_data[0]->exercise_video;
                    $video = $exercise_data[0]->video;
                    $exercise_instructions = $exercise_data[0]->exercise_instructions;
                    $exercise_json[] = [
                                         'exercise_id' => $exercise_id,
                                        'exercise_title' => $exercise_title,
                                        'exercise_gender' => $exercise_gender,
                                        'exercise_goal' => $exercise_goal,
                                        'exercise_workoutarea' => $exercise_workoutarea,
                                        'exercise_minage' => $exercise_minage,
                                        'exercise_maxage' => $exercise_maxage,
                                        'exercise_calories' => $exercise_calories,
                                        'exercise_injury' => $exercise_injury,
                                        'week_day' => $week_day,
                                        'exercise_image' => $exercise_image,
                                        'exercise_tips' => $exercise_tips,
                                        'exercise_instructions' => $exercise_instructions,
                                        'exercise_reps' => $exercise_reps,
                                        'exercise_sets' => $exercise_sets,
                                        'exercise_rest' => $exercise_rest,
                                        'exercise_equipment' => $exercise_equipment,
                                        'exercise_level' => $exercise_level,
                                        'exercise_image_link' => $exercise_image_link,
                                        'exercise_video' => $exercise_video,
                                        'video' => $video
                                    ];
                                     $json_data = json_encode($exercise_json);
        
        }
        //  echo $json_data;
        // echo "female";
          return response()->json([
              'title' => $title,
              'image' => $image,
              'total_coins'=>$total_coins_female,
              'exercises' =>$exercise_json               
           ]);
        
        // return response()->json($female_exercise_arr); // Corrected response structure

        }

        }else{
               return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
       }else{
            return response()->json([
                'msg' => 'Please insert valid data'

            ]);
            }
        }
        
    public function custom_workout(Request $request){

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
        return response()->json([
                'msg' => 'data inserted successfully'
            ]);
        
   }        
   public function get_workout(Request $request){
          $user_id = $request->input('user_id');  
        //  $check_user = DB::table('users')->where('id', $user_id)->get(); 
        //     if ($check_user->isEmpty()) {
        //     return response()->json(['msg' => 'User not exist.']);
        // }
        $week_day_exercise = DB::table('user_custom_workouts')->where('user_id', $user_id)->get(); 
            if ($week_day_exercise->isEmpty()) {
            return response()->json(['msg' => 'data not found.']);
        }
        
       $workout_data = [];

        foreach ($week_day_exercise as $list) {
            $jsornarr_list = $list->exercise_id;
            $workout_name = $list->workout_name;
            $image = $list->image;
            $id = $list->id;
            $loop_count = 0;
        
            // Initialize array to store exercise data
            $exercise_json = [];
        
            $arr_list = json_decode($jsornarr_list, true);
            foreach ($arr_list as $val) {
                $loop_count++;
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
                'total_exercises' => $loop_count,
                'workout_name' => $workout_name,
                'image' =>   $image,
                'custom_workout_id' => $id,
                'exercise_data' => $exercise_json
            ];
        }
        
        return response()->json(['data' => $workout_data]);
       
   }
   public function delete_exercise(Request $request){
        $user_id = $request->input('user_id');
        $current_date = $request->input('current_date');
        $workout_id = $request->input('workout_id');
        
        // Delete records based on conditions
        $deletedRows = DB::table('user_exercise_complete_status')
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
   public function exercise_week_data(Request $request){
            $user_id = $request->input('user_id');  
            $check_user = DB::table('user_exercise_complete_status')->where('user_id', $user_id)->get(); 
            if ($check_user->isEmpty()) {
            return response()->json(['msg' => 'No data found']);
                }
            $sevenDaysAgo = Carbon::now()->subDays(7);
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
    public function delete_custom_workout(Request $request){

        $user_id = $request->input('user_id');
        $version = $request->input('version');
        $exercise_id = $request->input('exercise_id');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
         
        $id = $request->input('custom_workout_id');
        if($id==null){
         return response()->json([
                'msg' => "custom workout id is empty"
            ]);
        }
        
        if($version==null){
         return response()->json([
                'msg' => "version is empty"
            ]);
        }
        
        if($user_id==null){
         return response()->json([
                'msg' => "user id is empty"
            ]);
        }
        
        if($versions_current!==null || $versions_middle!==null || $versions_past!==null){
            
              $check_user = DB::table('user_custom_workouts')->where('user_id',$user_id)->where('id',$id)->get(); 
                if ($check_user->isEmpty()) {
                return response()->json(['msg' => 'data not found']);
                }
             DB::table('user_custom_workouts')->where('user_id',$user_id)->where('id',$id)->delete();
              return response()->json([
                'msg' => "data deleted successfully"
                
            ]);
        
        }else{
         return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }
    public function edit_custom_workout(Request $request){

        $user_id = $request->input('user_id');
        // $image = $request->input('image');
        $version = $request->input('version');
        $id = $request->input('custom_workout_id');
        $exercise_list = $request->input('exercises');
        $workout_name = $request->input('workout_name');
        if($user_id ==null){
             return response()->json([
                    'msg' => 'please insert user id'
                ]);
        }
         if($version ==null){
             return response()->json([
                    'msg' => 'please insert version'
                ]);
        }
         if($id ==null){
             return response()->json([
                    'msg' => 'please insert custom workout id'
                ]);
        }
         if($exercise_list ==null){
             return response()->json([
                    'msg' => 'please isnert exercises id'
                ]);
        }
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        if($versions_current!==null || $versions_middle!==null || $versions_past!==null){
            
            
            $exercise_list = $request->input('exercises');
            // $image = $request->input('image');
            
            $jsonlist = json_encode($exercise_list);
              if($request->hasfile('image')){
                    $image = $request->file('image');
                    $ext = $image->extension();
                    $myfile = time() . '.' . $ext;
                    $image->storeAs('/public/profile_image', $myfile);
                    $image->storeAs('/public/image', $myfile);
                    
                    DB::table('user_custom_workouts')
                    ->where('user_id', $user_id)
                    ->where('id', $id)
                    ->update([
                        'image' =>$myfile,
                        'exercise_id' => $jsonlist,
                    ]);
                    
              }else{
                  $myfile = "";
                  DB::table('user_custom_workouts')
                    ->where('user_id', $user_id)
                    ->where('id', $id)
                    ->update([
                        'exercise_id' => $jsonlist,
                    ]);
                  
              }
            
                 
            
            return response()->json([
                    'msg' => 'data updated successfully'
                ]);
          
            
            
            // $exercise_list = [5, 6, 7, 9,10,13,18];
            // $exercise_list = array_map('strval', $exercise_list);
            // $jsonlist = json_encode($exercise_list);
            // $data = DB::table('user_custom_workouts')
            //     ->where('user_id', $user_id)
            //     ->where('id', $id)
            //     ->first();

            // if ($data) {
            //     $json_old_exercises = $data->exercise_id;
            //     $arr_old_exercises = json_decode($json_old_exercises, true);
            //     $arr_old_exercises = array_map('strval', $arr_old_exercises);
            //     $newArray = array_merge($arr_old_exercises, $exercise_list); 
            //     $json_data_list = json_encode($newArray);
            //     DB::table('user_custom_workouts')
            //         ->where('user_id', $user_id)
            //         ->where('id', $id)
            //         ->update([
            //             'exercise_id' => $json_data_list
            //         ]);
            
            //     return response()->json([
            //         'msg' => "Data updated successfully"
            //     ]);
            // } else {
            //     return response()->json([
            //         'msg' => "Workout not found"
            //     ], 404);
            // }           
            
        }else{
           return response()->json(['msg' => 'Please update the app to the latest version.']);
        }
    }
    public function delete_completed_exercises(Request $request){
        
        $currentDay = Carbon::now()->format('l');
    //   if($currentDay =='Sunday'){
            $user_id = $request->input('user_id');
            $workout_id = $request->input('workout_id');
            // $day = $request->input('day');
            $version = $request->input('version');

     
        // if($workout_id ==null){
        //      return response()->json([
        //             'msg' => 'workout id  is required'
        //         ]);
        // }
         if($version ==null){
             return response()->json([
                    'msg' => 'version  is required'
                ]);
        }
         if($user_id ==null){
             return response()->json([
                    'msg' => ' user id  is required'
                ]);
        }
       
        $currentDate = Carbon::today();
        $oneWeekAgo = $currentDate->copy()->subDays(7);
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        if($versions_current!==null || $versions_middle!==null || $versions_past!==null){
            $days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
              $deletedRowsForDay = DB::table('user_exercise_complete_status')
                ->where('user_id', $user_id)
                ->whereDate('created_at', '>=', $oneWeekAgo) // Get records from one week ago or later
                ->whereDate('created_at', '<=', $currentDate)
                 ->whereIn('user_day', $days) // Use the array of days directly
                // ->where('exercise_status', 'completed')
                ->delete();
                
                
            
            // // If no records, return an informative message
            // if ($deletedRowsForDay->isEmpty()) {
            //     return response()->json(['msg' => 'No records found']);
            // }
            
            // Return the fetched records as JSON
            return response()->json([
                'msg' => 'data deleted successfully',
                'data' => $deletedRowsForDay
            ]);

        }else{
             return response()->json(['msg' => 'Please update the app to the latest version.']);
        }
           
    //   }else{
    //       return response()->json(['msg' => 'today is not sunday']);  
    //   }
       

    }
     public function get_challenges_exercises(Request $request){
        $version = $request->input('version');
        $user_id = $request->input('user_id');
         if($version ==null){
             return response()->json([
                    'msg' => 'version  is required'
                ]);
        }
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        if($versions_current!==null || $versions_middle!==null || $versions_past!==null){
            
      
                
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
                        
                        $json_data = json_encode($json_data);
                        echo $json_data;
            
        }else{
           return response()->json(['msg' => 'Please update the app to the latest version.']);
        }

     
    }
    public function save_challenges_exercises(Request $request){
        
          
            $user_id = $request->input('user_id');
            $version = $request->input('version');
            $challenge_workout_id = $request->input('id');

     
        if($user_id ==null){
             return response()->json([
                    'msg' => 'workout id  is required'
                ]);
        }
         if($version ==null){
             return response()->json([
                    'msg' => 'version  is required'
                ]);
        }
         if($challenge_workout_id ==null){
             return response()->json([
                    'msg' => ' user id  is required'
                ]);
        }
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        if($versions_current!==null || $versions_middle!==null || $versions_past!==null){
             $challenge_data = DB::table('challenges')->where('id', $challenge_workout_id)->first();
             if($challenge_data){
                $user_ids = $challenge_data->user_id;
                if($user_ids){
                       $get_all_user_ids = DB::table('challenges')
                        ->where('id', '!=', $challenge_workout_id)
                        ->get();
                        
                        foreach($get_all_user_ids as $data){
                            $json_arr = $data->user_id;
                            $id = $data->id;
                            
                            $check_data =  DB::table('challenges')
                                ->where('id',$id)
                                ->get();
                                // print_r($check_data[0]->user_id);
                                
                            
                            if($check_data[0]->user_id){
                                // echo "exist";
                                //  echo $id;
                                $simpe_arr = json_decode($json_arr);
                                if (($key = array_search($user_id, $simpe_arr)) !== false) {
                                unset($simpe_arr[$key]);
                                }
                                $simpe_arr = array_values($simpe_arr);
                                $jsonString = json_encode($simpe_arr);
                                DB::table('challenges')->where('id',$id)->update([
                                'user_id' => $jsonString
                                ]);    
                            }else{
                                // echo "not exist";
                            }
                        }
                     $simpe_arr = json_decode($user_ids);
                     $simpe_arr[] = $user_id;
                     $uniqueArray = array_unique($simpe_arr);
                     $jsonString = json_encode($uniqueArray);
                     DB::table('challenges')->where('id',$challenge_workout_id)->update([
                                'user_id' => $jsonString,
    
                             ]);
                               return response()->json(['msg' => 'workout data saved']);
                    
                }else{
                          $array[] = $user_id; 
                          $jsonString = json_encode($array);
                          echo $jsonString;
                         DB::table('challenges')->where('id',$challenge_workout_id)->update([
                                'user_id' => $jsonString,
    
                             ]);
                               return response()->json(['msg' => 'workout data saved']);
                }
                 
             }else{
                   return response()->json(['msg' => 'data not found']);
                
             }
        }else{
        return response()->json(['msg' => 'Please update the app to the latest version.']);
        }    
    }
     public function save_challenge_status(Request $request)
    {
        $user_details_list = $request->input('user_details');
        $insertedData = [];

        foreach ($user_details_list as $user_details) {
            // Check if all required keys exist in $user_details
            if (
                isset($user_details['user_id']) &&
                isset($user_details['workout_id']) &&
                isset($user_details['user_exercise_id']) &&
                isset($user_details['user_day'])
            ) {
                // Check if the data already exists
                $existingRecord = DB::table('user_workout_challenge_status')
                    ->where('user_id', $user_details['user_id'])
                    ->where('workout_id', $user_details['workout_id'])
                    ->where('user_exercise_id', $user_details['user_exercise_id'])
                    ->where('user_day', $user_details['user_day'])
                    ->first();
                if ($existingRecord) {
                    return response()->json(['msg' => 'User challenge exercise allready exist']);
                }

                if (!$existingRecord) {
                    // Perform insert with 'undone' status
                    $insertedRecord = DB::table('user_workout_challenge_status')->insertGetId([
                        'user_id' => $user_details['user_id'],
                        'workout_id' => $user_details['workout_id'],
                        'user_exercise_id' => $user_details['user_exercise_id'],
                        'user_day' => $user_details['user_day'],
                        'exercise_status' => 'undone',
                        'status' => 'inactive',
                    ]);

                    // Fetch the inserted record and add it to the result array
                    $insertedData[] = DB::table('user_workout_challenge_status')
                        ->where('id', $insertedRecord)
                        ->first();
                }
            } else {
                $exercise_data = DB::table('user_workout_challenge_status')
                    ->select('*')
                    ->where('user_id', isset($user_details['user_id']))
                    ->get();
                return response()->json(['msg' => 'Required keys are missing in user_details',]);
            }
        }

        return response()->json(['msg' => 'Exercise Status for All Users Inserted Successfully', 'inserted_data' => $insertedData]);
    }
       public function get_challenge_exercises_status(Request $request)
    {
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        $user_id = $request->user_id;
        $workout_id = $request->workout_id;
        $user_day = $request->user_day;
        if ($versions_current) {
            $user_details = DB::table('user_workout_challenge_status')
                ->select('*')
                ->where('user_id', $user_id)
                ->where('workout_id', $workout_id)
                ->where('user_day', $user_day)
                ->get();
            return response()->json([
                'user_details' => $user_details
            ]);

        } elseif ($versions_middle) {
            $user_details = DB::table('user_workout_challenge_status')
                ->select('*')
                ->where('user_id', $user_id)
                ->where('workout_id', $workout_id)
                ->where('user_day', $user_day)
                ->get();
            return response()->json([
                'user_details' => $user_details
            ]);
        } elseif ($versions_past) {
            $user_details = DB::table('user_workout_challenge_status')
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
    
       public function update_challenge_status(Request $request)
    {
       
        $day = $request->day;
        $id = $request->id;
        // $challenge_workout_id = $request->challenge_workout_id;
        $workout_id = $request->workout_id;
        
        $challenge_workout_id = $workout_id;
        
        $user_id = $request->user_id;
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

        $status = DB::table('user_workout_challenge_status')
            ->select('exercise_status')
            ->where('id', $id)
            ->first();
            
           
            
         


        if ($versions_current) {
            if ($status) {
                // dd("current verison");
                if ($status->exercise_status == 'undone') {
                    DB::table('user_workout_challenge_status')
                        ->where('id', $id)
                        ->update(['exercise_status' => 'completed']);

                    // Check for all exercises completed for a particular day
                        $day_statuses = DB::table('user_workout_challenge_status')
                            ->select('user_day', 'exercise_status')
                            ->where('user_day', $day)
                            ->where('workout_id', $workout_id)
                            ->where('user_id', $user_id)
                            ->get();

                    $all_completed = $day_statuses->every(function ($day_status) {
                        return $day_status->exercise_status == 'completed';
                        
                    });
                    
                    DB::table('user_workout_challenge_status')
                    ->where('user_id',$user_id)
                    ->where('status','active')
                    ->update(['status' =>'inactive']);

                    if ($all_completed) {
                      
                        DB::table('user_workout_challenge_status')
                            ->where('user_day', $day)
                            ->where('workout_id', $workout_id)
                            ->where('user_id', $user_id)
                             ->update([
                                'final_status' => 'allcompleted',
                                'status' => 'active'
                                ]);
                                
                          DB::table('user_workout_challenge_status')
                            ->where('user_id',$user_id)
                            ->where('status','inactive')
                            ->where('final_status','allcompleted')
                            ->delete();
                            
                       
                            
                        // Add a response here if needed for successful update of final_status
                    
                     $challenge_data = DB::table('challenges')->where('id', $challenge_workout_id)->first();
                         if($challenge_data){
                                    $user_ids = $challenge_data->user_id;
                                    if($user_ids){
                                           $get_all_user_ids = DB::table('challenges')
                                            ->where('id', '!=', $challenge_workout_id)
                                            ->get();
                                            //removeupdate from other workout
                                            foreach($get_all_user_ids as $data){
                                             
                                                $json_arr = $data->user_id;
                                                $other_workout_id = $data->id;
                                                $check_data =  DB::table('challenges')
                                                    ->where('id',$other_workout_id)
                                                    ->get();
                                                    
                                                if($check_data[0]->user_id){
                                                    $simpe_arr = json_decode($json_arr);
                                                   
                                                   if (($key = array_search($user_id, $simpe_arr)) !== false) {
                                                                unset($simpe_arr[$key]);
                                                            }
                                                    $simpe_arr = array_values($simpe_arr);
                                                    $jsonString = json_encode($simpe_arr);
                                                   
                                                    DB::table('challenges')->where('id',$other_workout_id)->update([
                                                    'user_id' => $jsonString
                                                    ]);    
                                                }
                                            }
                                            //update
                                         $get_simple_arr = json_decode($user_ids);
                                         $get_simple_arr[] = $user_id;
                                         $uniqueArray = array_unique($get_simple_arr);
                                         $Stringdata = json_encode($uniqueArray);
                                         DB::table('challenges')->where('id',$challenge_workout_id)->update([
                                                    'user_id' => $Stringdata,
                                                 ]);
                                        
                                    }else{
                                        
                                              $array[] = $user_id; 
                                              $jsonString = json_encode($array);
                                              echo $jsonString;
                                             DB::table('challenges')->where('id',$challenge_workout_id)->update([
                                                    'user_id' => $jsonString,
                        
                                                 ]);
                                              
                                    }
                                     
                                 }else{
                                    //   return response()->json(['msg' => 'data not found']);
                                    
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
        } elseif ($versions_middle) {
            if ($status) {
                if ($status->exercise_status == 'undone') {
                    DB::table('user_workout_challenge_status')
                        ->where('id', $id)
                        ->update(['exercise_status' => 'completed']);

                    // Check for all exercises completed for a particular day
                    $day_statuses = DB::table('user_workout_challenge_status')
                        ->select('user_day', 'exercise_status')
                        ->where('user_day', $day)
                        ->where('workout_id', $workout_id)
                        ->where('user_id', $user_id)
                        ->get();

                    $all_completed = $day_statuses->every(function ($day_status) {
                        return $day_status->exercise_status == 'completed';
                        
                    });

                    if ($all_completed) {
                        DB::table('user_workout_challenge_status')
                            ->where('user_day', $day)
                            ->where('workout_id', $workout_id)
                            ->where('user_id', $user_id)
                            ->update([
                                'final_status' => 'allcompleted',
                                'status' => 'active'
                                
                                ]);
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
                    DB::table('user_workout_challenge_status')
                        ->where('id', $id)
                        ->update(['exercise_status' => 'completed']);

                    // Check for all exercises completed for a particular day
                    $day_statuses = DB::table('user_workout_challenge_status')
                        ->select('user_day', 'exercise_status')
                        ->where('user_day', $day)
                        ->where('workout_id', $workout_id)
                        ->where('user_id', $user_id)
                        ->get();

                    $all_completed = $day_statuses->every(function ($day_status) {
                        return $day_status->exercise_status == 'completed';
                    });

                    if ($all_completed) {
                        DB::table('user_workout_challenge_status')
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
     public function user_challenge_exercise_details(Request $request)
    {
        $id = $request->id;
        $workout_id = $request->workout_id;
        $user_details = DB::table('user_workout_challenge_status')
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
    public function user_lets_go(Request $request){
        $version = $request->input('version');
        $logintype = $request->input('login_type');
        $deviceid =  $request->input('deviceid');
        $socialtoken = $request->input('socialtoken');
        $deviceToken = $request->input('devicetoken');  //adhf
        $platform = $request->input('platform');
        // $device_id = $request->input('device_id');

        $loginToken = rand(100000000000, 999999999999999);

        $userWithDevice = DB::table('users')->where('device_id', $deviceid)->first();
        $userWithDeviceEmail = DB::table('users')->select('email','name')->where('device_id', $deviceid)->first();
        // dd($userWithDevice);
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        if ($versions_current) {
            
           
           if ($userWithDeviceEmail && $userWithDeviceEmail->email !== null){
                
                  return response()->json([
                    'id' => $userWithDevice->id,
                    'msg' => 'User already registered',
                    'profile_compl_status' =>$userWithDevice->profile_compl_status,
                    'deviceid' => $userWithDevice->device_id, 
                    'temp' => true,
                ]);
                
            }elseif($userWithDevice){
                 return response()->json([
                    'id' => $userWithDevice->id,
                    'msg' => 'User already exists',
                    'profile_compl_status' =>$userWithDevice->profile_compl_status,
                    'deviceid' => $userWithDevice->device_id, 
                    'temp' => true,
                ]);
                
            }else{
                 DB::table('users')->insert([
                    'login_token' => $loginToken,
                    'platform' => $platform,
                    'device_id'=>$deviceid,
                    'device_token' =>$deviceToken,
                    'profile_compl_status'=>0,
                    'status'=>0,
                ]);
                
                  $user_details = DB::table('users')->where('device_id', $deviceid)->first();
                    return response()->json([
                        'id' => $user_details->id,
                        'msg' => 'user registerd successfully',
                       'profile_compl_status' =>$user_details->profile_compl_status,
                        'deviceid' => $user_details->device_id, 
                        'temp' =>true,
                    ]); 
            }

        } elseif ($versions_middle) {
               if ($userWithDevice) {
               return response()->json([
                    'id' => $userWithDevice->id,
                    'msg' => 'User already exists',
                    'profile_compl_status' =>$userWithDevice->profile_compl_status,
                    'deviceid' => $userWithDevice->device_id,
                    'temp' =>true,
                ]);
                
            }else{
                 DB::table('users')->insert([
                    'login_token' => $loginToken,
                    'platform' => $platform,
                    'device_id'=>$deviceid,
                    'device_token' =>$deviceToken,
                    'profile_compl_status'=>0,
                    'status'=>0,
                ]);
                
                  $user_details = DB::table('users')->where('device_id', $deviceid)->first();
                    return response()->json([
                        'id' => $user_details->id,
                        'msg' => 'user registerd successfully',
                       'profile_compl_status' =>$user_details->profile_compl_status,
                        'deviceid' => $user_details->device_id, 
                        'temp' =>true,
                    ]); 
            }
            
        } elseif ($versions_past) {
              if ($userWithDevice) {
               return response()->json([
                    'id' => $userWithDevice->id,
                    'msg' => 'User already exists',
                    'profile_compl_status' =>$userWithDevice->profile_compl_status,
                    'deviceid' => $userWithDevice->device_id, 
                    'temp' =>true,
                ]);
                
            }else{
                 DB::table('users')->insert([
                    'login_token' => $loginToken,
                    'platform' => $platform,
                    'device_id'=>$deviceid,
                    'device_token' =>$deviceToken,
                    'profile_compl_status'=>0,
                    'status'=>0,
                ]);
                
                  $user_details = DB::table('users')->where('device_id', $deviceid)->first();
                    return response()->json([
                        'id' => $user_details->id,
                        'msg' => 'user registerd successfully',
                       'profile_compl_status' =>$user_details->profile_compl_status,
                        'deviceid' => $user_details->device_id, 
                        'temp' =>true,
                    ]); 
            }
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
    }
    
    public function user_exercise_complete_status(Request $request){
        
        $version = $request->input('version');
        $user_id = $request->input('user_id');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        if ($versions_current) {
            $unique_workout_ids = DB::table('user_exercise_complete_status')
                ->where('user_id', $user_id)
                ->distinct()
                ->pluck('workout_id')
                ->toArray();
            
        
            $unique_user_days = DB::table('user_exercise_complete_status')
                ->where('user_id', $user_id)
                ->distinct()
                ->pluck('user_day')
                ->toArray();
                
            $all_unique_data = [];
            
                foreach ($unique_workout_ids as $workout_id) {
                    if ($workout_id <= 0) {
                        continue;
                    }
                    $workout_data = [];
                    $maxNumber = 0;
                
                    foreach ($unique_user_days as $user_day) {
                        $unique_data = DB::table('user_exercise_complete_status')
                            ->where('user_id', $user_id)
                            ->where('workout_id', $workout_id)
                            ->where('user_day', $user_day)
                            ->where('final_status', 'allcompleted')
                            ->first();
                
                        if ($unique_data) {
                            $days[] = $unique_data->user_day;
                            $maxNumber = max($maxNumber, $unique_data->user_day);
                            $workout_data[$unique_data->user_day] = [
                                'day' => $unique_data->user_day,
                                'final_status' => $unique_data->final_status,
                            ];
                        }
                    }
                    $completeSequence = range(1, $maxNumber);
                    $completeDays = array_values($completeSequence);
                    foreach ($completeDays as $day) {
                        if (!array_key_exists($day, $workout_data)) {
                            $workout_data[$day] = [
                                'day' => $day,
                                'final_status' => 'allcompleted',
                            ];
                        }
                    }
                    ksort($workout_data);
                
                    if (!empty($workout_data)) {
                        $all_unique_data[] = [
                            'workout_id' => $workout_id,
                            'workout_data' => array_values($workout_data), // Reset array keys
                        ];
                    }
                }
                
                return response()->json($all_unique_data);

        } elseif ($versions_middle) {
             $unique_workout_ids = DB::table('user_exercise_complete_status')
                ->where('user_id', $user_id)
                ->distinct()
                ->pluck('workout_id')
                ->toArray();
            
        
            $unique_user_days = DB::table('user_exercise_complete_status')
                ->where('user_id', $user_id)
                ->distinct()
                ->pluck('user_day')
                ->toArray();
                
            $all_unique_data = [];
            
                foreach ($unique_workout_ids as $workout_id) {
                    if ($workout_id <= 0) {
                        continue;
                    }
                    $workout_data = [];
                    $maxNumber = 0;
                
                    foreach ($unique_user_days as $user_day) {
                        $unique_data = DB::table('user_exercise_complete_status')
                            ->where('user_id', $user_id)
                            ->where('workout_id', $workout_id)
                            ->where('user_day', $user_day)
                            ->where('final_status', 'allcompleted')
                            ->first();
                
                        if ($unique_data) {
                            $days[] = $unique_data->user_day;
                            $maxNumber = max($maxNumber, $unique_data->user_day);
                            $workout_data[$unique_data->user_day] = [
                                'day' => $unique_data->user_day,
                                'final_status' => $unique_data->final_status,
                            ];
                        }
                    }
                    $completeSequence = range(1, $maxNumber);
                    $completeDays = array_values($completeSequence);
                    foreach ($completeDays as $day) {
                        if (!array_key_exists($day, $workout_data)) {
                            $workout_data[$day] = [
                                'day' => $day,
                                'final_status' => 'allcompleted',
                            ];
                        }
                    }
                    ksort($workout_data);
                
                    if (!empty($workout_data)) {
                        $all_unique_data[] = [
                            'workout_id' => $workout_id,
                            'workout_data' => array_values($workout_data), // Reset array keys
                        ];
                    }
                }
                
                return response()->json($all_unique_data);
            
            
            
        } elseif ($versions_past) {
         $unique_workout_ids = DB::table('user_exercise_complete_status')
                ->where('user_id', $user_id)
                ->distinct()
                ->pluck('workout_id')
                ->toArray();
            
        
            $unique_user_days = DB::table('user_exercise_complete_status')
                ->where('user_id', $user_id)
                ->distinct()
                ->pluck('user_day')
                ->toArray();
                
            $all_unique_data = [];
            
                foreach ($unique_workout_ids as $workout_id) {
                    if ($workout_id <= 0) {
                        continue;
                    }
                    $workout_data = [];
                    $maxNumber = 0;
                
                    foreach ($unique_user_days as $user_day) {
                        $unique_data = DB::table('user_exercise_complete_status')
                            ->where('user_id', $user_id)
                            ->where('workout_id', $workout_id)
                            ->where('user_day', $user_day)
                            ->where('final_status', 'allcompleted')
                            ->first();
                
                        if ($unique_data) {
                            $days[] = $unique_data->user_day;
                            $maxNumber = max($maxNumber, $unique_data->user_day);
                            $workout_data[$unique_data->user_day] = [
                                'day' => $unique_data->user_day,
                                'final_status' => $unique_data->final_status,
                            ];
                        }
                    }
                    $completeSequence = range(1, $maxNumber);
                    $completeDays = array_values($completeSequence);
                    foreach ($completeDays as $day) {
                        if (!array_key_exists($day, $workout_data)) {
                            $workout_data[$day] = [
                                'day' => $day,
                                'final_status' => 'allcompleted',
                            ];
                        }
                    }
                    ksort($workout_data);
                
                    if (!empty($workout_data)) {
                        $all_unique_data[] = [
                            'workout_id' => $workout_id,
                            'workout_data' => array_values($workout_data), // Reset array keys
                        ];
                    }
                }
                
                return response()->json($all_unique_data);       
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }

    }
    public function search_wokrouts(Request $request){
        $search_key = $request->input('search_key');
        echo $search_key;
        $data = DB::table('workouts')
                // ->orWhere('workout_gender', 'LIKE', '%' . $search_key . '%')
                ->Where('workout_injury', 'LIKE', '%' . $search_key . '%')
                // ->orWhere('workout_maxage', 'LIKE', '%' . $search_key . '%')
                // Add more columns as needed
                ->get();
         return response()->json($data);
        
    }
    
    public function user_custom_diets(Request $request){
        $version = $request->input('version');
        $user_id = $request->input('user_id');
        $diet_id = $request->input('diet_id');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        if ($versions_current) {
            
            DB::table("user_custom_diets")->insert([

                'user_id' => $user_id,
                'diet_id' => $diet_id
                ]);
            return response()->json([
                'msg' => 'user custom diet added.'

            ]);

        } elseif ($versions_middle) {
             
            DB::table("user_custom_diets")->insert([

                'user_id' => $user_id,
                'diet_id' => $diet_id
                ]);
            return response()->json([
                'msg' => 'user custom diet added.'

            ]);
            
            
        } elseif ($versions_past) {
             
            DB::table("user_custom_diets")->insert([

                'user_id' => $user_id,
                'diet_id' => $diet_id
                ]);
            return response()->json([
                'msg' => 'user custom diet added.'

            ]);
           
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
        
        
    }
    public function get_custom_diets(Request $request){
        $version = $request->input('version');
        $user_id = $request->input('user_id');
        $diet_id = $request->input('diet_id');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        if ($versions_current) {
            
            $data = DB::table("user_custom_diets")
            ->join('diets', 'diets.diet_id', '=', 'user_custom_diets.diet_id')
            ->where('user_custom_diets.user_id', $user_id)
            ->get(); 
            return response()->json([
                'msg' => $data

            ]);

        } elseif ($versions_middle) {
             
            $data = DB::table("user_custom_diets")
            ->join('diets', 'diets.diet_id', '=', 'user_custom_diets.diet_id')
            ->where('user_custom_diets.user_id', $user_id)
            ->get(); 
            return response()->json([
                'msg' => $data

            ]);
            
            
        } elseif ($versions_past) {
             
            $data = DB::table("user_custom_diets")
            ->join('diets', 'diets.diet_id', '=', 'user_custom_diets.diet_id')
            ->where('user_custom_diets.user_id', $user_id)
            ->get(); 
            return response()->json([
                'msg' => $data

            ]);
           
        } else {
            return response()->json([
                'msg' => 'Please update the app to the latest version.'

            ]);
        }
        
        
    }
    public function send_voucher(Request $request){
        $email = $request->email;
        $message = $request->message;
        $amount = $request->amount;
        $title = $request->title;
        $image = $request->image;
        
        //dd(print_r($request));
        $data = ['title' => $title, 'email' => $email, 'image' => $image, 'amount' => $amount,'message' => $message ];

            Mail::send('voucher_email_body', ['data' => $data], function ($message) use ($email) {
                $message->to($email)->subject('Fitme voucher ');
             
            });
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
    
    public function get_coin_data(Request $request){
        
          $user_id = $request->user_id;
          $data = DB::table('wallet')->where('user_id',$user_id)->first();
                 
         return response()->json($data);
        
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
    public function check_condition(Request $request) {
    $version = $request->input('version');
    $user_id = $request->input('user_id');
    $check_data = DB::table('users')->where('id', $user_id)->first();
    $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
    $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
    $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

    if ($versions_current || $versions_middle || $versions_past) {
        if ($check_data) {
            if ($check_data->term_and_conditions == 'accepted') {
                return response()->json([
                    'term_conditon' => "accepted",
                    'location' => $check_data->country
                ]);
            } else {
                return response()->json([
                    'term_conditon' => $check_data->term_and_conditions,
                    'location' => $check_data->country
                ]);
            }
        } else {
            return response()->json([
                'msg' => 'user not found'
            ]);
        }
    } else {
        return response()->json([
            'msg' => 'Please update the app to the latest version.'
        ]);
    }
}    
   public function condition(Request $request){
       
    $version = $request->input('version');
    $user_id = $request->input('user_id');
    $term_conditions = $request->input('term_conditons');
    $country = $request->input('country');
    $check_data = DB::table('users')->where('id', $user_id)->first();
    $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
    $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
    $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

    if ($versions_current || $versions_middle || $versions_past) {
        if ($check_data) {
            DB::table('users')->where('id',$user_id)->update([
                'term_and_conditions'=> $term_conditions,
                'country' => $country,
                ]);
             return response()->json([
                'msg' => 'data updated'
            ]);
           
        } else {
            return response()->json([
                'msg' => 'user not found'
            ]);
        }
    } else {
        return response()->json([
            'msg' => 'Please update the app to the latest version.'
        ]);
    }
       
   }
//   public function insert_coins(Request $request)
// {
//     $user_id = $request->user_id;
//     $user_day = $request->user_day;

//     $check_data = DB::table('event_exercise_completion_status')
//         ->where('user_id', $user_id)
//         ->where('user_day', $user_day)
//         ->where('exercise_status', 'completed')
//         ->first();
        
        

//     if ($check_data) {
        
//         $coins = $check_data->fit_coins;

//         $skip_status = DB::table('event_exercise_completion_status')
//             ->where('user_id', $user_id)
//             ->where('user_day', $user_day)
//             ->where('exercise_status', 'completed')
//             ->whereNotNull('skip_status')
//             ->sum('skip_status');
     

//         $prev_status = DB::table('event_exercise_completion_status')
//             ->where('user_id', $user_id)
//             ->where('user_day', $user_day)
//             ->where('exercise_status', 'completed')
//             ->whereNotNull('prev_status')
//             ->sum('prev_status');

//         $next_status = DB::table('event_exercise_completion_status')
//             ->where('user_id', $user_id)
//             ->where('user_day', $user_day)
//             ->where('exercise_status', 'completed')
//             ->whereNotNull('next_status')
//             ->sum('next_status');

//         $total_mineus_coins = $next_status + $prev_status + $skip_status;
//         $total_coin_add = $coins - $total_mineus_coins;
       

//         DB::table('users')->where('id', $user_id)->update([
//             'fit_coins' => DB::raw('fit_coins + ' . $total_coin_add),
//         ]);

//         DB::table('event_exercise_completion_status')
//             ->where('user_id', $user_id)
//             ->where('user_day', $user_day)
//             // ->where('exercise_status', 'completed')
//             ->update(['today_earning' => $total_coin_add]);

//         return response()->json([
//             'msg' => 'coin added successfully'
//         ]);
//     }

//     return response()->json([
//         'msg' => 'no data found'
//     ]);
// }
    public function insert_coins(Request $request)
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
                
                // dd($check_data);
        
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
                            'info' =>$type,
                            'current_day' => $user_day,
                            ]);
                            
                             if ($type == "cardio") {
                                //  dd($type);
                                  DB::table('all_history')->insert([
                                    'user_id' =>$user_id,
                                    'cardio_exercise_status' =>'done',
                                    'day' => $user_day,
                                    'fit_coins'  =>$total_coin_add
                                    ]);
                            } else {
                                    // dd($type);
                                  DB::table('all_history')->insert([
                                    'user_id' =>$user_id,
                                    'event_exercise_status' =>'done',
                                    'day' => $user_day,
                                    'fit_coins'  =>$total_coin_add
                                    ]);
                            }
        
                
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

    public function get_termconditon_data(Request $request){
    $version = $request->input('version');
    if($version==null){
         return response()->json([
                  'data' =>'please submit version'
                    ]);
        
    }
    $check_data = DB::table('term_of_condition')->get();
    $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
    $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
    $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

    if ($versions_current || $versions_middle || $versions_past) {
        if ($check_data) {
              return response()->json([
                      'data' => $check_data
                    ]);
         }
        
            return response()->json([
                'data' => 'data not found'
            ]);
        
    } else {
        return response()->json([
            'msg' => 'Please update the app to the latest version.'
        ]);
    }

   }
   public function reset_leaderboard(Request $request){
    $version = $request->input('version');
    $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
    $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
    $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

    if ($versions_current || $versions_middle || $versions_past) {
            $today = Carbon::now();
          $dayOfWeek = $today->format('l');

           
         if ($dayOfWeek === 'Sunday') {
             DB::table('users')->update([
                 'fit_coins',0
                 ]);
            return response()->json([
                'msg' => 'Data has been reset successfully'
            ]);
        } else {
            return response()->json([
                'msg' => 'Data reset was not performed'
            ]);
        }
        
        
    } else {
        return response()->json([
            'msg' => 'Please update the app to the latest version.'
        ]);
    }
        
       
   }
   
   public function get_banners(Request $request){
       
    $version = $request->input('version');
    if($version==null){
         return response()->json([
           'msg' => 'version is required'
        ]);
        
    }
    
    $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
    $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
    $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

    if ($versions_current || $versions_middle || $versions_past) {
         $data = DB::table('banners')->get();
         
            return response()->json([
                'data' => $data
            ]);

    } else {
        return response()->json([
            'msg' => 'Please update the app to the latest version.'
        ]);
    }

   }
public function add_edit_exercises(Request $request)
{
    $version = $request->input('version');
    $user_id = $request->input('user_id');
    $exercise_id = $request->input('exercise_id');

    if ($exercise_id == null || !is_array($exercise_id)) {
        return response()->json([
            'msg' => 'exercise id is required and should be an array'
        ]);
    }

    $day = $request->input('day');
    $image = $request->input('image');
    $title = $request->input('title');
    $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
    $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
    $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

    if ($versions_current || $versions_middle || $versions_past) {
        $check_gender = DB::table('users')->where('id', $user_id)->first();

        if (!$check_gender) {
            return response()->json([
                'msg' => 'user not exist'
            ]);
        }

        $data = DB::table('user_edit_exercise')->where('day', $day)->where('user_id', $user_id)->first();

        $gender = $check_gender->gender;
        $column = $gender == 'Male' ? 'male_exercise_id' : 'female_exercise_id';

        $jsonData = json_encode($exercise_id);

        if (!$data) {
            // Insert new record
            DB::table('user_edit_exercise')->insert([
                'user_id' => $user_id,
                $column => $jsonData,
                'day' => $day,
                'image' => $image,
                'title' => $title,
                'total_coins' => 50,
            ]);
            return response()->json([
                'msg' => 'exercise inserted successfully'
            ]);
        }

        // Update existing record
        DB::table('user_edit_exercise')->where('day', $day)->where('user_id', $user_id)->update([
            $column => $jsonData
        ]);

        return response()->json([
            'msg' => 'exercise updated successfully'
        ]);
    } else {
        return response()->json([
            'msg' => 'Please update the app to the latest version.'
        ]);
    }
}
public function update_name_email(Request $request)

{
  
    $version = $request->input('version');
    $user_id = $request->input('user_id');
    $name = $request->input('name');
    $email = $request->input('email');

    $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
    $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
    $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

   
        $check_user = DB::table('users')->where('id', $user_id)->first();

        if (!$check_user) {
            return response()->json([
                'msg' => 'user not exist'
            ]);
        }
        if($check_user->name==null){
            
            DB::table('users')->where('id',$user_id)->update([
                'name'=> $name
                ]);
        }
         if($check_user->email==null){
             
             $check_email = DB::table('users')->where('email', $email)->first();
             if($check_email){
                return response()->json([
                    'msg' => 'email alrady exist'
                ]);
                 
             }
             
             
             DB::table('users')->where('id',$user_id)->update([
                'email'=> $email
                ]);
        }

        return response()->json([
            'msg' => 'data updated successfully'
        ]);
 
}

public function get_all_weekday_exercise(Request $request)
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
                // dd("sec");
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
                if (!is_null($exercise_ids)) {

                $unique_exercise_ids = array_unique($exercise_ids);
            } else {
    
                $unique_exercise_ids = []; 
            }
 
            // $exercise_ids = is_array($exercise_ids) ? $exercise_ids : [];
            
            // Now apply array_unique safely
            // $unique_exercise_ids = array_unique($exercise_ids);
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
 public function delete_data(Request $request){
      
        $status = $request->input('status');
        $user_id = $request->input('user_id');
        if(empty($user_id)){
              return response()->json(['msg' => 'user id is required']);
        }
        if(empty($status)){
              return response()->json(['msg' => 'status is required']);
        }
        
        if($status='delete'){
            DB::table('fitme_event')
            ->where('user_id',$user_id)
            ->delete();
            return response()->json(['msg' => 'plan deleted successfully']);
        }else{
            return response()->json(['msg' => 'status not match']);
        }
        
    }

 public function get_apikeys(Request $request){
      $version = $request->input('version');
    if($version==null){
         return response()->json([
           'msg' => 'version is required'
        ]);
        
    }
    
    $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
    $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
    $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

    if ($versions_current || $versions_middle || $versions_past) {
        
            $data = DB::table('intro_videos')->get()->map(function ($item) {
            $item->api_url = 'https://map-geocoding.p.rapidapi.com/json';
            return $item;
        });
        
        return response()->json(['data' => $data]);
  
  
    }else {
        return response()->json([
            'msg' => 'Please update the app to the latest version.'
        ]);
    
        
    }
 }
 public function custom_dialog(Request $request){
    $version = $request->input('version');
    $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
    $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
    $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();

    if ($versions_current === null && $versions_middle === null && $versions_past === null) {
        
       return response()->json([
          'msg' => 'Please update the app to the latest version.'
        ]);
        
    }
      $data =DB::table('custom_dialog')->get();
          return response()->json([
            'data' => $data]);
            echo "adfoias";
     
 }
 
 public function well_known_assetlinks_json(){
     $data[] = [
            "relation" => ["delegate_permission/common.handle_all_urls"],
            "target"   => [
                "namespace"                =>"android_app",
                "package_name"             => "fitme.health.fitness.homeworkouts.equipment",
                "sha256_cert_fingerprints" => [
                    "D2:6E:2A:67:A5:54:8E:39:6B:DF:8F:A4:BA:39:C3:6F:59:0C:DF:EC:B1:ED:E0:20:20:9D:76:02:D7:4F:AE:67"
                    ]
            ]
        ];
        
        return response()->json($data);
     
 }
 
    public function apple_app_site_association(){
     return '{"applinks":{"apps":[],"details":[{"appID":"277GPQ33HC.fitme.health.fitness.homeworkouts.equipment
","paths":["NOT /_/*","/*"]}]}}';
 }
 
 
    public function delete_wallet_data(){
     DB::table('wallet')->truncate();
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
    
    public function get_exercise_history(Request $request)
      {
        $version = $request->input('version');
        $user_id = $request->input('user_id');
        $date = $request->input('date');
    
        if (!$user_id) {
            return response()->json(['message' => 'User ID not found'], 400);
        }
    
        if (!$version) {
            return response()->json(['message' => 'Version not found'], 400);
        }
    
        // Fetch meditation (cardio) exercises
        $meditation_ex = DB::table('user_exercise_complete_status')
            ->join('exercises', 'user_exercise_complete_status.user_exercise_id', '=', 'exercises.exercise_id')
            ->where('user_exercise_complete_status.user_id', $user_id)
            ->where('user_exercise_complete_status.type', 'cardio')
            ->where('user_exercise_complete_status.exercise_status', 'completed')
            ->whereDate('user_exercise_complete_status.created_at', $date)
            ->select(
                // 'user_exercise_complete_status.*',
                'exercises.exercise_title as exercise_name',
                'exercises.exercise_image',
                'exercises.exercise_calories'
            )
            ->get();
    
        // Meditation summary
        $summary = DB::table('user_exercise_complete_status')
            ->join('exercises', 'user_exercise_complete_status.user_exercise_id', '=', 'exercises.exercise_id')
            ->where('user_exercise_complete_status.user_id', $user_id)
            ->where('user_exercise_complete_status.type', 'cardio')
            ->where('user_exercise_complete_status.exercise_status', 'completed')
            ->whereDate('user_exercise_complete_status.created_at', $date)
            ->select(
                DB::raw('SUM(exercises.exercise_calories) as total_calories'),
                DB::raw('TIMESTAMPDIFF(SECOND, MIN(user_exercise_complete_status.created_at), MAX(user_exercise_complete_status.created_at)) as total_time_seconds')
            )
            ->first();
    
        // Format time
        $summary->formatted_time = gmdate('H:i:s', $summary->total_time_seconds ?? 0);
    
        $meditation_response = [
            'summary' => [
                'total_calories' => $summary->total_calories ?? 0,
                'total_time_seconds' => $summary->total_time_seconds ?? 0,
                'formatted_time' => $summary->formatted_time,
            ],
            'meditation_data' => $meditation_ex,
        ];
    
        // Fetch normal exercises (excluding cardio)
        $normal_ex = DB::table('user_exercise_complete_status')
        ->join('exercises', 'user_exercise_complete_status.user_exercise_id', '=', 'exercises.exercise_id')
        ->where('user_exercise_complete_status.user_id', $user_id)
        ->where('user_exercise_complete_status.type', '!=', 'cardio')
        ->where('user_exercise_complete_status.exercise_status', 'completed')
        ->whereDate('user_exercise_complete_status.created_at', $date)
        ->pluck('exercises.exercise_id') // Extract only exercise_id values
        ->toArray(); // Convert collection to array
    
    
        $total_exercises = count($normal_ex);
    
        // Normal exercise summary
        $normal_summary = DB::table('user_exercise_complete_status')
            ->join('exercises', 'user_exercise_complete_status.user_exercise_id', '=', 'exercises.exercise_id')
            ->where('user_exercise_complete_status.user_id', $user_id)
            ->where('user_exercise_complete_status.type', '!=', 'cardio')
            ->where('user_exercise_complete_status.exercise_status', 'completed')
            ->whereDate('user_exercise_complete_status.created_at', $date)
            ->select(
                DB::raw('SUM(exercises.exercise_calories) as total_calories'),
                DB::raw('TIMESTAMPDIFF(SECOND, MIN(user_exercise_complete_status.created_at), MAX(user_exercise_complete_status.created_at)) as total_time_seconds')
            )
            ->first();
    
        $normal_summary->formatted_time = gmdate('H:i:s', $normal_summary->total_time_seconds ?? 0);
    
        $normal_response = [
            'summary' => [
                'total_calories' => $normal_summary->total_calories ?? 0,
                'total_time_seconds' => $normal_summary->total_time_seconds ?? 0,
                'formatted_time' => $normal_summary->formatted_time,
                'total_exercises' => $total_exercises,
            ],
            'exercise_data' => $normal_ex,
        ];
    
        // Fetch step count details
        $step_Count = DB::table('user_stepcounts_details')
            ->where('user_id', $user_id)
            ->whereDate('created_at', $date)
            ->first();
    
        // Final JSON response
        return response()->json([
            'status' => true,
            'data' => [
                'normal_exercises' => $normal_response,
                'step_count' => $step_Count ?? (object)[],
            ]
        ]);
    }
    
    
    
      public function get_eventwithoutenvent_history(Request $request){
          
        $version = $request->input('version');
        $user_id = $request->input('user_id');
        $date = $request->input('date');
    
        if (!$user_id) {
            return response()->json([
                'message' => 'User ID not found',
                'status'=>false,
                ]);
        }
    
        if (!$version) {
            return response()->json([
                'message' => 'Version not found',
                'status'=>false,
            ]);
        }
        
     $current_date = now()->toDateString();

        // Query for non-event exercises
        $withoutEvent_result = DB::table('user_exercise_complete_status')
            ->join('exercises', 'user_exercise_complete_status.user_exercise_id', '=', 'exercises.exercise_id')
            ->where('user_exercise_complete_status.user_id', $user_id)
            ->where('user_exercise_complete_status.exercise_status','completed')
            ->whereDate('user_exercise_complete_status.created_at', $current_date)
            ->select(
                DB::raw('SUM(exercises.exercise_calories) as total_calories'),
                DB::raw('COUNT(user_exercise_complete_status.id) as total_exercise_count'),
                DB::raw('TIMESTAMPDIFF(MINUTE, MIN(user_exercise_complete_status.created_at), MAX(user_exercise_complete_status.updated_at)) as total_time')
            )
            ->first();
        
        // Query for event exercises
        $Event_result = DB::table('event_exercise_completion_status')
            ->join('exercises', 'event_exercise_completion_status.user_exercise_id', '=', 'exercises.exercise_id')
            ->where('event_exercise_completion_status.user_id', $user_id)
            ->where('event_exercise_completion_status.exercise_status','completed')
            ->whereDate('event_exercise_completion_status.created_at', $current_date)
            ->select(
                DB::raw('SUM(exercises.exercise_calories) as total_calories'),
                DB::raw('COUNT(event_exercise_completion_status.id) as total_exercise_count'),
                DB::raw('TIMESTAMPDIFF(MINUTE, MIN(event_exercise_completion_status.created_at), MAX(event_exercise_completion_status.updated_at)) as total_time')
            )
            ->first();
        
        // Handle null results
        $withoutEvent_result = $withoutEvent_result ?? (object) ['total_calories' => 0, 'total_time' => 0, 'total_exercise_count' => 0];
        $Event_result = $Event_result ?? (object) ['total_calories' => 0, 'total_time' => 0, 'total_exercise_count' => 0];
        
        // Calculate totals
        $totalCelo = $withoutEvent_result->total_calories + $Event_result->total_calories;
        $total_time = $withoutEvent_result->total_time + $Event_result->total_time;
        $total_exercise_count = $withoutEvent_result->total_exercise_count + $Event_result->total_exercise_count;
        
        // Prepare response data
        $data = [
            'total_calories' => $totalCelo ?? 0,
            'total_time' => $total_time ?? 0,
            'total_exercise_count' => $total_exercise_count ?? 0
        ];

        
        return response()->json([
            'data' => $data,
            'status'=>true,
            ]);
          
      }



} 
