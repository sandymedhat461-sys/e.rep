<?php

namespace App\Http\Controllers\MedicalRep;

use App\Models\Drug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DrugController extends BaseMedicalRepController
{
    public function index(Request $request): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $drugs = Drug::with(['category', 'ingredients'])
            ->whereHas('repAssignments', function ($q) use ($rep) {
                $q->where('rep_id', $rep->id);
            })
            ->where('status', 'active')
            ->get();

        return $this->success(['drugs' => $drugs]);
    }

    public function show(int $id): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $drug = Drug::where('category_id', $rep->category_id)
            ->with(['company:id,company_name', 'category:id,name', 'activeIngredients'])
            ->find($id);
        if (!$drug) {
            return $this->error('Drug not found', 404);
        }

        return $this->success(['drug' => $drug]);
    }
}
