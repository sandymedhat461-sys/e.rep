<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Doctor;
use App\Models\MedicalRep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class UserManagementController extends Controller
{
    
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'companies' => Company::where('status', 'pending')->get(),
                'doctors' => Doctor::where('status', 'pending')->get(),
                'reps' => MedicalRep::where('status', 'pending')->get(),
            ],
        ]);
    }


    public function approve(string $type, int $id): JsonResponse
    {
        try {
            $user = $this->resolveUser($type, $id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            if ($user instanceof Company) {
                $user->status = 'approved';
            } else {
                $user->status = 'active';
            }
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Account approved successfully',
            ]);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }


    public function block(string $type, int $id): JsonResponse
    {
        try {
            $user = $this->resolveUser($type, $id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $user->status = 'blocked';
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Account blocked successfully',
            ]);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function deleteUser(string $type, int $id): JsonResponse
    {
        try {
            $user = $this->resolveUser($type, $id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $user->delete();

            return $this->success([], 'User deleted');
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function listDoctors(): JsonResponse
    {
        $doctors = Doctor::whereIn('status', ['active', 'pending', 'blocked'])->get();

        return $this->success([
            'doctors' => $doctors,
        ]);
    }

    public function listReps(): JsonResponse
    {
        $reps = MedicalRep::all();

        return $this->success([
            'reps' => $reps,
        ]);
    }

    public function listCompanies(): JsonResponse
    {
        $companies = Company::all();

        return $this->success([
            'companies' => $companies,
        ]);
    }

    public function createCompany(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request, [
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:companies,email'],
            'password' => ['required', 'string', 'min:6'],
            'hotline' => ['required', 'string'],
            'commercial_register' => ['required', 'string', 'unique:companies,commercial_register'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $company = Company::create([
            'company_name' => $validated['company_name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'hotline' => $validated['hotline'],
            'commercial_register' => $validated['commercial_register'],
            'status' => 'approved',
        ]);

        return $this->success([
            'company' => [
                'id' => $company->id,
                'company_name' => $company->company_name,
                'email' => $company->email,
                'status' => $company->status,
            ],
        ], null, 201);
    }

    public function createRep(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request, [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:medical_reps,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['required', 'string'],
            'national_id' => ['required', 'string', 'unique:medical_reps,national_id'],
            'company_id' => ['required', 'exists:companies,id'],
            'category_id' => ['required', 'exists:drug_categories,id'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $rep = MedicalRep::create([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'phone' => $validated['phone'],
            'national_id' => $validated['national_id'],
            'company_id' => $validated['company_id'],
            'category_id' => $validated['category_id'],
            'status' => 'active',
        ]);

        return $this->success([
            'rep' => [
                'id' => $rep->id,
                'full_name' => $rep->full_name,
                'email' => $rep->email,
                'company_id' => $rep->company_id,
                'status' => $rep->status,
            ],
        ], null, 201);
    }

    private function resolveUser(string $type, int $id): Company|Doctor|MedicalRep|null
    {
        return match ($type) {
            'company' => Company::find($id),
            'doctor' => Doctor::find($id),
            'rep' => MedicalRep::find($id),
            default => null,
        };
    }
}

