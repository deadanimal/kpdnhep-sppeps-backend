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
use App\Permohonan;
use App\Permit;
use Storage;
use DB;

class ApplicantController extends Controller
{
    public function __construct()
    {
       // Apply the jwt.auth middleware to all methods in this controller
       // except for the authenticate method. We don't want to prevent
       // the user from retrieving their token if they don't already have it
       $this->middleware('auth:api');
    }

    public function newApplication(Request $request){
        $id = $this->putindb($request);

        $newp = new Permohonan();
        $newp->status_aktif = 1;
        $user = auth('api')->user();
        $newp->id_pengguna = $user['id'];
        $newp->jenis_permohonan = "p1";
        $newp->id_ekstra = $id;
        $newp->save();   
        
        return response()->json(["status"=>"success"],200);
    
    }

    public function retrieveStatus(Request $request){
        $user = auth('api')->user();
        $data = DB::table('Permohonan')->where('id_pengguna',$user['id'])->get();
        $data->type = $data->jenis_permohonan == "p1" ? "Permohonan Baharu" : "";
        return response()->json($data,200);
    }

    private function putindb(Request $request){
        $id = 0;

        if ($request->file('fail_lesen')){
            $path = Storage::putFile('lesen', $request->file('fail_lesen'));
        }else{
            $path = "";
        }   

        if (isset($request->panel_bank)){

            $id = DB::table('Info Ekstra')->insertGetId([
                "panel_bank" => $request->panel_bank,
                "notel" => $request->notel,
                "nama_panel" => $request->nama_panel,
                "no_kp" => $request->no_kp,
                "no_permit" => $request->no_permit,
                "notel2" => $request->notel2,
                "pp_eps" => $request->pp_eps ? $request->pp_eps : 9,
                "skop_tugas" => $request->skop_tugas,
                "lesen" => $request->lesen,
                "notelori" => $request->notelori,
                "emelori" => $request->emelori,
                "alamat1ori" => $request->alamat1ori,
                "alamat2ori" => $request->alamat2ori,
                "poskodori" => $request->poskodori,
                "negeriori" => $request->negeriori,
                "pk_sek" => $request->pek_sek,
                "tahap_pen" => $request->tahap_pen,
                "fail_lesen" => $path
            ]);

        }else{
            //insert file first
                //return response()->json(["status"=>$request->pp_eps],200);
                $id = DB::table('Info Ekstra')->insertGetId([
                    "pp_eps" => !empty($request->pp_eps) ? $request->pp_eps : 9,
                    "skop_tugas" => $request->skop_tugas,
                    "lesen" => $request->lesen,
                    "notelori" => $request->notelori,
                    "emelori" => $request->emelori,
                    "alamat1ori" => $request->alamat1ori,
                    "alamat2ori" => $request->alamat2ori,
                    "poskodori" => $request->poskodori,
                    "negeriori" => $request->negeriori,
                    "pk_sek" => $request->pek_sek,
                    "tahap_pen" => $request->tahap_pen,
                    "fail_lesen" => $path
                ]);

        }
        return $id;
    }

    public function saveApplication(Request $request){
        $id = $this->putindb($request);

        $newp = new Permohonan();
        $newp->status_aktif = 0;
        $user = auth('api')->user();
        $newp->id_pengguna = $user['id'];
        $newp->jenis_permohonan = "p1";
        $newp->id_ekstra = $id;
        $newp->save();   
        
        return response()->json(["status"=>"success"],200);
    }

    public function checkMyKad(Request $request){

        // $response = Http::asForm()->post($mykadvalidationlink[0]->value,[
        //     'mykad' => $mykadNum,
        //     'requestType' => 'kutipan',
        //     'btnsimpan' => 'simpan'
        // ]);

        $mykadno = $request->myKadId;
        //if ($mykadno == "999999999999" || $mykadno == "019999999999"){
            //check if ic already exists
            $record = User::where("no_kp",$mykadno)->get();
            
            if (empty($record[0])){
                return response()->json(["status"=>"success"],200);
            }else{
                //means havent complete registration
                if ($record[0]->code_daftar == -1){
                    return response()->json(["status"=>"backtologin"],200);
                }else{
                    return response()->json(["status"=>"sendtothirdpage","no_kp"=> $record[0]->no_kp,"code_daftar"=> $record[0]->code_daftar],200);
                }
            }
        // }          
        // else
        //     return response()->json("failed",400);           
    }

    public function savePre(Request $request)
    {    
	    //check if exists before
	   // $user = User::where("no_kp","=",$request->no_kp)->get();
	   // dd($user);
	    $user = new User;
        $user->emel = $request->email;
        $user->password = Hash::make($request->password);
        $user->no_kp = $request->no_kp;
        $user->code_daftar = rand(10000000,99999999);
        $user->status_aktif = 0;
        $user->level_akses = 0;
	    $user->save();
	    return response()->json(["status"=>"success","code"=> $user->code_daftar],200);
    }

    public function checkGotAppliedBefore(Request $request){
        $user = auth('api')->user();
        $userid = $user['id'];
        $result = $this->doVerification($request->user_filter,$userid);
        return response()->json(["status"=>$result],200);
    }

    public function doVerification($user_filter,$userid){
       // $allowed_pages = "";
        if ($user_filter == "all"){
            $got = DB::table('Permohonan')->where('jenis_permohonan',"p1")->where('id_pengguna',$userid)->where('status_aktif',1)->first();
            if (!empty($got)){
                //check if failed or not
                if ($got->status_terkini != NULL && $got->status_terkini != "" && $got->status_terkini == "GA"){
                    $permohonan = Permohonan::where('jenis_permohonan',"p4")->orderBy('tarikh_cipta','DESC')->get();
                    if (count($permohonan) > 0 && $permohonan[0]->status_terkini == "LU"){
                        $permit = Permit::where("cipta_oleh",$got->id_pengguna)->orderBy('tarikh_cipta','DESC')->get();
                    
                        if (count($permit) > 0 && $permit[0]->tarikh_tamat <= date()){
                            //return response()->json(["status"=>"3"],200);
                            return "3";
                        }else if (count($permit) > 0 && $permit[0]->tarikh_tamat > date()){
                            //return response()->json(["status"=>"2"],200);
                            return "2";
                        }
                    }else{
                        //return response()->json(["status"=>"4"],200);
                        return "4";
                    }
                }else if($got->status_terkini != NULL && $got->status_terkini != "" && $got->status_terkini == "LU"){
                    //check if expired
                    $permit = Permit::where("cipta_oleh",$got->id_pengguna)->orderBy('tarikh_cipta','DESC')->get();
                    
                    if (count($permit) > 0 && $permit[0]->tarikh_tamat <= date()){
                       // return response()->json(["status"=>"3"],200);
                       return "3";
                    }else if (count($permit) > 0 && $permit[0]->tarikh_tamat > date()){
                        //return response()->json(["status"=>"2"],200);
                        return "2";
                    }
                   
                    //return response()->json(["status"=>"0"],200);
                    return "0";
                }else{
                    //return response()->json(["status"=>"0"],200);
                    return "0";
                }
               // $allowed_pages = $allowed_pages + "1,";
            }else{
                //return response()->json(["status"=>"1"],200);
                return "1";
            }
            //$got = DB::table('Permohonan')->where('jenis_permohonan',"p2")
        }else{

        }
    }
   
}
