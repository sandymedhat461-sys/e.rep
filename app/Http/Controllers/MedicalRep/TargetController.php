<?php

namespace App\Http\Controllers\MedicalRep;

use App\Models\RepTarget;
use Illuminate\Http\JsonResponse;

class TargetController extends BaseMedicalRepController
{
    
    public function index(): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $targets = RepTarget::where('rep_id', $rep->id)->get()->map(function (RepTarget $target) {
            $percentage = $target->target_value > 0
                ? round(($target->current_value / $target->target_value) * 100, 1)
                : 0.0;

            $start = $target->period_start;
            $end = $target->period_end;
            if (!$start && !$end && $target->period) {
                [$pStart, $pEnd] = array_pad(explode(' - ', (string) $target->period), 2, null);
                $start = $start ?? $pStart;
                $end = $end ?? $pEnd;
            }

            return [
                'target_type' => $target->target_type,
                'target_value' => $target->target_value,
                'current_value' => $target->current_value,
                'percentage' => $percentage,
                'period_start' => $start,
                'period_end' => $end,
            ];
        });

        return $this->success(['targets' => $targets]);
    }
}
