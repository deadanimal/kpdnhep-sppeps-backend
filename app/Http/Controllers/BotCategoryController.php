<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use DB;
use App\BotCategory;
use App\BotSubCategory;
use App\Http\Controllers\BotSubcategoryController;

class BotCategoryController extends Controller
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
        $bot_category = DB::table('bot_category')->orderBy("id",'asc')->get();
        foreach ($bot_category as $key => $value) {
            $subcategories = DB::table('bot_subcategories')->where("bot_category_id",$value->id)->get();            
            $value->subCategories = $subcategories;
        }
        return response()->json($bot_category,200);
    }

    public function createCategory(Request $request)
    {
        $bot_category = new BotCategory();
        $bot_category->category_name = $request->name;
        $bot_category->category_name_english = $request->englishname;
        $bot_category->delete = 1;
        $bot_category->save();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("create","create category(id: {$bot_category->id})");
        return response()->json("success",200);
    }

    public function addSubcategory(Request $request){
        $new = new BotSubCategory();
        $new->sub_category_name = $request->name;
        $new->sub_category_name_english = $request->englishname;
        $new->bot_category_id = $request->id;
        $new->delete = 1;
        $new->has_sub = 0;
        $new->save();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("create","add sub category(id: {$new->id})");
        return response()->json(["status"=>"Success"]);
    }

    public function deleteBotCategory(Request $request){
        $bot_category = BotCategory::find($request->id);
        //if have subcategories
        $subcategories = BotSubCategory::where("bot_category_id",$bot_category->id)->get();
        if (count($subcategories) > 0){
            $BotSubcategoryCController = new BotSubcategoryController();
            foreach ($subcategories as $key => $value) {
                $BotSubcategoryCController->deleteOthers($value);                
            }
        }
        $bot_category->delete();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("delete","delete category(id: {$bot_category->id})");
        return response()->json("success",200);
    }

    public function updateBotCategory(Request $request){
        $bot_category = BotCategory::find($request->id);
        $bot_category->category_name = $request->name;
        $bot_category->category_name_english = $request->englishname;   
        $bot_category->save();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("update","update sub category(id: {$bot_category->id})");
        return response()->json("success",200);
    } 
}