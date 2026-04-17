<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;

class DoctorAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'national_id' => ['required', 'string', 'max:20', 'unique:doctors,national_id'],
            'email' => ['required', 'email', 'unique:doctors,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'specialization' => ['required', 'string', 'max:100'],
            'hospital_name' => ['required', 'string', 'max:255'],
            'syndicate_id' => ['required', 'string', 'unique:doctors,syndicate_id', 'min:5', 'max:20'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'syndicate_id_image' => ['required', 'image', 'max:2048'],
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

            if ($request->hasFile('profile_image')) {
                $data['profile_image'] = $request->file('profile_image')->store('doctors', 'public');
            }

            if ($request->hasFile('syndicate_id_image')) {
                $data['syndicate_id_image'] = $request->file('syndicate_id_image')->store('doctors', 'public');
            }

            $data['status'] = 'pending';

            $doctor = Doctor::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Account created, awaiting admin approval',
                'data' => [
                    'doctor' => $doctor,
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
            $doctor = Doctor::where('email', $data['email'])->first();

            if (!$doctor || !Hash::check($data['password'], (string) $doctor->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            if ($doctor->status === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account awaiting admin approval',
                ], 403);
            }

            if ($doctor->status === 'blocked') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account has been blocked',
                ], 403);
            }

            $token = $doctor->createToken('doctor-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'doctor' => $doctor,
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
                'doctor' => $request->user(),
            ],
        ]);
    }

    public function checkSyndicateId(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'syndicate_id' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $exists = Doctor::where('syndicate_id', $request->syndicate_id)->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'available' => !$exists,
            ],
        ]);
    }
}

