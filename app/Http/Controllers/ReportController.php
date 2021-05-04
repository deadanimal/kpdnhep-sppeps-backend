<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use DB;
use Auth;

use DateTime;

class ReportController extends Controller
{
    public function __construct()
    {
       // Apply the jwt.auth middleware to all methods in this controller
       // except for the authenticate method. We don't want to prevent
       // the user from retrieving their token if they don't already have it
       $this->middleware('jwt.auth');
    }

    public function index(Request $request)
    {
        if ($request->type == "dashboard"){
            $dashboard_r = DB::table('dashboard_report')->first();
            return response()->json($dashboard_r,200);
        }elseif ($request->type == "us") {
            if ($request->start == ""){
                return response()->json("fail",200);
            }
            $us = DB::table('usage_report')->whereBetween('date',[$request->start . " 00:00:00",$request->end . " 23:59:59"])->orderBy("date","desc")->get();
            return response()->json($us,200);
        }elseif ($request->type == "ur") {
            if ($request->start == ""){
                return response()->json("fail",200);
            }
            $ur = DB::table('user_review')->whereBetween('user_review.created_at',[$request->start . " 00:00:00",$request->end . " 23:59:59"])->join('customer', 'customer.id', '=', 'user_review.userid')->orderBy("user_review.created_at","desc")->select('user_review.*','customer.phonenumber','customer.email','customer.name')->get();
        
            return response()->json($ur,200);
        }elseif ($request->type == "lch"){
            $customers = DB::table('customer')->whereBetween('customer.attendtime',[$request->start . " 00:00:00",$request->end . " 23:59:59"])->join('users', 'users.id', '=', 'customer.agentId')->orderBy("customer.attendtime","desc")->select('customer.*','users.email as emel')->get();
            return response()->json($customers,200);
        }elseif ($request->type == "lch2") {
            $c_history = DB::table('conversations')->where("clientId",$request->client)->orWhere("recipient",$request->client)->where("clientId","!=",0)->orderBy("created_at","asc")->get();
            
            return response()->json($c_history,200);
        }elseif ($request->type == "audit") {
            $c_history = DB::table('audit_trail')->join("users","audit_trail.userid","=","users.id")->whereBetween('date',[$request->start . " 00:00:00",$request->end . " 23:59:59"])->orderBy("date","desc")->select('audit_trail.*','users.email')->get();
            
            return response()->json($c_history,200);
        }
    }
    
}