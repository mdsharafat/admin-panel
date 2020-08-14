<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $credential =  $request->only('email', 'password');
        if(Auth::attempt($credential)){
            $token = Str::random(80);
            Auth::user()->api_token = $token;
            Auth::user()->save();
            $isAdmin = Auth::user()->isAdmin();
            return response()->json(['token' => $token, 'isAdmin' => $isAdmin], 200);
        }

        return response()->json(['status' => 'Credential mismatch.'], 403);
    }

    public function verify(Request $request)
    {
        return $request->user()->only('name', 'email');
    }
}
