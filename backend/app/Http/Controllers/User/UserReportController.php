<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\WasteAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserReportController extends Controller
{
    protected WasteAiService $aiService;

    public function __construct(WasteAiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        $user = Auth::user();
        $reports = Report::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => Report::where('user_id', $user->id)->count(),
            'pending' => Report::where('user_id', $user->id)->where('status', 'pending')->count(),
            'in_progress' => Report::where('user_id', $user->id)->where('status', 'in_progress')->count(),
            'completed' => Report::where('user_id', $user->id)->where('status', 'completed')->count(),
        ];

        return view('user.dashboard', compact('reports', 'stats'));
    }

    public function create()
    {
        return view('user.reports.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address_note' => ['nullable', 'string', 'max:255'],
        ]);

        $imagePath = $request->file('image')->store('reports', 'public');
        $fullPath = storage_path("app/public/{$imagePath}");

        // Automated AI Waste Detection
        $aiResult = $this->aiService->detectWaste($fullPath, $request->file('image')->getClientOriginalName());

        $report = Report::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'image_path' => $imagePath,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'address_note' => $validated['address_note'] ?? null,
            'waste_category' => $aiResult['label'] ?? 'Unclassified',
            'ai_confidence' => $aiResult['confidence'] > 0 ? $aiResult['confidence'] : null,
            'status' => 'pending',
        ]);

        return redirect()->route('user.dashboard')->with('success', 'Laporan sampah Anda berhasil dikirim! Deteksi AI mengidentifikasi kategori: ' . $report->waste_category);
    }
}
