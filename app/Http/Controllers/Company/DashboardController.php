<?php

namespace App\Http\Controllers\Company;

use App\Models\Drug;
use App\Models\DrugSample;
use App\Models\Event;
use App\Models\EventRequest;
use App\Models\MedicalRep;
use App\Models\Meeting;
use App\Models\RepTarget;
use App\Models\Reward;
use App\Models\RewardRedemption;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseCompanyController
{
    public function index(): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $companyId = $company->id;
        $now = Carbon::now();

        $drugsTotal = Drug::where('company_id', $companyId)->count();

        $drugsByCategory = DB::table('drugs')
            ->where('drugs.company_id', $companyId)
            ->leftJoin('drug_categories', 'drugs.category_id', '=', 'drug_categories.id')
            ->select(
                'drugs.category_id',
                DB::raw('COALESCE(MAX(drug_categories.name), "Uncategorized") as category_name'),
                DB::raw('count(*) as total')
            )
            ->groupBy('drugs.category_id')
            ->get()
            ->map(fn ($row) => [
                'category_id' => $row->category_id,
                'category_name' => $row->category_name,
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();

        $repsTotal = MedicalRep::where('company_id', $companyId)->count();
        $repsApproved = MedicalRep::where('company_id', $companyId)->whereIn('status', ['approved', 'active'])->count();
        $repsPending = MedicalRep::where('company_id', $companyId)->where('status', 'pending')->count();

        $eventsTotal = Event::where('company_id', $companyId)->count();
        $eventsUpcoming = Event::where('company_id', $companyId)
            ->where(function ($q) use ($now) {
                $q->where('status', 'upcoming')
                    ->orWhere('event_date', '>=', $now);
            })
            ->count();
        $eventsCompleted = Event::where('company_id', $companyId)
            ->where(function ($q) use ($now) {
                $q->where('status', 'completed')
                    ->orWhere('event_date', '<', $now);
            })
            ->count();

        $totalAttendees = EventRequest::query()
            ->where('status', 'approved')
            ->whereHas('event', fn ($q) => $q->where('company_id', $companyId))
            ->count();

        $samplesTotal = DrugSample::query()
            ->whereHas('drug', fn ($q) => $q->where('company_id', $companyId))
            ->count();
        $samplesPending = DrugSample::query()
            ->whereHas('drug', fn ($q) => $q->where('company_id', $companyId))
            ->where('status', 'pending')
            ->count();
        $samplesDelivered = DrugSample::query()
            ->whereHas('drug', fn ($q) => $q->where('company_id', $companyId))
            ->where('status', 'delivered')
            ->count();

        $rewardsTotal = Reward::where('company_id', $companyId)->count();
        $pendingRedemptions = RewardRedemption::query()
            ->where('status', 'pending')
            ->whereHas('reward', fn ($q) => $q->where('company_id', $companyId))
            ->count();

        $repIds = MedicalRep::where('company_id', $companyId)->pluck('id');

        $meetingsCompletedByRep = Meeting::query()
            ->whereIn('rep_id', $repIds)
            ->where('status', 'completed')
            ->select('rep_id', DB::raw('count(*) as c'))
            ->groupBy('rep_id')
            ->pluck('c', 'rep_id');

        $samplesHandledByRep = DrugSample::query()
            ->whereIn('rep_id', $repIds)
            ->whereIn('status', ['approved', 'rejected', 'delivered'])
            ->select('rep_id', DB::raw('count(*) as c'))
            ->groupBy('rep_id')
            ->pluck('c', 'rep_id');

        $repPerformance = MedicalRep::query()
            ->where('company_id', $companyId)
            ->get()
            ->map(function (MedicalRep $rep) use ($meetingsCompletedByRep, $samplesHandledByRep) {
                $targets = RepTarget::where('rep_id', $rep->id)->get()->map(function (RepTarget $target) {
                    $pct = $target->target_value > 0
                        ? round(($target->current_value / $target->target_value) * 100, 1)
                        : 0.0;

                    return [
                        'target_type' => $target->target_type,
                        'target_value' => $target->target_value,
                        'current_value' => $target->current_value,
                        'percentage' => $pct,
                        'period_start' => $target->period_start,
                        'period_end' => $target->period_end,
                    ];
                })->values()->all();

                return [
                    'rep_id' => $rep->id,
                    'rep_name' => $rep->full_name,
                    'targets' => $targets,
                    'meetings_completed' => (int) ($meetingsCompletedByRep[$rep->id] ?? 0),
                    'samples_handled' => (int) ($samplesHandledByRep[$rep->id] ?? 0),
                ];
            })
            ->values()
            ->all();

        $topDrugs = Drug::query()
            ->where('company_id', $companyId)
            ->withCount('drugSamples')
            ->orderByDesc('drug_samples_count')
            ->limit(5)
            ->get()
            ->map(fn (Drug $d) => [
                'drug_id' => $d->id,
                'name' => $d->name ?? $d->market_name,
                'sample_requests' => $d->drug_samples_count,
            ])
            ->all();

        return $this->success([
            'drugs' => [
                'total' => $drugsTotal,
                'by_category' => $drugsByCategory,
            ],
            'reps' => [
                'total' => $repsTotal,
                'approved' => $repsApproved,
                'pending' => $repsPending,
            ],
            'events' => [
                'total' => $eventsTotal,
                'upcoming' => $eventsUpcoming,
                'completed' => $eventsCompleted,
                'total_attendees' => $totalAttendees,
            ],
            'samples' => [
                'total_requests' => $samplesTotal,
                'pending' => $samplesPending,
                'delivered' => $samplesDelivered,
            ],
            'rewards' => [
                'total' => $rewardsTotal,
                'pending_redemptions' => $pendingRedemptions,
            ],
            'rep_performance' => $repPerformance,
            'top_drugs' => $topDrugs,
        ]);
    }
}
