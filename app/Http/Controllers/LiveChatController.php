<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use DB;
use Auth;
use App\Conversationz;
use App\ConversationId;
use App\LiveChatNotification;
use App\Customer;
use App\AdminBusy;
use DateTime;

class LiveChatController extends Controller
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
        $user = Auth::user();
        if($user->role == 1){ 
            $notification = DB::table('live_chat_notification')->where("who","admin")->where("waiting",0)->get();

            if (count($notification)<1){
                $notification = DB::table('live_chat_notification')->where("who","admin")->where("waiting",1)->get();
                if(count($notification)<1){
                    return response()->json(["status"=>"nothing"],200);
                }else{
                        $l = LiveChatNotification::where('userid',$notification[0]->userid)->get();
                        $l[0]->waiting = 0;
                        $l[0]->save();
                        return response()->json(["status"=>"nothing"],200);
                }
              
            }
            $count = LiveChatNotification::all()->count() - 1; 
            $notification[0]->waitcount = $count;
            return response()->json($notification,200);
 
        }else if($user->role == 2){
            $notification = DB::table('live_chat_notification')->where("who","superadmin")->get();
            
            if (count($notification)<1){
                return response()->json(["status"=>"nothing"],200);
            }
            $count = LiveChatNotification::all()->count() - 1; 
            $notification[0]->waitcount = $count;  
            return response()->json($notification,200);
        }
    }

    public function acceptRequest(Request $request){
        $userId = LiveChatNotification::where("waiting",0)->get();
        
        if ($userId->isEmpty()){
            $waiting = LiveChatNotification::where("waiting",1)->first();
            if (!$waiting){
                return response()->json(["status"=>"Gone"],200);
            }else {
                $c = Customer::find($waiting->userid);
                $waiting->delete(); 
                $user = auth('api')->user();
                $c->agentId = $user->id;
                $c->attendtime = new DateTime();
                $c->save();
                $adminbusy = new AdminBusy();
                $adminbusy->userid = $c->agentId;
                $adminbusy->save();
              
                return response()->json(["status"=>"success","name"=>$c->name,"language"=>$c->languageOfChoice],200);
            }      
        }else{
            $c = Customer::find($userId[0]->userid);
            $userId[0]->delete();
        
            $user = auth('api')->user();
            $c->agentId = $user->id;
            $c->attendtime = new DateTime();
            $c->save();
            $adminbusy = new AdminBusy();
            $adminbusy->userid = $c->agentId;
            $adminbusy->save();
            return response()->json(["status"=>"success","name"=>$c->name,"language"=>$c->languageOfChoice],200);
        }
    }

    public function agentMsg(Request $request){        
        $user = auth('api')->user();
        if ($request->userId == ""){
            $c = Customer::where("agentId",$user->id)->latest()->get();
        }     
        $m = new Conversationz();
        $m->message = "empty";        
        $m->clientId = $user->id;
        $m->recipient = $request->userId == "" ? $c[0]->id : $request->userId;
        $m->sendtime = new DateTime();
        $m->agentMsg = $request->message;
        $m->save();
        $altC = new ConversationId();
        $altC->receipent = "client";
        $altC->clientid = $request->userId == "" ? $c[0]->id : $request->userId;
        $altC->agentid = $user->id;
        $altC->message = $request->message;
        $altC->save();

       // Event::dispatch("s",array($m));
        return response()->json(($request->userId == "" ? $c[0]->id : "success"),200);
        // $cid = new ConversationId();
        // $cid->convoid = $m->id;
        // $cid->save();
    }

    public function routeToSuperAdmin(Request $request){
        $l = LiveChatNotification::where('waiting',0)->first();
        $l->who = "superadmin";
        $l->save();
    }
    
    public function getagentmsg(Request $request){
        $user = auth('api')->user();
        $message = ConversationId::where("receipent","agent")->where("agentid",$user->id)->first();
        if ($message){
            $m = $message;
            $message->delete();
            return response()->json($m,200);
        }else{
            return response()->json(["status"=>"nothing"],200);
        }    
    }

    public function toggle(Request $request){
        $c = DB::table("toggle_notification")->where("id",1)->first();
        if($c->on_off == 0){
            DB::table("toggle_notification")->where("id",1)->update(['on_off'=>1]);
        }else{
            DB::table("toggle_notification")->where("id",1)->update(['on_off'=>0]);
        }
      
        return response()->json(["status"=>"success"],200);
    }

    public function checkToggle(Request $request){
        $lc = DB::table("toggle_notification")->where("id",1)->first();
        if ($lc->on_off == 1){
            return response()->json(["status"=>"1"]);
        }else{
            return response()->json(["status"=>"0"]);

        }        
    }

    public function toggleLang(Request $request){
        $c = DB::table("toggle_notification")->where("id",2)->first();
        if($c->on_off == 0){
            DB::table("toggle_notification")->where("id",2)->update(['on_off'=>1]);
        }else{
            DB::table("toggle_notification")->where("id",2)->update(['on_off'=>0]);
        }
      
        return response()->json(["status"=>"success"],200);
    }

    public function checkToggleLang(Request $request){
        $lc = DB::table("toggle_notification")->where("id",2)->first();
        if ($lc->on_off == 1){
            return response()->json(["status"=>"1"]);
        }else{
            return response()->json(["status"=>"0"]);

        }        
    }

    public function sendFeedback(Request $request){
        $user = auth('api')->user();
        $m = new Conversationz();
        $m->message = "empty";        
        $m->clientId = $user->id;
        $c = Customer::where("agentId",$user->id)->latest()->get();
        $m->recipient = $request->userId == "" ? $c[0]->id : $request->userId;
        $m->sendtime = new DateTime();
        $m->agentMsg = $request->message;
        $m->save();
        $altC = new ConversationId();
        $altC->receipent = "clientFromBot";
        // if ($user->role == 2){
        //     $altC->receipent = "clientFromBot2";
        // }
        $altC->clientid = $request->userId == "" ? $c[0]->id : $request->userId;
        $altC->agentid = $user->id;
        $c[0]->languageOfChoice == "English" ? $altC->message = "Please click 1 to 5 for our service rating" : $altC->message = $request->message;
        //$altC->message = $request->message;
        $altC->save();
        $ab = AdminBusy::find($user->id);
        $ab->delete();
        return response()->json(["status"=>"success"]);
    }

    public function dltAndNotifyClient(Request $request){
        $l = LiveChatNotification::where('who',"superadmin")->first();
        if ($l !== NULL){
            $lasm = "Harap Maaf, tidak dapat melayan anda. Sila cuba sebentar lagi.";
            if ($l->language == "English"){
                $lasm = "Sorry, unable to serve you. Please try again later.";
            }
            $l->delete();
            $altC = new ConversationId();
            $altC->receipent = "clientRejected";
            $altC->clientid = $request->userId;
            $altC->agentid = 0;
            $altC->message = $lasm;
            $altC->save();
            return response()->json(["status"=>"success"]);
        }else{
            return response()->json(["status"=>"accepted"]);
        } 
       
    }
}