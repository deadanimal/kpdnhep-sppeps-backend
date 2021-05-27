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
        $user = auth('api')->user();

        $old = DB::table('Permohonan')->where('status_aktif',0)->where('id_pengguna',$user['id'])->where('jenis_permohonan','p1')->first();
   
        if(!is_null($old)){
            if ($request->file('fail_lesen')){
                $path = Storage::putFile('lesen', $request->file('fail_lesen'));
            }else{
                $path = $old->fail_lesen;
            } 
            $old2 = Permohonan::find($old->id);
            $old2->status_aktif = 1;
            $old2->save();
            if (isset($request->panel_bank)){
                $id = DB::table('Info Ekstra')->where('id', $old->id_ekstra)
                ->update([
                    "panel_bank" => $request->panel_bank,
                    "notel" => $request->notel,
                    "nama_panel" => $request->nama_panel,
                    "no_kp" => $request->no_kp,
                    "no_permit" => $request->no_permit,
                    "notel2" => $request->notel2,
                    "pp_eps" => $request->pp_eps == 0 || $request->pp_eps == 1 ? $request->pp_eps : 9,
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
                    $id = DB::table('Info Ekstra')->where('id', $old->id_ekstra)
                    ->update([
                        "pp_eps" => $request->pp_eps == 0 || $request->pp_eps == 1 ? $request->pp_eps : 9,
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
        }else{
            $id = $this->putindb($request);

            $newp = new Permohonan();
            $newp->status_aktif = 1;
            $user = auth('api')->user();
            $newp->id_pengguna = $user['id'];
            $newp->jenis_permohonan = "p1";
            $newp->id_ekstra = $id;
            $newp->save();   
        }
        return response()->json(["status"=>"success"],200);
    
    }

    public function save_renew(Request $request){
        $user = auth('api')->user();

        $old = DB::table('Permohonan')->where('status_aktif',0)->where('id_pengguna',$user['id'])->where('jenis_permohonan','p2')->first();
   
        if(!is_null($old)){
            if ($request->file('fail_lesen')){
                $path = Storage::putFile('lesen', $request->file('fail_lesen'));
            }else{
                $path = $old->fail_lesen;
            } 
            $old2 = Permohonan::find($old->id);
            $old2->status_aktif = 1;
            $old2->save();
            if (isset($request->panel_bank)){
                $id = DB::table('Info Ekstra')->where('id', $old->id_ekstra)
                ->update([
                    "panel_bank" => $request->panel_bank,
                    "notel" => $request->notel,
                    "nama_panel" => $request->nama_panel,
                    "no_kp" => $request->no_kp,
                    "no_permit" => $request->no_permit,
                    "notel2" => $request->notel2,
                    "pp_eps" => $request->pp_eps == 0 || $request->pp_eps == 1 ? $request->pp_eps : 9,
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
                    $id = DB::table('Info Ekstra')->where('id', $old->id_ekstra)
                    ->update([
                        "pp_eps" => $request->pp_eps == 0 || $request->pp_eps == 1 ? $request->pp_eps : 9,
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
        }else{
            $id = $this->putindb($request);

            $newp = new Permohonan();
            $newp->status_aktif = 1;
            $user = auth('api')->user();
            $newp->id_pengguna = $user['id'];
            $newp->jenis_permohonan = "p2";
            $newp->id_ekstra = $id;
            $newp->save();   
        }
        return response()->json(["status"=>"success"],200);
    
    }

    public function retrieveStatus(Request $request){
        $user = auth('api')->user();
        $data = DB::table('Permohonan')->where('id_pengguna',$user['id'])->get();
        foreach($data as $key => $value){

            switch($value->jenis_permohonan){
                case "p1":
                    $value->type = "Permohonan Baharu";
                    break;
                case "p2":
                    $value->type = "Permohonan Pembaharuan";
                    break;
                case "p3":
                    $value->type = "Permohonan Pendua";
                    break;
                case "p4":
                    $value->type = "Permohonan Rayuan";
                    break;
            } 

            $value->date = $value->tarikh_cipta;
            $nric = DB::table('users')->where('id',$value->id_pengguna)->first();
            $value->nric = $nric->no_kp;
            if ($value->status_aktif == 0){
                $value->statuz = "Belum Hantar";
            }else{
                if ($value->status_terkini == "NULL" || $value->status_terkini == ""){
                    $value->statuz = "Sedang Diproses";
                }else{
                    $value->statuz = $value->status_terkini == "GA"? "Gagal" : "Lulus";
                }
            }
        }

        return response()->json($data,200);
    }

    public function getExtraInfo(Request $request){
        $user = auth('api')->user();
        $permohonanLatest = DB::table('Permohonan')->where('jenis_permohonan',$request->p_type)->where('id_pengguna',$user['id'])->orderBy('tarikh_cipta','DESC')->first();
        if(!is_null($permohonanLatest)){
            $last = DB::table('Info Ekstra')->where('id',$permohonanLatest->id_ekstra)->first();
            return response()->json($last,200);
        }
        return response()->json("",200);
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
                "fail_lesen" => $path,
                "nama_faillesen" => $request->nama_faillesen
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
                    "fail_lesen" => $path,
                    "nama_faillesen" => $request->nama_faillesen
                ]);

        }
        return $id;
    }

    public function saveApplication(Request $request){
        $user = auth('api')->user();
        //check if exists
        $old = DB::table('Permohonan')->where('status_aktif',0)->where('id_pengguna',$user['id'])->where('jenis_permohonan','p1')->first();
        
        if(!is_null($old)){

            if ($request->file('fail_lesen')){
                $path = Storage::putFile('lesen', $request->file('fail_lesen'));
            }else{
                if(!is_null($old)){         
                    $ei = DB::table('Info Ekstra')->where('id', $old->id_ekstra)->first();
                    $path = $ei->fail_lesen;
                }else{
                    $path = "";
                }
            }   
    
            if (isset($request->panel_bank)){
                $id = DB::table('Info Ekstra')->where('id', $old->id_ekstra)
                ->update([
                    "panel_bank" => $request->panel_bank,
                    "notel" => $request->notel,
                    "nama_panel" => $request->nama_panel,
                    "no_kp" => $request->no_kp,
                    "no_permit" => $request->no_permit,
                    "notel2" => $request->notel2,
                    "pp_eps" => $request->pp_eps == 0 || $request->pp_eps == 1 ? $request->pp_eps : 9,
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
                    "fail_lesen" => $path,
                    "nama_faillesen" => $request->nama_faillesen
                ]);
    
            }else{
                
                //insert file first
                    //return response()->json(["status"=>$request->pp_eps],200);
                    $id = DB::table('Info Ekstra')->where('id', $old->id_ekstra)
                    ->update([
                        "pp_eps" => $request->pp_eps == 0 || $request->pp_eps == 1 ? $request->pp_eps : 9,
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
                        "fail_lesen" => $path,
                        "nama_faillesen" => $request->nama_faillesen
                    ]);
    
            }
        }else{
            $id = $this->putindb($request);
            $newp = new Permohonan();
            $newp->status_aktif = 0;
            $user = auth('api')->user();
            $newp->id_pengguna = $user['id'];
            $newp->jenis_permohonan = "p1";
            $newp->id_ekstra = $id;
            $newp->save();   
        }      
        
        return response()->json(["status"=>"success"],200);
    }

    public function saveApplicationR(Request $request){
        $user = auth('api')->user();

             //check if exists
            $old = DB::table('Permohonan')->where('status_aktif',0)->where('id_pengguna',$user['id'])->where('jenis_permohonan','p2')->first(); 
            
            if ($request->file('fail_lesen')){
                $path = Storage::putFile('lesen', $request->file('fail_lesen'));
            }else{  
                if(!is_null($old)){         
                    $ei = DB::table('Info Ekstra')->where('id', $old->id_ekstra)->first();
                    $path = $ei->fail_lesen;
                }else{
                    $path = "";
                }
            }   

            if ($request->file('fail_sokongan')){
                $path2 = Storage::putFile('surat_sokong', $request->file('fail_sokongan'));
            }else{
                if(!is_null($old)){
                    $ei = DB::table('Info Ekstra')->where('id', $old->id_ekstra)->first();
                    $path2 = $ei->surat_skg;
                }else{
                    $path2 = "";
                }       
            }

        if(!is_null($old)){

            if ($request->mode == "hantar"){
                DB::table('Permohonan')->where('id', $old->id)
                ->update([
                    "status_aktif" => 1
                ]);
            }
            //focus here
            if (isset($request->panel_bank)){
                $id = DB::table('Info Ekstra')->where('id', $old->id_ekstra)
                ->update([
                    "panel_bank" => $request->panel_bank,
                    "notel" => $request->notel,
                    "nama_panel" => $request->nama_panel,
                    "no_kp" => $request->no_kp,
                    "no_permit" => $request->no_permit,
                    "notel2" => $request->notel2,
                    "lesen" => $request->lesen,
                    "notelori" => $request->notelori,
                    "emelori" => $request->emelori,
                    "alamat1ori" => $request->alamat1ori,
                    "alamat2ori" => $request->alamat2ori,
                    "poskodori" => $request->poskodori,
                    "negeriori" => $request->negeriori,
                    "tahap_pen" => $request->tahap_pen,
                    "fail_lesen" => $path,
                    "surat_skg" => $path2,
                    "sp_eps" => $request->sp_eps,
                    "dari_tahun" => isset($request->dari_tahun) ? $request->dari_tahun : NULL,
                    "p_sampingan" => isset($request->p_sampingan) ? $request->p_sampingan : NULL,
                    "tahun_h" => $request->tahun_h

                ]);
    
            }else{
                
                //insert file first
                    //return response()->json(["status"=>$request->pp_eps],200);
                    $id = DB::table('Info Ekstra')->where('id', $old->id_ekstra)
                    ->update([
                        "pp_eps" => $request->pp_eps == 0 || $request->pp_eps == 1 ? $request->pp_eps : 9,
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
                        "fail_lesen" => $path,
                        "surat_skg" => $path2,
                        "sp_eps" => $request->sp_eps,
                        "dari_tahun" => isset($request->dari_tahun) ? $request->dari_tahun : NULL,
                        "p_sampingan" => isset($request->p_sampingan) ? $request->p_sampingan : NULL,
                        "tahun_h" => $request->tahun_h
                    ]);
    
            }
        }else{
           
            if (isset($request->panel_bank)){
                $id = DB::table('Info Ekstra')->insertGetId([
                    "panel_bank" => $request->panel_bank,
                    "notel" => $request->notel,
                    "nama_panel" => $request->nama_panel,
                    "no_kp" => $request->no_kp,
                    "no_permit" => $request->no_permit,
                    "notel2" => $request->notel2,
                    "lesen" => $request->lesen,
                    "notelori" => $request->notelori,
                    "emelori" => $request->emelori,
                    "alamat1ori" => $request->alamat1ori,
                    "alamat2ori" => $request->alamat2ori,
                    "poskodori" => $request->poskodori,
                    "negeriori" => $request->negeriori,
                    "tahap_pen" => $request->tahap_pen,
                    "fail_lesen" => $path,
                    "surat_skg" => $path2,
                    "sp_eps" => $request->sp_eps,
                    "dari_tahun" => isset($request->dari_tahun) ? $request->dari_tahun : NULL,
                    "p_sampingan" => isset($request->p_sampingan) ? $request->p_sampingan : NULL,
                    "tahun_h" => $request->tahun_h

                ]);
    
            }else{
                
                //insert file first
                    //return response()->json(["status"=>$request->pp_eps],200);
                    $id = DB::table('Info Ekstra')->insertGetId([
                   
                        "pp_eps" => $request->pp_eps == 0 || $request->pp_eps == 1 ? $request->pp_eps : 9,
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
                        "fail_lesen" => $path,
                        "surat_skg" => $path2,
                        "sp_eps" => $request->sp_eps,
                        "dari_tahun" => isset($request->dari_tahun) ? $request->dari_tahun : NULL,
                        "p_sampingan" => isset($request->p_sampingan) ? $request->p_sampingan : NULL,
                        "tahun_h" => $request->tahun_h
                    ]);
    
            }
            $newp = new Permohonan();
            if ($request->mode == "hantar"){
                $newp->status_aktif = 1;
            }else{
                $newp->status_aktif = 0;
            }
           
            $user = auth('api')->user();
            $newp->id_pengguna = $user['id'];
            $newp->jenis_permohonan = "p2";
            $newp->id_ekstra = $id;
            $newp->save();   
        }      
        
        return response()->json(["status"=>"success"],200);
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
            //start simulation
            return "1,2,3,4,5,6";
            //end simulation
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
