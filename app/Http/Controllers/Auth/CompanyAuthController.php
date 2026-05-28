<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\ResetsPasswords;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Support\PersonalAccessTokenLabel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CompanyAuthController extends Controller
{
    use ResetsPasswords;

    protected function passwordBroker(): string
    {
        return 'company';
    }


    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:companies,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'hotline' => ['required', 'string', 'max:20'],
            'commercial_register' => ['required', 'string', 'max:100'],
            'company_profile_image' => ['nullable', 'image', 'max:2048'],
            'company_id_image' => ['required', 'image', 'max:2048'],
        ], [
            'company_name.required'         => 'Please enter your company name',
            'email.required'                => 'Please enter your email address',
            'email.email'                   => 'Please enter a valid email address',
            'email.unique'                  => 'This email is already registered',
            'password.required'             => 'Please enter a password',
            'password.min'                  => 'Password must be at least 8 characters',
            'password.confirmed'            => 'Passwords do not match',
            'hotline.required'              => 'Please enter a hotline number',
            'commercial_register.required'  => 'Please enter your commercial register number',
            'company_id_image.required'     => 'Please upload your company ID image',
            'company_id_image.image'        => 'Company ID file must be an image',
            'company_id_image.max'          => 'Company ID image must not exceed 2MB',
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

            if ($request->hasFile('company_profile_image')) {
                $data['company_profile_image'] = $request->file('company_profile_image')->store('companies', 'public');
            }

            if ($request->hasFile('company_id_image')) {
                $data['company_id_image'] = $request->file('company_id_image')->store('companies', 'public');
            }

            $data['status'] = 'pending';

            $company = Company::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Account created, awaiting admin approval',
                'data' => [
                    'company' => $company,
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
            $company = Company::where('email', $data['email'])->first();

            if (! $company || ! Hash::check($data['password'], (string) $company->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password. Please try again.',
                ], 401);
            }

            if ($company->status === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account awaiting admin approval',
                ], 403);
            }

            if ($company->status === 'blocked') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account has been blocked',
                ], 403);
            }

            if ($company->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Company account is not approved',
                ], 403);
            }

            $token = $company->createToken(PersonalAccessTokenLabel::make(
                (string) $company->company_name
            ))->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'company' => $company,
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
                'company' => $request->user(),
            ],
        ]);
    }
}
