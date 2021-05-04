<?php

namespace App\Http\Controllers;

use JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use DB;
use Carbon\Carbon;

class ReportScheduleController extends Controller
{
    public function __invoke()
    {
       $c=1;
       if ($c == 1){

        DB::table('admin_busy')->whereNotNull("userid")->delete();
        DB::table('conversation_id')->whereNotNull("id")->delete();
        DB::table('live_chat_notification')->whereNotNull("id")->delete();
        DB::table('blocked_ic')->whereNotNull("ic")->delete();

        //$start = Carbon::today();
        $start = Carbon::yesterday();
       //$end = Carbon::today();
       $top_categories = array();
       $temp_categories = array();
       $temp_values = array();
 
       $yesterday_record = DB::table('conversations')->whereDate('created_at',$start->toDateString())->get();
       $customers = DB::table('customer')->whereDate('created_at',$start->toDateString())->get();
    //    $yesterday_record = DB::table('conversations')->whereBetween('created_at',[$start->toDateTimeString(),$end->toDateTimeString()])->get();
    //    var_dump($start->toDateTimeString());
    //    var_dump($end);
       foreach ($yesterday_record as $key => $value) {
           if ($value->clientId == 0){
               if (str_contains($value->botMsg, $value->message)){
                   
                   if (!in_array($value->message,$temp_categories)){
                       array_push($temp_categories,$value->message);
                       array_push($temp_values,1);
                   }else{
                       $needle = array_search($value->message, $temp_categories);
                       $temp_values[$needle] = $temp_values[$needle] + 1;
                   }
               }
           }
       }

       //get the rest
       $totalUsers = 0;
       $totalByAgent = 0;
       $diffAvgWait = 0;
       $top_values = array();   
       $avgSpent = 0;

       foreach ($customers as $key => $value) {
           $totalUsers = $totalUsers + 1;
           if ($value->agentId !== NULL){
                if ($value->transferTime !== NULL && $value->attendtime !== NULL){                   
        
                    $first = DB::table("conversations")->whereDate('created_at',$start->toDateString())->where('recipient',$value->id)->where('clientId',$value->agentId)->first();
                    
                    if ($first !== NULL){ 
                    
                    $startWait = Carbon::parse($value->transferTime);
                    $endWait = Carbon::parse($value->attendtime);
                    $diffAvgWait =  $diffAvgWait + $startWait->diffInSeconds($endWait);
                        $totalByAgent = $totalByAgent + 1;
                        $last = DB::table("conversations")->whereDate('created_at',$start->toDateString())->where('recipient',$value->id)->where('clientId',$value->agentId)->latest()->first();
                        
                        $startWait = Carbon::parse($first->created_at);
                        $endWait = Carbon::parse($last->created_at);
                        $avgSpent = $avgSpent + $startWait->diffInSeconds($endWait);
                    }
                    
                }
           }
       }

       if ($diffAvgWait != 0){
           $diffAvgWait = $diffAvgWait / $totalByAgent;           
       }

       if ($avgSpent != 0){
           $avgSpent = $avgSpent / $totalByAgent;
       }

       arsort($temp_values);
       $counterTop = 0;
       foreach ($temp_values as $key => $value) {
           array_push($top_categories,$temp_categories[$key]);
           if ($counterTop < 5){
               array_push($top_values,$value);
               $counterTop = $counterTop + 1;
           }
       }
        //process average rating
        $avgR = 0;
        $ratings = DB::table('user_review')->whereDate('created_at',$start->toDateString())->get();
        $avgRatings = 0;

        foreach ($ratings as $key => $value) {
            $avgRatings = $avgRatings + $value->star;
        }

        if (count($ratings) > 0){
            $avgR = round($avgRatings / count($ratings),1);
        }
        
       // dd($avgRatings);
        //dd(count($ratings));

       //sort
              
       DB::table("usage_report")->insert([
         "top_1" => isset($top_categories[0]) ? $top_categories[0] : " ",
         "top_2" => isset($top_categories[1]) ? $top_categories[1] :  " ",
         "top_3" => isset($top_categories[2]) ? $top_categories[2] : " ",
         "top_4" => isset($top_categories[3]) ? $top_categories[3] : " ",
         "top_5" => isset($top_categories[4]) ? $top_categories[4] : " ",
         "date" => $start->toDateString(),
         "averageWait" => round($diffAvgWait),
         "totalUser" => $totalUsers,
         "averageSpent" => round($avgSpent),
         "averageRating" => $avgR,
       ]);
       
       $existingDashboard = DB::table('dashboard_report')->first();

       $avgRatings = 0;

       $totalUsers = $totalUsers + $existingDashboard->totalUser;

            //process average rating
            $ratings = DB::table('user_review')->whereDate('created_at',$start->toDateString())->get();

            foreach ($ratings as $key => $value) {
                $avgRatings = $avgRatings + $value->star;
            }

            $totalRatingCount = count($ratings);
           
            if ($avgRatings != 0){
                $avgRatings = $avgRatings + ($existingDashboard->averageRating * $existingDashboard->totalRatingCount);
                $totalRatingCount = $existingDashboard->totalRatingCount + $totalRatingCount;
                $avgRatings = $avgRatings / $totalRatingCount;
            }else{
                $avgRatings = $existingDashboard->averageRating;
                $totalRatingCount = $existingDashboard->totalRatingCount;
            }
            //end process

            //process average wait n spent
            if ($totalByAgent != 0){
                $diffAvgWait = $diffAvgWait * $totalByAgent;
                $avgSpent = $avgSpent * $totalByAgent;

                $diffAvgWait = $diffAvgWait + ($existingDashboard->averageWait * $existingDashboard->totalLiveChat);
                $avgSpent = $avgSpent + ($existingDashboard->averageSpent * $existingDashboard->totalLiveChat);
                              
                $totalByAgent = $existingDashboard->totalLiveChat + $totalByAgent;

                $diffAvgWait = $diffAvgWait / $totalByAgent;
                $avgSpent = $avgSpent / $totalByAgent;
            }else{
                $totalByAgent = $existingDashboard->totalLiveChat;
                $avgSpent = $existingDashboard->averageSpent;
                $diffAvgWait = $existingDashboard->averageWait;
            }
                                 
            DB::table("dashboard_report")->where('id',1)->update([
                "top_1" => isset($top_values[0]) ? $top_values[0] : 0,
                "top_2" => isset($top_values[1]) ? $top_values[1] : 0,
                "top_3" => isset($top_values[2]) ? $top_values[2] : 0,
                "top_4" => isset($top_values[3]) ? $top_values[3] : 0,
                "top_5" => isset($top_values[4]) ? $top_values[4] : 0,
                "top_1_name" => isset($top_categories[0]) ? $top_categories[0] : " ",
                "top_2_name" => isset($top_categories[1]) ? $top_categories[1] :  " ",
                "top_3_name" => isset($top_categories[2]) ? $top_categories[2] : " ",
                "top_4_name" => isset($top_categories[3]) ? $top_categories[3] : " ",
                "top_5_name" => isset($top_categories[4]) ? $top_categories[4] : " ",
                strtolower($start->format('l')) => $totalUsers-$existingDashboard->totalUser,
                "averageRating" => round($avgRatings),
                "averageWait" => round($diffAvgWait),
                "totalUser" => $totalUsers,
                "averageSpent" => round($avgSpent),
            ]);

            DB::table('conversations')->whereNotNull("botMsg")->delete();
       
    }else{
        // DB::table("dashboard_report")->where('id',1)->update([
        //         "top_1" => 1,
        //         "top_2" => 2,
        //         "top_3" => 4,
        //         "top_4" => 2,
        //         "top_5" => 3,
        //         "top_1_name" => "Zakat Fitrah",
        //         "top_2_name" =>  "Agihan",
        //         "top_3_name" => "Bayar Zakat",
        //         "top_4_name" => "Umum",
        //         "top_5_name" => "Kira",
        //         "averageRating" => 3,
        //         "averageWait" => 126,
        //         "totalUser" => 5,
        //         "averageSpent" => 62,
        //         "monday" => 4,
        //         "tuesday" => 1,
        //         "wednesday" => 0,
        //         "thursday" => 0,
        //         "friday" => 0,
        //         "saturday" => 0,
        //         "sunday" => 0,
        //         "totalRatingCount" => 0,
        //         "totalLiveChat" => 0,
        //     ]);
        // DB::table("dashboard_report")->where('id',1)->update([
        //         "top_1" => 0,
        //         "top_2" => 0,
        //         "top_3" => 0,
        //         "top_4" => 0,
        //         "top_5" => 0,
        //         "top_1_name" => " ",
        //         "top_2_name" =>  " ",
        //         "top_3_name" => " ",
        //         "top_4_name" => " ",
        //         "top_5_name" => " ",
        //         "averageRating" => 0,
        //         "averageWait" => 0,
        //         "totalUser" => 0,
        //         "averageSpent" => 0,
        //         "monday" => 0,
        //         "tuesday" => 0,
        //         "wednesday" => 0,
        //         "thursday" => 0,
        //         "friday" => 0,
        //         "saturday" => 0,
        //         "sunday" => 0,
        //         "totalRatingCount" => 0,
        //         "totalLiveChat" => 0,
        //     ]);
        // DB::table("usage_report")->insert([
        //         "top_1" => "Agihan",
        //         "top_2" => "Bayar Zakat",
        //         "top_3" => "Zakat Fitrah",
        //         "top_4" => "",
        //         "top_5" => "",
        //         "averageRating" => 4,
        //         "averageWait" => 129,
        //         "totalUser" => 2,
        //         "averageSpent" => 44,
        //         "date" => "2021-03-04",               
        //     ]);
        // DB::table("audit_trail")->whereNotNull('action')->update([
        //        "userid" => 29,            
        //     ]);
        DB::table("bot_questions")->whereNotNull('id')->update(
            [
                "delete" => 0
            ]
            );
    }
    } 

}