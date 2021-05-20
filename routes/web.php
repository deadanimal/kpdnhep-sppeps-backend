<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
// Route::get('/chatframe', function () {
//     return view('chatframe');
// });

//jwt
Route::group(['middleware' => 'api','prefix' => 'api'], function(){
    Route::post('authenticate', 'AuthenticateController@authenticate');
    Route::post('logout', 'AuthenticateController@logout');
    Route::post('refresh', 'AuthenticateController@refresh');
    Route::post('me', 'AuthenticateController@me');
    Route::post('register', 'RegisterController@create');
    Route::get('getUsers', 'AuthenticateController@getUsers');
    Route::post('deleteUser', 'AuthenticateController@deleteUser');    
    Route::post('disableUser', 'AuthenticateController@disableUser');    
    Route::post('changePassword', 'AuthenticateController@changePassword');    
    Route::post('updateUser', 'AuthenticateController@updateUser');    
    Route::post('sendResetLink', 'AuthenticateController@sendResetLink');    
    Route::post('changePassNow', 'AuthenticateController@changePassNow');    
    Route::get('index', 'AuthenticateController@index');
    Route::post('giveReport', 'ReportController@index');
    Route::post('checkMyKad','NonUserController@checkMyKad');
    Route::post('savePre','NonUserController@savePre');	
    Route::post('checkUrlVars','NonUserController@checkUrlVars');	
    Route::post('registerFull', 'NonUserController@register');
    Route::post('authenticateOthers', 'AuthenticateController@authenticateOthers');
    Route::get('downloadPhoto', 'AuthenticateController@downloadPicture');
    Route::post('newApplication', 'ApplicantController@newApplication');
    Route::post('saveApplication', 'ApplicantController@saveApplication');
    Route::post('doVerification','ApplicantController@checkGotAppliedBefore');
    Route::get('retrieveStatus','ApplicantController@retrieveStatus');
    //Route::get();
});
//end of jwt
Route::get('login',array('as'=>'login',function(){
   // return view('login');
}));

