<?php

namespace App\Http\Controllers\Company;

use App\Models\ActiveIngredient;
use App\Models\Drug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DrugController extends BaseCompanyController
{

    public function index(): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $drugs = Drug::where('company_id', $company->id)
            ->with(['category', 'ingredients'])
            ->get();

        return $this->success(['drugs' => $drugs]);
    }


    public function store(Request $request): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $validated = $this->validateRequest($request, [
            'market_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:drug_categories,id'],
            'image' => ['nullable', 'image', 'max:2048'],
            'status' => ['nullable', 'in:active,inactive'],
            'ingredient_ids' => ['nullable', 'array'],
            'ingredient_ids.*' => ['integer', 'exists:active_ingredients,id'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('drugs', 'public');
        }

        $marketName = $validated['market_name'];
        $payload = [
            'company_id' => $company->id,
            'market_name' => $marketName,
            'description' => $validated['description'] ?? null,
            'category_id' => $validated['category_id'],
            'image' => $imagePath,
        ];
        if (Schema::hasColumn('drugs', 'name')) {
            $payload['name'] = $marketName;
        }
        if (Schema::hasColumn('drugs', 'status')) {
            $payload['status'] = $validated['status'] ?? 'active';
        }
        $drug = Drug::create($payload);

        $ingredientIds = $validated['ingredient_ids'] ?? [];
        $ownedIngredientIds = ActiveIngredient::whereIn('id', $ingredientIds)
            ->pluck('id')
            ->all();
        $drug->ingredients()->sync($ownedIngredientIds);

        return $this->success(['drug' => $drug->load(['category', 'ingredients'])], null, 201);
    }


    public function show(int $id): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $drug = Drug::where('company_id', $company->id)
            ->with(['category', 'ingredients'])
            ->find($id);
        if (!$drug) {
            return $this->error('Drug not found', 404);
        }

        return $this->success(['drug' => $drug]);
    }


    public function update(Request $request, int $id): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $drug = Drug::where('company_id', $company->id)->find($id);
        if (!$drug) {
            return $this->error('Drug not found', 404);
        }

        $validated = $this->validateRequest($request, [
            'market_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:drug_categories,id'],
            'image' => ['nullable', 'image', 'max:2048'],
            'status' => ['nullable', 'in:active,inactive'],
            'ingredient_ids' => ['nullable', 'array'],
            'ingredient_ids.*' => ['integer', 'exists:active_ingredients,id'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $marketName = $validated['market_name'];
        $data = [
            'market_name' => $marketName,
            'description' => $validated['description'] ?? null,
            'category_id' => $validated['category_id'],
        ];
        if (Schema::hasColumn('drugs', 'name')) {
            $data['name'] = $marketName;
        }
        if (Schema::hasColumn('drugs', 'status')) {
            $data['status'] = $validated['status'] ?? $drug->status ?? 'active';
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('drugs', 'public');
        }

        $drug->update($data);

        if (array_key_exists('ingredient_ids', $validated)) {
            $ownedIngredientIds = ActiveIngredient::whereIn('id', $validated['ingredient_ids'] ?? [])
                ->pluck('id')
                ->all();
            $drug->ingredients()->sync($ownedIngredientIds);
        }

        return $this->success(['drug' => $drug->fresh()->load(['category', 'ingredients'])]);
    }

    public function destroy(int $id): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $drug = Drug::where('company_id', $company->id)->find($id);
        if (!$drug) {
            return $this->error('Drug not found', 404);
        }

        $drug->delete();
        return $this->success([], 'Drug deleted');
    }
}
