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

        if (!in_array((string) $rep->status, ['approved', 'active'], true)) {
            return $this->error('Rep account is not approved', 403);
        }

        return $rep;
    }
}
