<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    /**
     * Get summary statistics and recent reports for mobile dashboard.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $isCitizen = ($user->role !== 'admin');

        $query = Report::query();
        if ($isCitizen) {
            $query->where('user_id', $user->id);
        }

        $totalReports = (clone $query)->count();
        $pendingReports = (clone $query)->where('status', 'pending')->count();
        $inProgressReports = (clone $query)->where('status', 'in_progress')->count();
        $completedReports = (clone $query)->where('status', 'completed')->count();
        $rejectedReports = (clone $query)->where('status', 'rejected')->count();

        $recentReports = (clone $query)
            ->with('user:id,name,email')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($report) {
                $report->image_url = $report->image_path ? asset('storage/' . $report->image_path) : null;
                return $report;
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_reports' => $totalReports,
                'pending_reports' => $pendingReports,
                'in_progress_reports' => $inProgressReports,
                'completed_reports' => $completedReports,
                'rejected_reports' => $rejectedReports,
                'recent_reports' => $recentReports,
            ],
        ], 200);
    }
}
