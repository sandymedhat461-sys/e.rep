<?php

namespace App\Http\Controllers\Auth\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Throwable;

trait ResetsPasswords
{
    abstract protected function passwordBroker(): string;

    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Please enter your email address',
            'email.email'    => 'Please enter a valid email address',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $status = Password::broker($this->passwordBroker())->sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_THROTTLED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please wait before requesting another reset link',
                ], 429);
            }

            return response()->json([
                'success' => true,
                'message' => 'If that email is registered, a password reset link has been sent',
            ]);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'token.required'     => 'Reset token is missing',
            'email.required'     => 'Please enter your email address',
            'email.email'        => 'Please enter a valid email address',
            'password.required'  => 'Please enter a new password',
            'password.min'       => 'Password must be at least 8 characters',
            'password.confirmed' => 'Passwords do not match',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $status = Password::broker($this->passwordBroker())->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, string $password) {
                    $user->forceFill([
                        'password' => $password,
                    ])->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password has been reset successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token',
            ], 400);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }
}
