<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;
use App\Http\Controllers\Controller;
use Validator;
use Hash;

class PassportController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api')->only('logout');
    }

    /**
    * Get a validator for an incoming registration request.
    *
    * @param  array  $data
    * @return \Illuminate\Contracts\Validation\Validator
    */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => 'required|string|alpha_dash|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|max:32|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{3,}$/',
        ]);
        //password_confirmation must be included in this string
    }

    /**
    * Create a new user instance after a valid registration.
    *
    * @param  array  $data
    * @return \App\User
    */

    protected function create(array $data)
    {
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        return $user;
    }

    public function register(Request $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return response()->error($validator->errors(), 422);
        }
        $user = $this->create($request->all());
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['username'] =  $user->username;
        $success['id'] = $user->id;
        return response()->success($success);
    }

    public function login(){
        if(Auth::attempt(['username' => request('username'), 'password' => request('password')])){
            $user = Auth::user();
            if(!$user){abort(404);}
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            $success['username'] =  $user->username;
            $success['id'] = $user->id;
            return response()->success($success);
        }
        else{
            return response()->error(config('error.406'),406);
        }
    }

    public function logout(){
        $user = auth('api')->user();
        $user->token()->revoke();
        return response()->success([
            'user_id' => $user->id,
            'message' => "you've logged out!",
        ]);
    }
}
