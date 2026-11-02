<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\WasteAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportApiController extends Controller
{
    /**
     * List reports with optional status and category filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Report::with('user:id,name,email,phone')
            ->latest();

        // If not admin, restrict to own reports unless requested for public feed
        if ($request->user()->role !== 'admin' && ! $request->boolean('public_feed', false)) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('waste_category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('address_note', 'like', "%{$search}%");
            });
        }

        $reports = $query->paginate($request->integer('per_page', 20));

        // Append public URL for each report's photo
        $reports->getCollection()->transform(function ($report) {
            $report->image_url = $report->image_path ? asset('storage/' . $report->image_path) : null;
            return $report;
        });

        return response()->json([
            'status' => 'success',
            'data' => $reports->items(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
            ],
        ], 200);
    }

    /**
     * Submit a new waste report with photo, GPS geolocation, and automatic AI classification.
     */
    public function store(Request $request, WasteAiService $aiService): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['required', 'image', 'max:10240'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address_note' => ['nullable', 'string', 'max:500'],
            'waste_category' => ['nullable', 'string'],
        ]);

        $imageFile = $request->file('image');
        $storedPath = $imageFile->store('reports', 'public');
        $fullPath = Storage::disk('public')->path($storedPath);

        // Run AI classification via FastAPI service
        $aiResult = $aiService->detectWaste($fullPath, $imageFile->getClientOriginalName());

        $category = $validated['waste_category'] ?? null;
        if (! $category || $category === 'Auto' || $category === 'Unclassified') {
            $category = $aiResult['label'] ?? 'Unclassified';
        }

        $report = Report::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'image_path' => $storedPath,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'address_note' => $validated['address_note'] ?? null,
            'waste_category' => $category,
            'ai_confidence' => $aiResult['confidence'] ?? null,
            'status' => 'pending',
        ]);

        $report->load('user:id,name,email,phone');
        $report->image_url = asset('storage/' . $report->image_path);

        return response()->json([
            'status' => 'success',
            'message' => 'Laporan sampah berhasil dikirim',
            'data' => $report,
        ], 201);
    }

    /**
     * Get specific report details.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $report = Report::with('user:id,name,email,phone')->findOrFail($id);

        if ($request->user()->role !== 'admin' && $report->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses ke laporan ini.',
            ], 403);
        }

        $report->image_url = $report->image_path ? asset('storage/' . $report->image_path) : null;

        return response()->json([
            'status' => 'success',
            'data' => $report,
        ], 200);
    }
}
