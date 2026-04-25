<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorPoint;
use App\Models\Reward;
use App\Models\RewardRedemption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RewardRedemptionController extends Controller
{
   
    public function index(Request $request): JsonResponse
    {
        $redemptions = RewardRedemption::query()
            ->where('doctor_id', $request->user()->id)
            ->with('reward:id,company_id,title,points_required')
            ->latest()
            ->get();

        return $this->success(['redemptions' => $redemptions]);
    }

 
    public function store(Request $request, ?int $rewardId = null): JsonResponse
    {
        if ($rewardId === null) {
            $validated = $this->validateRequest($request, [
                'reward_id' => ['required', 'exists:rewards,id'],
            ]);
            if ($validated instanceof JsonResponse) {
                return $validated;
            }
            $rewardId = (int) $validated['reward_id'];
        }

        $reward = Reward::find($rewardId);
        if (!$reward) {
            return $this->error('Reward not found', 404);
        }

        $doctorId = (int) $request->user()->id;
        $totalPoints = (int) DoctorPoint::where('doctor_id', $doctorId)->sum('value');

        if ($totalPoints < (int) $reward->points_required) {
            return $this->error('Not enough points', 422);
        }

        $redemption = RewardRedemption::create([
            'doctor_id' => $doctorId,
            'reward_id' => $reward->id,
            'points_spent' => (int) $reward->points_required,
            'status' => 'pending',
        ]);

        return $this->success(['redemption' => $redemption], null, 201);
    }
}
