<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller {

    public function signup(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        $user = new User([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password'))
        ]);

        $user->save();

        return response()->json([
            'message' => 'Successfully created user'
        ], 201);
    }

    public function signin(Request $request)
    {
        $this->validate($request, [            
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $credentials = $request->only('email', 'password');
        try {
            if(!$token = JWTAuth::attempt($credentials)){
                return response()->json([
                    'error' => 'Invalid Credentials!',
                ], 401);
            }
        }catch (JWTException $e) {
            return response()->json([
                'error' => 'Could not create token'
            ], 500);
        }
        // I also want to return the user object if login is successful
        $user = Auth::user();
        
         return response()->json([
            'token' => $token, 'user' => $user
        ], 200);
        /*
        return response()->json([
            'token' => $token
        ], 200);
        */
    }
}