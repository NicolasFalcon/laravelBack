<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\User_deleteController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\Updatepassword_linkController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    return response()->json(['message' => 'Cache cleared successfully']);
});

Route::post('getnearbygyms', [LoginController::class, 'getNearbyGyms']);


Route::post('user_login', [UserController::class, 'user_login']);
Route::post('user_registration', [UserController::class, 'user_registration']);
Route::post('sendemail_link', [UserController::class, 'sendemail_link']);
Route::post('user_update_details', [UserController::class, 'user_update_details']);
Route::post('user_verify', [UserController::class, 'user_verify']);
Route::post('allworkout', [UserController::class, 'allworkout']);
Route::post('userprofile', [UserController::class, 'userprofile']);
Route::post('usercustomworkout', [UserController::class, 'usercustomworkout']);
Route::post('userfreecustomworkout', [UserController::class, 'userfreecustomworkout']);
Route::post('userfreecustomexercise', [UserController::class, 'userfreecustomexercise']);
Route::get('get_categorie_diet', [UserController::class, 'get_categorie_diet']);
Route::post('get_categorie', [UserController::class, 'get_categorie']);
Route::post('user_exercise_status', [UserController::class, 'user_exercise_status']);
Route::post('user_exercise_details', [UserController::class, 'user_exercise_details']);
Route::post('user_exercise_record', [UserController::class, 'user_exercise_record']);
Route::get('popularWorkout/{version}', [UserController::class, 'popularWorkout']);
Route::post('user_details', [UserController::class, 'user_details']);
Route::post('user_status', [UserController::class, 'user_status']);
Route::post('workout_status', [UserController::class, 'workout_status']);
Route::post('steps_details', [UserController::class, 'steps_details']);
Route::post('send_notification', [LoginController::class, 'send_notification']);
Route::get('send_notification_payment', [LoginController::class, 'send_notification_payment']);
Route::post('exercisecalo', [User_deleteController::class, 'exercisecalo']);
Route::post('exercise_total_calo', [User_deleteController::class, 'exercise_total_calo']);
Route::post('history', [UserController::class, 'history']);
Route::post('monthly_history', [UserController::class, 'monthly_history']);
Route::post('selectDate_exercise', [UserController::class, 'selectDate_exercise']);
Route::get('deleteAccount', [UserController::class, 'deleteAccount']);
Route::get('deleteGoogleAccount', [UserController::class, 'deleteGoogleAccount']);
Route::post('send_passwordreset_email', [UserController::class, 'send_passwordreset_email']);
Route::get('send_jsonfile', [LoginController::class, 'send_jsonfile']);
Route::post('like_dislike', [UserController::class, 'like_dislike']);
Route::post('total_like_view', [UserController::class, 'total_like_view']);
Route::post('workout_view_count', [UserController::class, 'workout_view_count']);
Route::get('get_music', [User_deleteController::class, 'music']);
Route::get('get_exercise', [User_deleteController::class, 'get_exercise']);
Route::get('get_weekday_exercise', [User_deleteController::class, 'get_all_days']);
Route::post('user_custom_workout', [User_deleteController::class, 'custom_workout']);
Route::get('get_user_custom_workout', [User_deleteController::class, 'get_workout']);
Route::get('delete_custom_workout', [User_deleteController::class, 'delete_custom_workout']);
Route::post('edit_custom_workout', [User_deleteController::class, 'edit_custom_workout']);
Route::get('delete_exercise', [User_deleteController::class, 'delete_exercise']);
Route::get('oneweek_exercise_data', [User_deleteController::class, 'exercise_week_data']);
Route::get('delete_completed_exercises', [User_deleteController::class, 'delete_completed_exercises']);
Route::get('get_challenges_exercises', [User_deleteController::class, 'get_challenges_exercises']);
Route::post('save_challenges_exercises', [User_deleteController::class, 'save_challenges_exercises']);
Route::post('get_challenge_exercises_status', [User_deleteController::class, 'get_challenge_exercises_status']);
Route::post('save_challenge_exercises_status', [User_deleteController::class, 'save_challenge_status']);
Route::post('update_challenge_exercises_status', [User_deleteController::class, 'update_challenge_status']);
Route::post('user_challenge_exercise_details', [User_deleteController::class, 'user_challenge_exercise_details']);
Route::post('user_lets_go', [User_deleteController::class, 'user_lets_go']);
Route::get('user_exercise_complete_status_data', [User_deleteController::class, 'user_exercise_complete_status']);
Route::get('search_wokrouts', [User_deleteController::class, 'search_wokrouts']);
Route::post('user_custom_diets', [User_deleteController::class, 'user_custom_diets']);
Route::get('get_custom_diets', [User_deleteController::class, 'get_custom_diets']);
Route::post('send_voucher', [User_deleteController::class, 'send_voucher']);
Route::post('check_condition', [User_deleteController::class, 'check_condition']);
Route::post('user_edit_exercises', [User_deleteController::class, 'add_edit_exercises']);
Route::post('update_name_email', [User_deleteController::class, 'update_name_email']);
Route::post('get_all_weekday_exercise', [User_deleteController::class, 'get_all_weekday_exercise']);
Route::get('get_apikeys', [User_deleteController::class, 'get_apikeys']);


