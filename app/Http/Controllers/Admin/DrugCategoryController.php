<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Drug;
use App\Models\DrugCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DrugCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        if (!auth('admin-api')->user()) {
            return $this->error('Unauthenticated', 401);
        }

        $categories = DrugCategory::query()->orderBy('name')->get();

        return $this->success(['categories' => $categories]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!auth('admin-api')->user()) {
            return $this->error('Unauthenticated', 401);
        }

        $validated = $this->validateRequest($request, [
            'name' => ['required', 'string', 'max:255'],
            'line_manager_name' => ['nullable', 'string', 'max:255'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $category = DrugCategory::create($validated);

        return $this->success(['category' => $category], null, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if (!auth('admin-api')->user()) {
            return $this->error('Unauthenticated', 401);
        }

        $category = DrugCategory::find($id);
        if (!$category) {
            return $this->error('Category not found', 404);
        }

        $validated = $this->validateRequest($request, [
            'name' => ['required', 'string', 'max:255'],
            'line_manager_name' => ['nullable', 'string', 'max:255'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $category->update($validated);

        return $this->success(['category' => $category->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        if (!auth('admin-api')->user()) {
            return $this->error('Unauthenticated', 401);
        }

        $category = DrugCategory::find($id);
        if (!$category) {
            return $this->error('Category not found', 404);
        }

        if (Drug::where('category_id', $category->id)->exists()) {
            return $this->error('Category has assigned drugs', 422);
        }

        $category->delete();

        return $this->success([], 'Category deleted');
    }
}
