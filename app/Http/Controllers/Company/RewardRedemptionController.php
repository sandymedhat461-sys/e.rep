<?php

namespace App\Http\Controllers\Company;

use App\Models\RewardRedemption;
use Illuminate\Http\JsonResponse;

class RewardRedemptionController extends BaseCompanyController
{
   
    public function index(): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $redemptions = RewardRedemption::query()
            ->whereHas('reward', fn ($q) => $q->where('company_id', $company->id))
            ->with(['reward', 'doctor:id,full_name,email'])
            ->latest()
            ->get();

        return $this->success(['redemptions' => $redemptions]);
    }

    
    public function fulfill(int $id): JsonResponse
    {
        $redemption = $this->ownedRedemption($id);
        if ($redemption instanceof JsonResponse) {
            return $redemption;
        }

        $redemption->update(['status' => 'fulfilled']);
        return $this->success(['redemption' => $redemption->fresh()]);
    }

    
    public function cancel(int $id): JsonResponse
    {
        $redemption = $this->ownedRedemption($id);
        if ($redemption instanceof JsonResponse) {
            return $redemption;
        }

        $redemption->update(['status' => 'cancelled']);
        return $this->success(['redemption' => $redemption->fresh()]);
    }

    private function ownedRedemption(int $id): RewardRedemption|JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $redemption = RewardRedemption::query()
            ->where('id', $id)
            ->whereHas('reward', fn ($q) => $q->where('company_id', $company->id))
            ->first();

        if (!$redemption) {
            return $this->error('Redemption not found', 404);
        }

        return $redemption;
    }
}