Route::post('single_exercise_status', [UserController::class, 'single_exercise_status']);

Route::post('resize_videos', [TestController::class, 'resize_videos']); 
Route::post('reduceAllVideos', [TestController::class, 'reduceAllVideos']); 




Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('addfavorite', [FavoriteController::class, 'addfavorite']); // token applied
Route::get('getfavoritediet', [FavoriteController::class, 'getfavoritediet']); // token applied
Route::post('getfavoritewokrout', [FavoriteController::class, 'getfavoritewokrout']); //token applied
Route::get('removefavorite_workout', [FavoriteController::class, 'removefavorite_workout']); //token applied
Route::post('user_login_test', [FavoriteController::class, 'user_login_test']);


Route::get('user_delete', [User_deleteController::class, 'user_delete']); // token applied
Route::get('getcategory', [User_deleteController::class, 'getcategory']); //not  applied
Route::get('getfocusarea', [User_deleteController::class, 'getfocusarea']);
//not  applied
//not  applied
Route::post('getdiet', [User_deleteController::class, 'getdiet']);
Route::get('dietdetails', [User_deleteController::class, 'dietdetails']); //not  applied
Route::Post('transactions', [User_deleteController::class, 'transactions']);
Route::post('transactionsdetails', [User_deleteController::class, 'transactionsdetails']);

Route::get('goals_levels_focusarea_data', [UserController::class, 'goals_levels_focusarea_data']); //not  applied
Route::get('equipment_data', [User_deleteController::class, 'equipment_data']); //not  applied
Route::get('equipment_workout', [User_deleteController::class, 'equipment_workout']); //not  applied
Route::get('levels_data', [User_deleteController::class, 'levels_data']); //not  applied
Route::get('get_profile_image', [User_deleteController::class, 'get_profile_image']); //token applied
Route::post('update_profile_image', [User_deleteController::class, 'update_profile_image']); //token applied
Route::post('update_profile', [User_deleteController::class, 'update_profile']); //token applied
Route::get('workout_category', [User_deleteController::class, 'workout_category']); //not  applied
Route::get('days', [User_deleteController::class, 'days']); //not  applied
Route::get('popularworkout', [User_deleteController::class, 'popularworkout']);
Route::get('categorydata', [User_deleteController::class, 'categorydata']);
Route::post('productdata', [User_deleteController::class, 'productdata']);
Route::post('mindsetdata', [User_deleteController::class, 'mindsetdata']);
Route::post('push_notification', [User_deleteController::class, 'push_notification']);
Route::get('setp_count', [User_deleteController::class, 'setp_count']);
Route::post('update_details', [User_deleteController::class, 'update_details']);
Route::post('check_condition', [User_deleteController::class, 'check_condition']);
Route::post('condition', [User_deleteController::class, 'condition']);
Route::post('add_coins', [User_deleteController::class, 'insert_coins']);
Route::get('get_banners', [User_deleteController::class, 'get_banners']);
Route::get('delete_data', [User_deleteController::class, 'delete_data']);
Route::get('custom_dialog', [User_deleteController::class, 'custom_dialog']);
Route::get('delete_wallet_data', [User_deleteController::class, 'delete_wallet_data']); // testing
Route::get('.well-known/assetlinks.json', [UserController::class, 'well_known_assetlinks_json']);
Route::get('apple-app-site-association', [UserController::class, 'apple_app_site_association']); 
Route::post('create_custom_diet', [User_deleteController::class, 'create_custom_diet']); 





