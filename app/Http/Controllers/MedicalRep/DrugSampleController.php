<?php

namespace App\Http\Controllers\MedicalRep;

use App\Models\DrugSample;
use Illuminate\Http\JsonResponse;

class DrugSampleController extends BaseMedicalRepController
{
    
    public function index(): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $samples = DrugSample::where('rep_id', $rep->id)
            ->with(['doctor:id,full_name,email', 'drug:id,name,market_name'])
            ->latest()
            ->get();
        return $this->success(['samples' => $samples]);
    }

    
    public function show(int $id): JsonResponse
    {
        $sample = $this->ownedSample($id);
        if ($sample instanceof JsonResponse) {
            return $sample;
        }

        return $this->success(['sample' => $sample->load(['doctor:id,full_name,email', 'drug:id,name,market_name'])]);
    }

    
    public function approve(int $id): JsonResponse
    {
        $sample = $this->ownedSample($id);
        if ($sample instanceof JsonResponse) {
            return $sample;
        }
        $sample->update(['status' => 'approved']);
        return $this->success(['sample' => $sample->fresh()]);
    }

   
    public function reject(int $id): JsonResponse
    {
        $sample = $this->ownedSample($id);
        if ($sample instanceof JsonResponse) {
            return $sample;
        }
        $sample->update(['status' => 'rejected']);
        return $this->success(['sample' => $sample->fresh()]);
    }

   
    public function deliver(int $id): JsonResponse
    {
        $sample = $this->ownedSample($id);
        if ($sample instanceof JsonResponse) {
            return $sample;
        }
        if ($sample->status !== 'approved') {
            return $this->error('Only approved samples can be delivered', 422);
        }
        $sample->update(['status' => 'delivered']);
        return $this->success(['sample' => $sample->fresh()]);
    }

    private function ownedSample(int $id): DrugSample|JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $sample = DrugSample::where('rep_id', $rep->id)->find($id);
        if (!$sample) {
            return $this->error('Sample not found', 404);
        }
        return $sample;
    }
}
