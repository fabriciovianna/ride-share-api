<?php

namespace App\Http\Controllers;

use App\Http\Requests\Login\SubmitRequest;
use App\Http\Requests\Login\VerifyRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Response;

class LoginController extends Controller
{
    public function submit(SubmitRequest $request)
    {
        $user = User::firstOrCreate($request->validated());

        if (!$user)
            return response()->json(['message' => 'Could not process a user with that phone number.'], 401);

        $loginCode = rand(111111, 999999);
        $user->login_code = $loginCode;
        $user->save();

        return response()->json(['message' => 'Token validation generated sucessfully.', 'token' => $loginCode], Response::HTTP_OK);
    }

    public function verify(VerifyRequest $request)
    {
        $user = User::where('phone', $request->phone)->where('login_code', $request->login_code)->first();

        if ($user) {

            $user->update(['login_code' => null]);

            return $user->createToken($request->login_code)->plainTextToken;
        }
        
        return response()->json(['message' => 'Invalid verification code'], Response::HTTP_BAD_REQUEST);
    }
}
