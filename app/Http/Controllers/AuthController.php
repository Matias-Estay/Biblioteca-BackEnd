<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if($request->user()){
            return true;
        }else{
            return false;
        }
        return false;
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
    }
}
