<?php

namespace App\Http\Controllers\Company;

use App\Models\ActiveIngredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActiveIngredientController extends BaseCompanyController
{
   
    public function index(): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $ingredients = ActiveIngredient::orderBy('name')->get();
        return $this->success(['ingredients' => $ingredients]);
    }


    public function store(Request $request): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $validated = $this->validateRequest($request, [
            'name' => ['required', 'string', 'max:255', 'unique:active_ingredients,name'],
            'description' => ['nullable', 'string'],
            'side_effect' => ['nullable', 'string'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $ingredient = ActiveIngredient::create([
            ...$validated,
            'created_by_company_id' => $company->id,
        ]);

        return $this->success(['ingredient' => $ingredient], null, 201);
    }

 
    public function update(Request $request, int $id): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $ingredient = ActiveIngredient::find($id);
        if (!$ingredient) {
            return $this->error('Ingredient not found', 404);
        }

        $validated = $this->validateRequest($request, [
            'name' => ['required', 'string', 'max:255', 'unique:active_ingredients,name,' . $id],
            'description' => ['nullable', 'string'],
            'side_effect' => ['nullable', 'string'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $ingredient->update($validated);
        return $this->success(['ingredient' => $ingredient->fresh()]);
    }

   
    public function destroy(int $id): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $ingredient = ActiveIngredient::find($id);
        if (!$ingredient) {
            return $this->error('Ingredient not found', 404);
        }

        $ingredient->delete();
        return $this->success([], 'Ingredient deleted');
    }
}
