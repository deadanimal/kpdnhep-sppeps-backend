<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Mail;
use JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function __construct()
    {
       // Apply the jwt.auth middleware to all methods in this controller
       // except for the authenticate method. We don't want to prevent
       // the user from retrieving their token if they don't already have it
       $this->middleware('jwt.auth');
    }
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    //use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
   // protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('guest');
    // }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(Request $request)
    {
        try {
            User::create([
            'name' => $request->name,
            'role' => $request->role,
            'email' => $request->email,
            'disabled' => 0,
            'password' => Hash::make($request->password),
            ]);
            $audit_c = new AuditController(); 
            $audit_c->addAudit("create","create user(email: {$request->email})");

        } catch (\Exception $exception) {
            //var_dump($exception->errorInfo);
            if (str_contains($exception->errorInfo[0],"23505")){
                return response()->json(["status"=>"failunique"],200);
            }
        }

        $data = array();
        
        try {
                //code...   
            Mail::send('emailCred', ["email"=>$request->email,"password"=>$request->password,"website"=>env('ADMIN_ZAKAT_URL')], function($message) use ($request){
                $message->to($request->email, 'Registration Details')->subject("Registration Details");
                $message->from('maklumbalasaduan@zakatselangor.com.my','maklumbalasaduan@zakatselangor.com.my');
            });
        } catch (\Throwable $th) {
           // var_dump($th);
        }
     
        return response()->json(["status"=>"success"],200);
    }

}
