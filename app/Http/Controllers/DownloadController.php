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

    public function userDownload(Request $request){
        //return Storage::download($path);
        //$url = Storage::get($path);
        //return response()->file("mykad/qL57sWqBpGpZLGrbiOPSjcevnfIlnz3MiLD8neY3.png");

        $path = storage_path('app/mykad/' . 'qL57sWqBpGpZLGrbiOPSjcevnfIlnz3MiLD8neY3.png');

        if (!File::exists($path)) {
            abort(404);
        }
    
        $file = File::get($path);
        $type = File::mimeType($path);
    
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
    
        return $response;
    }
    
}