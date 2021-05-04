<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use DB;
use App\DynamicVariable;

class DynamicVariableController extends Controller
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
        $dynamic_variable = DB::table('dynamic_variables')->get();
        return response()->json($dynamic_variable,200);
    }

    public function create(Request $request)
    {
        $dynamic_variable = new DynamicVariable();
        $dynamic_variable->name = $request->name;
        $dynamic_variable->value = $request->value;
        $dynamic_variable->delete = 1;
        $dynamic_variable->save();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("create","create dynamic variable(id: {$dynamic_variable->id})");
        return response()->json("success",200);
    }

    public function delete(Request $request){
        $dynamic_variable = DynamicVariable::find($request->id);
        $dynamic_variable->delete();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("delete","delete dynamic variable(id: {$dynamic_variable->id})");
        return response()->json("success",200);
    }

    public function update(Request $request){
        $dynamic_variable = DynamicVariable::find($request->id);
        $dynamic_variable->name = $request->name;
        $dynamic_variable->value = $request->value;
        $dynamic_variable->save();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("update","update dynamic variable(id: {$dynamic_variable->id})");
        return response()->json("success",200);
    } 
}