<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use App\User;
use DateTime;
use DB;

class AuditController extends Controller
{
    public function __construct()
    {
       // Apply the jwt.auth middleware to all methods in this controller
       // except for the authenticate method. We don't want to prevent
       // the user from retrieving their token if they don't already have it
       //$this->middleware('jwt.auth', ['except' => ['authenticate']]);
       $this->middleware('auth:api');
    }


    public function addAudit($action,$description,$id="")
    {
        $user = $this->me();
               
        DB::table('audit_trail')->insert(
            [
                "userid" => isset($user->getData()->id) ? $user->getData()->id : $id ,
                "action" => $action,
                "description" => $description,
                "date" => new DateTime()
            ]
            );
 
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }
}