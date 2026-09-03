<?php

namespace App\Http\Controllers\Api\V1\Operator;

use App\Enums\ExpedientStatus;
use App\Enums\LoanStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ExpedientMovementResource;
use App\Models\Expedient;
use App\Models\ExpedientMovement;
use App\Models\LoanRequest;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $toExtractCount = LoanRequest::where('status', LoanStatus::Approved)->count();
        $pendingApprovalCount = LoanRequest::where('status', LoanStatus::Pending)->count();
        $toRearchiveCount = Expedient::where('current_status', ExpedientStatus::Returned)->count();
        $activeLoansCount = Expedient::where('current_status', ExpedientStatus::Loaned)->count();
        $overdueCount = Expedient::overdue()->count();

        $recentMovements = ExpedientMovement::with([
            'expedient.employee',
            'fromLocation.branch',
            'toLocation.branch',
            'user',
        ])
            ->latest('created_at')
            ->take(10)
            ->get();

        return response()->json([
            'stats' => [
                'to_extract' => $toExtractCount,
                'pending_approval' => $pendingApprovalCount,
                'to_rearchive' => $toRearchiveCount,
                'active_loans' => $activeLoansCount,
                'overdue_loans' => $overdueCount,
            ],
            'recent_activity' => ExpedientMovementResource::collection($recentMovements),
        ]);
    }
}
