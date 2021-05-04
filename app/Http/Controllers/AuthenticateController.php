<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use App\Http\Controllers\AuditController;
use Tymon\JWTAuthExceptions\JWTException;
use App\User;
use Illuminate\Support\Facades\Validator;
use Auth;
use Mail;
use Illuminate\Support\Facades\Hash;
use DateTime;
use DB;
use Storage;

class AuthenticateController extends Controller
{
    public function __construct()
    {
       // Apply the jwt.auth middleware to all methods in this controller
       // except for the authenticate method. We don't want to prevent
       // the user from retrieving their token if they don't already have it
       //$this->middleware('jwt.auth', ['except' => ['authenticate']]);
       $this->middleware('auth:api', ['except' => ['authenticate','sendResetLink','changePassNow','authenticateOthers','downloadPicture']]);
    }

    public function index()
    {
        $user = $this->me();
        $theuser = User::find($user->getData()->id);
        // $theuser->save();
        // $user->gambar = Storage::temporaryUrl(
        //     $theuser->gambar, now()->addMinutes(5)
        // );
        return $user;
    }  

    public function updateUser(Request $request)
    {
        $user = $this->me();
        $theuser = User::find($user->getData()->id);
        if (strlen($request->name) > 0){
            $theuser->name = $request->name;
        }        

        if (strlen($request->email)>0){
            //check if email exists
            $duplicate = User::where('email',$request->email)->count();

            if ($duplicate > 0){
                return response()->json(['status'=>'This Email Already Exists'],200);
            }else{
                $theuser->email = $request->email;
            }
        }     
        $audit_c = new AuditController(); 
        $audit_c->addAudit("update","change profile information");
        $theuser->save();
        return response()->json(['status'=>'success'],200);
    }

    public function getUsers(){
        $user = User::orderBy("id")->get();
        return response()->json($user, 200);
    }

    public function deleteUser(Request $request){
        $theuser = User::find($request->id);
        $theuser->delete();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("delete","delete user " . $theuser->email);
        return response()->json(["status"=>"success"], 200);
    }

    public function disableUser(Request $request){
        $theuser = User::find($request->id);
        if ($request->disabled == 1){
            $theuser->disabled = 0;
        }else{
            $theuser->disabled = 1;
        }
        $theuser->save();
        $audit_c = new AuditController(); 
        $audit_c->addAudit("update","disable user " . $theuser->email);
        return response()->json(["status"=>"success"], 200);
    }

    public function changePassword(Request $request){
            $user = User::find($request->id);
            if (Hash::check($request->currentpassword, $user->password)) {
                // Success
                $user->password = Hash::make($request->newpassword);
                $user->save();
                $audit_c = new AuditController(); 
                $audit_c->addAudit("update","change password");
                return response()->json(['status' => 'success'], 200);
            }else{
                return response()->json(['status' => 'Wrong Current Password'], 200);
            }
    }

    public function authenticate(Request $request)
    {
       
        $theuser = User::where("no_kp",$request->id)->where("level_akses",0)->first();
 
        if ($theuser === NULL){
            return response()->json(['error' => 'Incorrect Credentials'], 401);
        }

        if ($theuser->status_aktif == 0){
            return response()->json(['error' => 'account_not_active'], 401);
        }
    
        try {            
            // verify the credentials and create a token for the user
            if (! $token = auth('api')->attempt(["no_kp"=>$request->id, "password"=>$request->password])) {
                return response()->json(['error' => 'Incorrect Credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong
            return response()->json(['error' => 'could not create token'], 500);
        }
      
        // if no errors are encountered we can return a JWT
        // $audit_c = new AuditController(); 
        // $audit_c->addAudit("update","login");
    
        return $this->respondWithToken($token);
    }

        /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        //$user = $this->me();
        auth('api')->logout();
        //$theuser = User::find($user->getData()->id);
        //$theuser->last_logout = new DateTime();
        //$theuser->save();
        // $audit_c = new AuditController(); 
        // $audit_c->addAudit("update","logout",$user->getData()->id);
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }
     /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }  

public function sendResetLink(Request $request){
            //Create Password Reset Token  
            //You can add validation login here
            $user = DB::table('users')->where('email', '=', $request->email)
            ->first();

            //Check if the user exists
            if ($user === NULL) {
                return response()->json(['status'=>'Email Does Not Exist'],200);
            }

            $tokenData = DB::table('password_resets')
            ->where('email', $request->email)->first();
        
            if ($tokenData !== NULL){
                //Delete the token
                $result = DB::table('password_resets')->where('email', $user->email)
                ->delete();
            }

            DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => bin2hex(random_bytes(60)),
            'created_at' => new DateTime()
            ]);
            
