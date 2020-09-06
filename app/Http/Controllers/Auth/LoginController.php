<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{

    use AuthenticatesUsers;


    protected function attemptLogin(Request $request) 
    {
        $token = $this->guard()->attempt($this->credentials($request));

        if(! $token) {
            return false;
        }

        // get the authemticatited user
        $user = $this->guard()->user();

        if($user instanceOf MustVerifyEmail && !$user->hasVerifiedEmail()) {
            return false;
        }

        // set user token
        $this->guard()->setToken($token);

        return true;
    }

    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        //get token from auth guard
        $token = (string)$this->guard()->getToken();

        //extract expiry date of token
        $expiration = $this->guard()->getPayload()->get('exp');

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiration
        ]);
    }

    protected function sendFailedLoginResponse(Request $request) 
    {
        $user = $this->guard()->user();

        if($user instanceOf MustVerifyEmail && !$user->hasVerifiedEmail()) {
            return response()->json([
                'errors' => [
                    'verification' => 'you need to verify your email account'
                ]
            ]);
        }

        throw ValidationException::withMessages([
            $this->username() => 'Invalid credentials'
        ]);
    }

    public function logout() 
    {
        $this->guard()->logout();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
