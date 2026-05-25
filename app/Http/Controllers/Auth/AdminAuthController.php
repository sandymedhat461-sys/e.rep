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
            'phone' => ['required', 'string', 'max:11'],
            'email' => ['required', 'email', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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
                (string) $admin->full_name,
                PersonalAccessTokenLabel::ROLE_ADMIN
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
                    'message' => 'Invalid credentials',
                ], 401);
            }

            $token = $admin->createToken(PersonalAccessTokenLabel::make(
                (string) $admin->full_name,
                PersonalAccessTokenLabel::ROLE_ADMIN
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
