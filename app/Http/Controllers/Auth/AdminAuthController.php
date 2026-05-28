<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\ResetsPasswords;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Support\PersonalAccessTokenLabel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Throwable;

class AdminAuthController extends Controller
{
    use ResetsPasswords;

    protected function passwordBroker(): string
    {
        return 'admin';
    }


    #[OA\Post(
        path: '/api/auth/admin/register',
        summary: 'Register a new admin',
        responses: [new OA\Response(response: 201, description: 'Success')]
    )]
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'digits:11'],
            'email' => ['required', 'email', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'full_name.required'  => 'Please enter your full name',
            'phone.required'      => 'Please enter your phone number',
            'phone.digits'        => 'Phone number must be exactly 11 digits',
            'email.required'      => 'Please enter your email address',
            'email.email'         => 'Please enter a valid email address',
            'email.unique'        => 'This email is already registered',
            'password.required'   => 'Please enter a password',
            'password.min'        => 'Password must be at least 8 characters',
            'password.confirmed'  => 'Passwords do not match',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $admin = Admin::create($validator->validated());
            $token = $admin->createToken(PersonalAccessTokenLabel::make(
                (string) $admin->full_name
            ))->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'admin' => $admin,
                    'token' => $token,
                ],
            ], 201);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Please enter your email address',
            'email.email'       => 'Please enter a valid email address',
            'password.required' => 'Please enter your password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();
            $admin = Admin::where('email', $data['email'])->first();

            if (! $admin || ! Hash::check($data['password'], (string) $admin->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password. Please try again.',
                ], 401);
            }

            $token = $admin->createToken(PersonalAccessTokenLabel::make(
                (string) $admin->full_name
            ))->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'admin' => $admin,
                    'token' => $token,
                ],
            ]);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }


    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }


    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'admin' => $request->user(),
            ],
        ]);
    }
}
