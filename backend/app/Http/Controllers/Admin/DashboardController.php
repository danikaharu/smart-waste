<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Metric Counters
        $stats = [
            'total_reports' => Report::count(),
            'pending' => Report::where('status', 'pending')->count(),
            'in_progress' => Report::where('status', 'in_progress')->count(),
            'completed' => Report::where('status', 'completed')->count(),
            'rejected' => Report::where('status', 'rejected')->count(),
            'total_users' => User::where('role', 'user')->count(),
        ];

        // 2. Waste Category Distribution
        $categoryCounts = [
            'Organic Waste' => Report::where('waste_category', 'Organic Waste')->count(),
            'Plastic Waste' => Report::where('waste_category', 'Plastic Waste')->count(),
            'Paper Waste' => Report::where('waste_category', 'Paper Waste')->count(),
            'Unclassified' => Report::where('waste_category', 'Unclassified')->count(),
        ];

        // 3. Monthly Trend (last 6 months)
        $monthlyTrends = Report::select(
            DB::raw("DATE_FORMAT(created_at, '%b %Y') as month"),
            DB::raw("COUNT(*) as count")
        )
        ->groupBy('month')
        ->orderBy(DB::raw("MIN(created_at)"), 'ASC')
        ->limit(6)
        ->get();

        // 4. Geolocated reports for Leaflet GIS Map
        $geolocatedReports = Report::with('user:id,name')
            ->select('id', 'user_id', 'title', 'waste_category', 'ai_confidence', 'status', 'latitude', 'longitude', 'address_note', 'image_path', 'created_at')
            ->latest()
            ->limit(100)
            ->get();

        // 5. Recent Reports for quick summary table
        $recentReports = Report::with('user:id,name')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'categoryCounts',
            'monthlyTrends',
            'geolocatedReports',
            'recentReports'
        ));
    }
}