            //Get the token just created above
            $tokenData = DB::table('password_resets')
            ->where('email', $request->email)->first();
    
            if ($this->sendResetEmail($request->email, $tokenData->token)) {
                return response()->json(['status'=>'A reset link has been sent to your email address.'],200);
            } else {
                return response()->json(['status'=>'Error Has Occured'],400);
            }
}

    private function sendResetEmail($email, $token)
    {
        //Retrieve the user from the database
        $user = DB::table('users')->where('email', $email)->select('name', 'email')->first();
        //Generate, the password reset link. The token generated is embedded in the link
        $link = env('ADMIN_ZAKAT_URL') . '#/auth/reset?token=' . $token . '&email=' . urlencode($user->email);
        
        Mail::send('resetMail', ["link"=>$link], function($message) use ($user){
            $message->to($user->email, 'Reset Password Link')->subject("Reset Password Link");
            $message->from('maklumbalasaduan@zakatselangor.com.my','maklumbalasaduan@zakatselangor.com.my');
        });

        return response()->json(['status'=>"success"]);
        
    }

    public function changePassNow(Request $request){
                //Validate input
            $validator = Validator::make($request->all(), [
                'truemail' => 'required|email|exists:users,email',
                'email' => 'required',
                'token' => 'required']);

            //check if payload is valid before moving on
            if ($validator->fails()) {
                return response()->json(['status'=>'Please fill the form properly'],200);
            }

            $password = $request->email;
        // Validate the token
            $tokenData = DB::table('password_resets')
            ->where('token', $request->token)->first();

        // Redirect the user back to the password reset request form if the token is invalid
            if (!$tokenData) return response()->json(['status'=>'Invalid Token'],200);

            $user = User::where('email', $tokenData->email)->first();
        // Redirect the user back if the email is invalid
            if (!$user) return response()->json(['status'=>'Invalid Email'],200);

        //Hash and update the new password
            $user->password = \Hash::make($password);
            $user->update(); //or $user->save();

            //login the user immediately they change password successfully
//            Auth::login($user);

            //Delete the token
            DB::table('password_resets')->where('email', $user->email)
            ->delete();

            //Send Email Reset Success Email
            return response()->json(['status'=>'success'],200);
    }

    public function authenticateOthers(Request $request) {
  
        $theuser = User::where("emel",$request->id)->where("level_akses","!=",0)->first();
            if ($theuser === NULL){
                $theuser = User::where("username",$request->id)->where("level_akses","!=",0)->first();
                
                if ($theuser === NULL){
                     return response()->json(['error' => 'Incorrect Credentials'], 401);
                }else{
                    if ($theuser->status_aktif == 0){
                        return response()->json(['error' => 'account_not_active'], 401);
                    }
    
                    try {            
                        // verify the credentials and create a token for the user
                        if (! $token = auth('api')->attempt(["username"=>$request->id, "password"=>$request->password])) {
                            return response()->json(['error' => 'Incorrect Credentials'], 401);
                        }
                    } catch (JWTException $e) {
                        // something went wrong
                        return response()->json(['error' => 'could not create token'], 500);
                    }
                  
                    // if no errors are encountered we can return a JWT
                    // $audit_c = new AuditController(); 
                    // $audit_c->addAudit("update","login");
                
                    return $this->respondWithToken($token);
                }

            }else{
               
                if ($theuser->status_aktif == 0){
                    return response()->json(['error' => 'account_not_active'], 401);
                }

                try {            
                    // verify the credentials and create a token for the user
                    if (! $token = auth('api')->attempt(["emel"=>$request->id, "password"=>$request->password])) {
                        return response()->json(['error' => 'Incorrect Credentials'], 401);
                    }
                } catch (JWTException $e) {
                    // something went wrong
                    return response()->json(['error' => 'could not create token'], 500);
                }
              
                // if no errors are encountered we can return a JWT
                // $audit_c = new AuditController(); 
                // $audit_c->addAudit("update","login");
            
                return $this->respondWithToken($token);

            }           
  
    }
    public function downloadPicture(Request $request){
        //dd($request->token);
        $user = auth()->setToken($request->token)->user();
        //dd($user->gambar);
        $urlphoto = explode("/",$user->gambar);
        return Storage::disk('pp')->download($urlphoto[1]);
    }
}