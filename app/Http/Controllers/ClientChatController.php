<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Event;
use JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use DB;
use Auth;
use App\Conversationz;
use App\ConversationId;
use App\LiveChatNotification;
use App\Customer;
use App\UserReview;
use App\AdminBusy;
use DateTime;

class ClientChatController extends Controller
{
    // public function __construct()
    // {
    //    // Apply the jwt.auth middleware to all methods in this controller
    //    // except for the authenticate method. We don't want to prevent
    //    // the user from retrieving their token if they don't already have it
    //    $this->middleware('jwt.auth');
    // }

    public function index(Request $request)
    {
        return view("clientchat");
    }

    public function acceptRequest(Request $request){
        $userId = LiveChatNotification::where("waiting",0)->get();
        
        if ($userId->isEmpty()){
            $waiting = LiveChatNotification::where("waiting",1)->first();
            if (!$waiting){
                return response()->json("Gone",200);
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
              
                return response()->json("success",200);
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
            return response()->json("success",200);
        }
    }

    public function sendmsg(Request $request){        
        $id = substr($request->id, 4,-4);
        $m = new Conversationz();
        $m->message = $request->message;
        if ($request->agentId == ""){
            $agentid = Customer::find($request->id);
            $m->recipient = $agentid->agentId;            
        }else{
            $m->recipient = $request->agentId;            
        }        
        $m->clientId = $id;
        $m->sendtime = new DateTime();
        $m->save();
        $altC = new ConversationId();
        $altC->receipent = "agent";
        $altC->clientid = $id;
        $altC->agentid = $request->agentId;
        $altC->message = $request->message;
        $altC->save();

       // Event::dispatch("s",array($m));
        return $request->id == "" ? $c[0]->id : "success";
        // $cid = new ConversationId();
        // $cid->convoid = $m->id;
        // $cid->save();
    }

    public function getclientmsg(Request $request){
        $id = substr($request->id, 4,-4);
        // $id = substr($id, -4);
        $message = ConversationId::where([["receipent","client"],['clientid',$id]])->orWhere([['receipent','clientFromBot'],['clientid',$id]])->orWhere([['receipent','clientRejected'],['clientid',$id]])->first();
        // $message = ConversationId::where("receipent","clientFromBot")->where("clientid",$id)->first();
        //var_dump($id);
        if ($message){
            $m = $message;
            $message->delete();
            return response()->json($m,200);
        }else{
            return response()->json("nothing");
        }        
    }

    function giveFeedback(Request $request){
        $ur = new UserReview();
        $id = substr($request->userId, 4,-4);
        $ur->userid = $id;
        $ur->feedback = $request->fbk;
        $ur->star = $request->star;
        $ur->save();
        // $u = Customer::find($id);
        // $u->lasttime = new DateTime();
        // $u->save();
        $c = array(["message" => 'Sekian, Terima Kasih. Harap saya dapat bantu anda.'],["stat" => 'ok'],["receipent" => 'client']);
        return response()->json($c,200);
    }
}