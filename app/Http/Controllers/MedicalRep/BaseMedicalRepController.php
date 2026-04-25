<?php

namespace App\Http\Controllers\MedicalRep;

use App\Http\Controllers\Controller;
use App\Models\MedicalRep;
use Illuminate\Http\JsonResponse;

abstract class BaseMedicalRepController extends Controller
{
    protected function repOrForbidden(): MedicalRep|JsonResponse
    {
        $rep = auth('rep-api')->user();
        if (!$rep) {
            return $this->error('Unauthenticated', 401);
        }

        if ((string) $rep->status !== 'active') {
            return $this->error('Rep account is not active', 403);
        }

        return $rep;
    }
}
