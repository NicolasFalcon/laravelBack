<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{

    protected $base_url;

    public function __construct()
    {
        $this->base_url = url('/'); // or config('app.url')
    }
    
    public function addfavorite(Request $request)
    {
        $user_id = request('user_id');
        $workout_id = $request->input('workout_id');

        // Check if the favorite exists for the specific user and workout
        $check_favourite = DB::table('favorite')
            ->select('user_id', 'workout_id')
            ->where('workout_id', $workout_id)
            ->where('user_id', $user_id)
            ->first(); // Execute the query to retrieve the result

        if ($check_favourite) {
            // If favorite exists, delete it
            DB::table('favorite')
                ->where('user_id', $user_id)
                ->where('workout_id', $workout_id)
                ->delete();
            return response()->json(['msg' => 'Workout removed from favorites']);
        } else {
            // If favorite does not exist, insert it
            DB::table('favorite')->insert([
                'workout_id' => $workout_id,
                'user_id' => $user_id,
            ]);
            return response()->json(['msg' => 'Workout added to favorites']);
        }
    }


    public function getfavoritediet(Request $request)
    {
        $token = request('login_token');
        $email = request('email');


        $sqlquery = DB::table('users')->where('email', $email)->first();
        $dbtoken = $sqlquery->login_token;
        if ($token === $dbtoken) {

            $favoriteworkoutIds = DB::table('favorite')
                ->select('diet_id')
                ->where('email', $email)
                ->whereNotNull('diet_id')
                ->get();

            foreach ($favoriteworkoutIds as $x => $val) {
                $diet_id = $val->diet_id;
                $diet = DB::table('diets')
                    ->where('diet_id', $diet_id)
                    ->whereNotNull('diet_id')
                    ->first();
                if ($diet) {
                    // The query returned a result
                    $id = $diet->diet_id;
                    $title = $diet->diet_title;
                    $description = $diet->diet_description;
                    $ingredients = $diet->diet_ingredients;
                    $category = $diet->diet_category;
                    $instructions = $diet->diet_directions;
                    $calories = $diet->diet_calories;
                    $carbs = $diet->diet_carbs;
                    $protein = $diet->diet_protein;
                    $protein = $diet->diet_protein;
                    $fat = $diet->diet_fat;
                    $time = $diet->diet_time;
                    $servings = $diet->diet_servings;
                    $featured = $diet->diet_featured;
                    $status = $diet->diet_status;
                    $price = $diet->diet_price;
                    $image = $diet->diet_image;
                    $categorydata = DB::table('categories')->where('category_id', $category)->first();
                    $category = $categorydata->category_title;
                    $arr[] = [
                        'id' => $id,
                        'title' => $title,
                        'description' => $description,
                        'ingredients' => $ingredients,
                        'category' => $category,
                        'instructions' => $instructions,
                        'calories' => $calories,
                        'carbs' => $carbs,
                        'protein' => $protein,
                        'fat' => $fat,
                        'time' => $time,
                        'servings' => $servings,
                        'featured' => $featured,
                        'status' => $status,
                        'price' => $price,
                        'image' => $this->base_url ."/images/" . $image,

                    ];
                }
            }
            echo json_encode($arr, JSON_NUMERIC_CHECK);
        } else {
            $msg[] = [
                'msg' => "invalid token"
            ];
            echo json_encode($msg, JSON_NUMERIC_CHECK);
        }
    }

    public function getfavoritewokrout(Request $request)
    {
        $token = request('login_token');
        $User_id = request('user_id');
        $sqlquery = DB::table('users')->where('id', $User_id)->first();
        $dbtoken = $sqlquery->login_token;
        if ($token === $dbtoken) {
            $favoriteworkoutIds = DB::table('favorite')
                ->select('workout_id')
                ->where('user_id', $User_id)
                ->whereNotNull('workout_id')
                ->pluck('workout_id');
            return response()->json([$favoriteworkoutIds]);
        } else {
            return response()->json(['msg' => 'error']);
        }
    }

    public function removefavorite_workout(Request $request)
    {

        $token = request('login_token');
        $user_id = request('user_id');
        $workout_id = request('workout_id');

        $sqlquery = DB::table('users')->where('id', $user_id)->first();
        $dbtoken = $sqlquery->login_token;
        if ($token === $dbtoken) {
            echo "match";

            $deletedRows = DB::table('favorite')
                ->where('user_id', $user_id)
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
            $msg[] = [
                'msg' => "invalid token"
            ];
            echo json_encode($msg, JSON_NUMERIC_CHECK);
        }
    }


   

    public function injury_test(Request $request)
    {
        $version = $request->input('version');
        $versions_current = DB::table('versions')->where('versions', $version)->where('type', 'current')->first();
        $versions_middle = DB::table('versions')->where('versions', $version)->where('type', 'middle')->first();
        $versions_past = DB::table('versions')->where('versions', $version)->where('type', 'past')->first();
        $userData = DB::table('injurys')->select('id', 'injury_title', 'injury_image')->get();
        if ($versions_current) {
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
        } elseif ($versions_middle) {
            echo "middle";
        } elseif ($versions_past) {
            echo "past";
        } else {
            echo "no data";
        }
    }
}

