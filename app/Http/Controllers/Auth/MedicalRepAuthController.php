<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MedicalRep;
use App\Support\PersonalAccessTokenLabel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;

class MedicalRepAuthController extends Controller
{
   
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:11'],
            'national_id' => ['required', 'string', 'max:14', 'unique:medical_reps,national_id'],
            'email' => ['required', 'email', 'unique:medical_reps,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'company_id' => ['required', 'exists:companies,id'],
            'company_name' => ['required', 'string', 'max:255'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'company_id_image' => ['required', 'image', 'max:2048'],
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
                $data['profile_image'] = $request->file('profile_image')->store('reps', 'public');
            }

            if ($request->hasFile('company_id_image')) {
                $data['company_id_image'] = $request->file('company_id_image')->store('reps', 'public');
            }

            $data['status'] = 'pending';

            $rep = MedicalRep::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Account created, awaiting admin approval',
                'data' => [
                    'rep' => $rep,
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
            $rep = MedicalRep::where('email', $data['email'])->first();

            if (! $rep || ! Hash::check($data['password'], (string) $rep->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            if ($rep->status === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account awaiting admin approval',
                ], 403);
            }

            if ($rep->status === 'blocked') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account has been blocked',
                ], 403);
            }

            $token = $rep->createToken(PersonalAccessTokenLabel::make(
                (string) $rep->full_name,
                PersonalAccessTokenLabel::ROLE_MEDICAL_REP
            ))->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'rep' => $rep,
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
                'rep' => $request->user(),
            ],
        ]);
    }
}
