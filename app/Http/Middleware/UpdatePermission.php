<?php

namespace App\Http\Middleware;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Auth;
use App\Http\Helper\ResponseBuilder;
use Closure;

class UpdatePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        
        if(Auth::check()){
        
            $role_id = Auth::User()->user_type;
            $roleResult = Role::where("id", $role_id)->first();
            if($roleResult && $roleResult->user_type ==$role){
                return $next($request); 
            }else{
                $status = false;
                $message ="Unauthorized";
                $error = "";
                $data = "";
                $code = 401;                
                return ResponseBuilder::result($status, $message, $error, $data, $code);                
            }
        }else{
            $status = false;
            $message ="Unauthorized";
            $error = "";
            $data = "";
            $code = 401;                
            return ResponseBuilder::result($status, $message, $error, $data, $code);   
            
        }



    }
}
