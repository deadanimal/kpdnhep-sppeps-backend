<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use DB;
use App\BotCategory;
use App\BotSubCategory;
use App\BotQuestion;
use App\EndingMenu;

class BotSubcategoryController extends Controller
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
        if ($request->mode == ""){
            
            //$end = 0;
            $subcategories = array();
            $main_category = DB::table('bot_subcategories')->where("id", "=", $request->id)->first();
            
            // while ($end != 1) {
                if ($main_category->has_sub == 1){
                    $bot_category = DB::table('bot_subcategories')->where("sub_category_id", "=", $main_category->id)->orderBy("id","asc")->get();
                    foreach ($bot_category as $key => $value) {
                        $subCategories2 = DB::table('bot_subcategories')->where("sub_category_id", "=", $value->id)->orderBy("id","asc")->get();
                        $value->subCategories = $subCategories2;
                        array_push($subcategories,$value);
                    }                   
                   // $end = 1;
                    //end and load the questions
                    // if ($bot_category->has_sub == 0){
                    //     
                    // }
                }
                else{
                    //end and load the questions
                    $end = 1;
                    $questions = BotQuestion::where("category_id",$main_category->id)->orderBy("id","asc")->get();
                   // $ending_menus = DB::table("ending_menu")->where("subcategory",$request->id)->get();
                   
                    array_push($subcategories,$questions);
                   // array_push($subcategories,$ending_menus);
                }
           // }
            return response()->json($subcategories,200);

        }else{
            $main_category = DB::table('bot_subcategories')->where("id",$request->id)->get();
            $questions = DB::table('bot_questions')->where("category_id",$request->id)->orderBy('id', 'asc')->get();
            if (count($questions)<1){
                $main_category[0]->has_question = 0;
            }else{
                $main_category[0]->has_question = 1;
                $main_category[0]->questions = $questions;
                $main_category[0]->endings = DB::table("end_selections")->where("subcategory",$request->id)->get();
            }
            return response()->json($main_category,200);
        }

    }

    public function addSubcategory(Request $request){
        $new = new BotSubCategory();
        $new->sub_category_name = $request->name;
        $new->sub_category_name_english = $request->englishname;
        $new->sub_category_id = $request->id;
        $new->delete = 1;
        $new->has_sub = 0;
        $new->save();
        $old = BotSubCategory::find($request->id);
        $old->has_sub = 1;
        $old->save();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("create","add sub category(id: {$new->id})");
        return response()->json(["status"=>"Success"]);
    }

    public function deleteQ(Request $request){
            $q = BotQuestion::find($request->id);
            $q->delete();
            $audit_c = new AuditController(); 
            $audit_c->addAudit("delete","delete question(id: {$q->id})");
            return response()->json("success",200);
    }
    public function deleteZ(Request $request){
            $q = EndingMenu::find($request->id);
            $q->delete();
            $audit_c = new AuditController(); 
            $audit_c->addAudit("delete","delete ending menu(id: {$q->id})");
            return response()->json("success",200);
    }

    public function delete(Request $request){

        $bot_category = BotSubCategory::find($request->id);         
        $subid = $bot_category->sub_category_id;
        if ($bot_category->has_sub == 1){
            $this->deleteOthers($bot_category);
        }else{
            $q = BotQuestion::where("category_id",$bot_category->id)->get();
            if (count($q)>0){
                BotQuestion::where("category_id",$bot_category->id)->delete();
            }
        }
        $bot_category->delete();  
        $audit_c = new AuditController(); 
        $audit_c->addAudit("delete","delete sub category(id: {$bot_category->id})");
        $old = BotSubCategory::where("sub_category_id",$subid)->get();
        if (count($old)>0){
             
        }else{
            $bot_category = BotSubCategory::find($subid);
             $bot_category->has_sub = 0;
             $bot_category->save();
        }  
       
        return response()->json("success",200);
    }

    public function deleteMain(Request $request){
        $bot_category = BotSubCategory::find($request->id);
        if ($bot_category->has_sub == 1){
            $this->deleteOthers($bot_category);
        }else{
            $q = BotQuestion::where("category_id",$bot_category->id)->get();
            if (count($q)>0){
                BotQuestion::where("category_id",$bot_category->id)->delete();
            }
        }
        $bot_category->delete();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("delete","delete sub category(id: {$bot_category->id})");
        $sub_categories = BotSubCategory::where("sub_category_id",$bot_category->sub_category_id)->get();
        $mainSub = BotSubCategory::find($bot_category->sub_category_id);
        if (count($sub_categories) < 1){
            $mainSub->has_sub = 0;
            $mainSub->save();
        }        
        return response()->json("success",200);

    }

    public function deleteOthers($thecat){
        if ($thecat->has_sub == 1){
            $c = BotSubCategory::where("sub_category_id",$thecat->id)->get();
            if (count($c) > 1){
                foreach ($c as $key => $value) {
                    if ($value->has_sub == 1){
                        $this->deleteOthers($value);
                        $deletethis = BotSubCategory::where("id",$thecat->id)->first();
                        if ($deletethis){
                            $deletethis->delete();
                        }
                    }else{
                        $deletethis = BotSubCategory::where("id",$value->id)->first();
                        //check if got questions
                        $q = BotQuestion::where("category_id",$deletethis->id)->get();
                        if (count($q)>0){
                            BotQuestion::where("category_id",$deletethis->id)->delete();
                        }
                        $deletethis->delete();
                    }
                }
                $deletethis = BotSubCategory::where("id",$thecat->id)->first();
                if ($deletethis){
                    $deletethis->delete();
                }
            }else if(count($c) > 0){
                //var_dump($c);
                $this->deleteOthers($c[0]);
                $deletethis = BotSubCategory::where("id",$thecat->id)->first();
                $deletethis->delete();
            }
        }else{
            $deletethis = BotSubCategory::where("id",$thecat->id)->first();
             //check if got questions
             $q = BotQuestion::where("category_id",$deletethis->id)->get();
             if (count($q)>0){
                 BotQuestion::where("category_id",$deletethis->id)->delete();
             }
            $deletethis->delete();
        }
    }

    public function addQ(Request $request){
        $newQ = new BotQuestion();
        $newQ->category_id = $request->id;
        $newQ->question = $request->question;
        $newQ->question_english = $request->questionenglish;
       $request->first === true ? $newQ->first = 1 : $newQ->first = 0;
        $newQ->delete = 1;
    
        if (str_replace(" ","",$request->buttonname) == ""){
            $request->buttonname = NULL;
        }else{
            $newQ->button = $request->buttonname;
            //$newQ->requiredAnswers = $request->buttonname;
        } 
        
        if (str_replace(" ","",$request->buttonenglish) == ""){
            $request->button_english = NULL;
        }else{
            $newQ->button_english = $request->buttonenglish;
           // $newQ->requiredAnswers = $request->buttonname;
        } 
            
        str_replace(" ","",$request->buttonlink) == "" ? $request->buttonlink = NULL : $newQ->link = $request->buttonlink;
        str_replace(" ","",$request->thetrue) == "" ? $request->thetrue = NULL : $newQ->trueRoute = $request->thetrue;
        str_replace(" ","",$request->thefalse) == "" ? $request->thefalse = NULL : $newQ->falseRoute = $request->thefalse;
        str_replace(" ","",$request->requiredanswer) == "" ? $request->requiredanswer = NULL : $newQ->requiredAnswers = $request->requiredanswer;
        str_replace(" ","",$request->logic) == "" ? $request->logic = NULL : $newQ->logic = $request->logic;
     
        $newQ->save();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("create","create question(id: {$newQ->id})");
        return response()->json("success",200);
    }

    public function editQ(Request $request){
        $q = BotQuestion::find($request->id);
        $q->question = $request->question;
        $q->question_english = $request->questionenglish;
        $request->first === true ? $q->first = 1 : $q->first = 0;  
        $q->button = $request->buttonname;
        $q->button_english = $request->buttonenglish;
        $q->requiredAnswers = $request->buttonname;
        $q->link = $request->buttonlink;
        $q->trueRoute = $request->thetrue;
        $q->falseRoute = $request->thefalse;
        $q->requiredAnswers = $request->requiredanswer;
        $q->logic = $request->logic;
        $q->save();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("update","update question(id: {$q->id})");
        return response()->json("success",200);
    }

    public function addZ(Request $request){
        $newQ = new EndingMenu();
        $newQ->subcategory = $request->id;
        $newQ->name = $request->name;
        $newQ->link = $request->link;
        $newQ->name_english = $request->nameenglish;
        $newQ->delete = 1;
    
        $newQ->save();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("create","create ending menu(id: {$newQ->id})");
        return response()->json("success",200);
    }

    public function editZ(Request $request){
        $q = EndingMenu::find($request->id);
        $q->name = $request->name;
        $q->link = $request->link;
        $q->name_english = $request->nameenglish;
        $q->delete = 1;
        $q->save();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("update","update ending menu(id: {$q->id})");
        return response()->json("success",200);
    }

    public function update(Request $request){
        $bot_category = BotSubCategory::find($request->id);
        $bot_category->sub_category_name = $request->name;
        $bot_category->sub_category_name_english = $request->englishname;
        $bot_category->save();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("update","update sub category(id: {$bot_category->id})");
        return response()->json("success",200);
    }
    
    public function editMain(Request $request){
        $bot_category = BotSubCategory::find($request->id);
        $bot_category->sub_category_name = $request->name;
        $bot_category->sub_category_name_english = $request->englishname;
        $bot_category->header = $request->header;
        $bot_category->headerE = $request->headerE;
        $bot_category->save();
        $audit_c = new AuditController();
        $audit_c->addAudit("update","update sub category(id: {$bot_category->id})");
        return response()->json("success",200);
    } 

}