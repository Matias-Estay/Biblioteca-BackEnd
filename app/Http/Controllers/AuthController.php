<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use DB;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }
        $request->session()->regenerate();
        $user = (object)[];
        $user->name = $request->user()->name;
        $user->email = $request->user()->email;
        return $user;
    }

    public function loggedIn(Request $request){
        $user = Auth::user();
        if($user){
            return true;
        }else{
            return false;
        }
    }

    public function GET_UserType(Request $request){
        $user = Auth::user();
        $type = DB::table('user_types')->select("user_types.type")->join('users','users.type','=','user_types.id')->
        where('users.id','=',$user->type)->get();
        return $type[0];
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
    }

}
