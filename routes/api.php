<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//test track_orders
Route::get('/track_orders', 'track_controller@track_orders');
//link localhost:8000/api/track_orders


// prics trake
Route::post('/changprics', 'ApiController@changprics_api');
Route::post('/postcookies', 'ApiController@postcookies');
Route::get('/getlastupdate', 'ApiController@getlastupdate');
Route::post('/poststatus', 'ApiController@poststatus');
Route::get('/getstatus', 'ApiController@getstatus');


//close orders
Route::get('/get_processing_orders', 'git_data@processing_orders');


//track ads
Route::post('/post_track_amount_and_price', 'track_controller@post_track_amount_and_price');
Route::post('/post_track_status', 'track_controller@post_track_status');
Route::get('/git_track_data', 'track_controller@git_track_data');
Route::get('/git_track_data2', 'track_controller@track_orders');

//progress order 

//  binance
Route::get('/git_progress_task', 'progress_orders@git_progress_task');
Route::post('/chack_progress_order', 'progress_orders@chack_progress_order');
Route::get('/git_binance_email_otp', 'progress_orders@git_binance_email_otp');
Route::get('/git_binance_g2fa_otp', 'progress_orders@git_binance_g2fa_otp');
Route::post('/update_progress_task', 'progress_orders@update_progress_task');

//  wise
Route::get('/git_order_otp', 'progress_orders@git_order_otp');
Route::get('/git_progress_order', 'progress_orders@git_progress_order');
Route::get('/git_wise_login_otp', 'progress_orders@git_wise_login_otp');
Route::post('/update_progress_order', 'progress_orders@update_progress_order');
Route::get("/new_sms_massage/name/{name}/number/{number}/message/{message}", 'progress_orders@new_sms_massage');
Route::post('/update_transactions', 'progress_orders@update_transactions');


//track price system
Route::get('/get_prices', 'ApiController@get_crupto_pricec_from_marketcup');




Route::group(['middleware' => ['auth:sanctum']], function () {
    // middleware routes here
    Route::post('/authCheck', 'ApiController@authCheck');
    Route::post('/logout', 'ApiController@logout');
});
