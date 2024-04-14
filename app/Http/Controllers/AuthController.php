<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Rules\Password as FortifyPassword;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
                Auth::logout();
                return response()->json(['error' => 'Email not verified'], 403);
            }

            return $this->authSuccessResponse(Auth::user());
        }
        return $this->unauthorizedErrorResponse('Invalid credentials');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'dob' => 'required|date',
            'phone' => 'required|string',
            'password' => 'required',
            'address' => 'required|string',
            'playerType' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'dob' => $request->dob,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'address' => $request->address,
            'playerType' => $request->input('playerType', 'Batsman'),
        ]);

        event(new Registered($user));

        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            return response()->json(['error' => 'Email verification required'], 403);
        }
        return $this->authSuccessResponse($user);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }

        try {
            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json(['message' => 'Password reset link sent to your email address']);
            }

            return $this->clientErrorResponse(trans($status));
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to send password reset email. Please try again later.');
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', new FortifyPassword],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }

        $status = Password::reset(
            $request->only('email', 'password', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => bcrypt($password)])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password has been reset successfully']);
        }

        return $this->clientErrorResponse(trans($status));
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    protected function validationErrorResponse($message)
    {
        return response()->json(['error' => $message, 'code' => 400], 400);
    }

    protected function unauthorizedErrorResponse($message)
    {
        return response()->json(['error' => $message, 'code' => 401], 401);
    }

    protected function clientErrorResponse($message)
    {
        return response()->json(['error' => $message, 'code' => 400], 400);
    }

    protected function serverErrorResponse($message)
    {
        return response()->json(['error' => $message, 'code' => 500], 500);
    }

    protected function authSuccessResponse($user)
    {
        $userDetails = $user->only('id', 'name', 'username', 'email', 'dob', 'phone', 'address', 'playerType', 'profile_photo_url');
        return response()->json(['token' => $user->createToken('auth-token')->plainTextToken, 'user' => $userDetails]);
    }
}
