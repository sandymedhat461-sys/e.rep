<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\ResetsPasswords;
use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Support\PersonalAccessTokenLabel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;

class DoctorAuthController extends Controller
{
    use ResetsPasswords;

    protected function passwordBroker(): string
    {
        return 'doctor';
    }


    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'digits:11'],
            'national_id' => ['required', 'string', 'digits:14', 'unique:doctors,national_id'],
            'email' => ['required', 'email', 'unique:doctors,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'specialization' => ['required', 'string', 'max:100'],
            'hospital_name' => ['required', 'string', 'max:255'],
            'syndicate_id' => ['required', 'string', 'unique:doctors,syndicate_id', 'min:5', 'max:20'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'syndicate_id_image' => ['required', 'image', 'max:2048'],
        ], [
            'full_name.required'         => 'Please enter your full name',
            'phone.required'             => 'Please enter your phone number',
            'phone.digits'               => 'Phone number must be exactly 11 digits',
            'national_id.required'       => 'Please enter your national ID',
            'national_id.digits'         => 'National ID must be exactly 14 digits',
            'national_id.unique'         => 'This national ID is already registered',
            'email.required'             => 'Please enter your email address',
            'email.email'                => 'Please enter a valid email address',
            'email.unique'               => 'This email is already registered',
            'password.required'          => 'Please enter a password',
            'password.min'               => 'Password must be at least 8 characters',
            'password.confirmed'         => 'Passwords do not match',
            'specialization.required'    => 'Please enter your specialization',
            'hospital_name.required'     => 'Please enter your hospital name',
            'syndicate_id.required'      => 'Please enter your syndicate ID',
            'syndicate_id.unique'        => 'This syndicate ID is already registered',
            'syndicate_id.min'           => 'Syndicate ID must be at least 5 characters',
            'syndicate_id.max'           => 'Syndicate ID must not exceed 20 characters',
            'syndicate_id_image.required' => 'Please upload your syndicate ID image',
            'syndicate_id_image.image'   => 'Syndicate ID file must be an image',
            'syndicate_id_image.max'     => 'Syndicate ID image must not exceed 2MB',
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
            $doctor = Doctor::where('email', $data['email'])->first();

            if (! $doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with this email.',
                ], 401);
            }

            if (! Hash::check($data['password'], (string) $doctor->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect password. Please try again.',
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
            if ($doctor->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor account is not approved',
                ], 403);
            }


            $token = $doctor->createToken(PersonalAccessTokenLabel::make(
                (string) $doctor->full_name
            ))->plainTextToken;

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
        ], [
            'syndicate_id.required' => 'Please enter your syndicate ID',
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
                'available' => ! $exists,
            ],
        ]);
    }
}