Route::get('no_equipment', [User_deleteController::class, 'no_equipment']); //not  applied
Route::post('resend_otp', [UserController::class, 'resendotp']);
Route::get('injury', [UserController::class, 'injury']);
Route::post('injury_test', [FavoriteController::class, 'injury_test']);



// fitme plans  
Route::get('get_termconditon_data', [User_deleteController::class, 'get_termconditon_data']);
Route::get('reset_leaderboard', [User_deleteController::class, 'reset_leaderboard']);
Route::get('testing', [PlansController::class, 'testing']);
// Route::post('add_coinplan', [PlansController::class, 'add_coin']);
Route::get('get_user_event_data', [PlansController::class, 'get_event_data']);
Route::post('convert_coins', [PlansController::class, 'convert_coins']);
Route::post('create_code', [PlansController::class, 'create_code']);
Route::post('get_plans', [PlansController::class, 'get_plans']);
Route::get('get_active_plans', [PlansController::class, 'get_active_plans']);
Route::get('leader_board', [PlansController::class, 'leader_board']);
Route::post('event', [PlansController::class, 'event']);
Route::get('event_details/{id}', [PlansController::class, 'event_details']);
Route::post('event_exercise_complete_status', [PlansController::class, 'event_exercise_complete_status']);
Route::post('user_event__exercise_status', [PlansController::class, 'user_event__exercise_status']);
Route::post('send_push_notification_sunday', [PlansController::class, 'send_push_notification_sunday']);
Route::post('send_push_notification_sunday_us', [PlansController::class, 'send_push_notification_sunday_us']);
Route::get('send_mail_winner', [PlansController::class, 'send_mail_winner']);
Route::get('send_notification_saturday_winner', [PlansController::class, 'send_notification_saturday_winner']);
Route::get('send_notification_saturday_winner_us', [PlansController::class, 'send_notification_saturday_winner_us']);
Route::get('exercise_points_day', [PlansController::class, 'exercise_points_day']);
Route::get('delete_exercise_event', [PlansController::class, 'delete_exercise_event']);
Route::get('all_in_one', [PlansController::class, 'all_in_one']);
Route::get('all_user_data', [PlansController::class, 'all_user_data']);
Route::get('all_user_with_condition', [PlansController::class, 'all_user_with_condition']);
Route::get('event_deletedata', [PlansController::class, 'event_deletedata']);
Route::get('send_push_notification_monday', [PlansController::class, 'send_push_notification_monday']);
Route::get('send_push_notification_monday_us', [PlansController::class, 'send_push_notification_monday_us']);
Route::post('generat_code', [PlansController::class, 'generat_code']);
Route::post('add_referral_coin', [PlansController::class, 'add_referral_coin']);
Route::get('download_url', [PlansController::class, 'dawnload_url']);
// Route::get('/{parameter}', [PlansController::class, 'dawnload_url']);
// Route::get('/unique_url', [PlansController::class, 'generateUniqueUrl']);
Route::get('breathinout', [PlansController::class, 'breathinout']);
Route::get('breathinout_us', [PlansController::class, 'breathinout_us']);//notification
Route::get('monday_to_friday_notification', [PlansController::class, 'monday_to_friday_notification']); // done
Route::get('delete_exercises_sunday', [UserController::class, 'delete_completed_exercises']);
Route::get('coin_deduction_rec', [PlansController::class, 'coin_deduction_rec']);
Route::get('testa_coin_deduction_rec', [TestController::class, 'testa_coin_deduction_rec']);
Route::post('cardio_status', [PlansController::class, 'cardio_status']);
Route::post('add_breathinout_coins', [PlansController::class, 'breathinout_status']);
// Route::post('cardio_status', [PlansController::class, 'add_cardio_coins']);
Route::get('get_breathinout_session', [PlansController::class, 'get_breathinout_session']); 

