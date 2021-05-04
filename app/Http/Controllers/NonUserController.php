<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Storage;
use File;
use Response;

class NonUserController extends Controller
{
  
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
    
    public function checkUrlVars(Request $request){
	    $user = User::where("no_kp",$request->no_kp)->where("code_daftar",$request->kod)->select("no_kp","code_daftar","emel")->get();
	    
	    if (empty($user[0]))
	    	return response()->json(["status"=>"failed"],200);
	    else
		    return response()->json(["status"=>"success","info"=>$user],200);

    }
    public function register(Request $request){
        $user = User::where("code_daftar",$request->koddaftar)->first();
      
        $user->jantina = $request->jantina == "male"? 0 : 1;
        $user->alamat1 = $request->alamat1;
        $user->alamat2 = $request->alamat2;
        $user->poskod = $request->poskod;
        $user->negeri = $request->negeri;
        $user->notelbimbit = $request->no_telefon1 . $request->no_telefon2;
        //$user->cawangan_kp = $request->cawangan_kp;
        $path = Storage::putFile('mykad', $request->file('file1'));
        $path2 = Storage::putFile('profilePicture', $request->file('file2'));
        $user->gambar_kp = $path;
        $user->gambar = $path2;
        $user->code_daftar = -1;
        $user->status_aktif = 1;
        $user->save();
        
        //return response()->json(["status"=>"success","path"=>$path]);
        //$this->userDownload($path);
        //$this->download(Storage::disk('s3')->files('sppeps2/'.$randint));
        return response()->json(["status"=>"success"],200);
    }
  
}