Route::post('app_crash_rec', [PlansController::class, 'app_crash_rec']);
Route::post('past_winners', [PlansController::class, 'past_winners']);
Route::get('cardio_exercise_status', [PlansController::class, 'cardio_exercise_status']);
Route::get('delete_weekly_data', [PlansController::class, 'delete_weekly_data']);




// Api for only testing purpose 
// Route::post('test_user_registration', [TestController::class, 'test_user_registration']);
// Route::post('test_user_login', [TestController::class, 'test_user_login']);
Route::get('test_leader_board', [TestController::class, 'test_leader_board']);
 Route::get('test_all_in_one', [TestController::class, 'test_all_in_one']);
 Route::get('test_all_user_data', [TestController::class, 'test_all_user_data']);
 Route::get('user_rank', [TestController::class, 'user_rank']);
 Route::post('test_create_custom_diet', [TestController::class, 'test_create_custom_diet']);
Route::post('test_add_referral_coin', [TestController::class, 'test_add_referral_coin']);// done change
Route::post('test_update_custom_diet', [TestController::class, 'test_update_custom_diet']);

// Route::post('test_event', [TestController::class, 'test_event']);// done change
// Route::post('test_event_details', [TestController::class, 'test_event_details']); 
   Route::get('testing_event_details/{id}', [TestController::class, 'testing_event_details']); 
// Route::get('testing_leader_board', [TestController::class, 'testing_leader_board']);

Route::get('test_exercise_points_day', [TestController::class, 'test_exercise_points_day']);
// Route::get('test_user_exercise_status', [TestController::class, 'test_user_exercise_status']);
// Route::post('testing_insert_coins', [TestController::class, 'testing_insert_coins']);
Route::get('testing_exercise_points_day', [TestController::class, 'testing_exercise_points_day']);//update 29-08
// // Route::get('test_event_exercise_complete_status', [TestController::class, 'test_event_exercise_complete_status']);
 Route::post('test_user_event__exercise_status', [TestController::class, 'test_user_event__exercise_status']);


Route::post('testing_event_exercise_complete_status', [TestController::class, 'testing_event_exercise_complete_status']);
// Route::get('test1_leader_board', [TestController::class, 'test1_leader_board']);
 Route::post('testing_add_coins', [TestController::class, 'testing_add_coins']);
// Route::post('test_single_exercise_status', [TestController::class, 'test_single_exercise_status']);
// Route::post('test_custom_workout', [TestController::class, 'test_custom_workout']);
Route::get('test_coin_deduction_rec', [TestController::class, 'test_coin_deduction_rec']);
Route::get('testnew_coin_deduction_rec', [TestController::class, 'testnew_coin_deduction_rec']);
// Route::get('testing_all_user_data', [TestController::class, 'testing_all_user_data']);

Route::post('testing1_event_exercise_complete_status', [TestController::class, 'testing1_event_exercise_complete_status']);
 //Route::post('test1_add_coins', [TestController::class, 'test1_add_coins']);
// Route::post('past_winners', [TestController::class, 'past_winners']);
// // Route::get('test_coin_deduct_history', [TestController::class, 'test_coin_deduct_history']);
Route::get('test_get_breathinout_session', [TestController::class, 'test_get_breathinout_session']); 
Route::get('testing_get_breathinout_session', [TestController::class, 'testing_get_breathinout_session']);  //dileep 2_9_24

Route::post('test_get_all_weekday_exercise', [TestController::class, 'test_get_all_weekday_exercise']);//update 29-08

// corn apis for india and us 
Route::get('send_sunday_notification_us', [TestController::class, 'send_sunday_notification_us']); 
Route::get('music_details', [UserController::class, 'music_details']); 
Route::get('testa_all_user_with_condition', [TestController::class, 'testa_all_user_with_condition']);
Route::post('testa_add_referral_coin', [TestController::class, 'testa_add_referral_coin']);



