<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $category = $request->input('category');

        $reports = Report::with('user:id,name,email,phone')
            ->search($search)
            ->filterStatus($status)
            ->filterCategory($category)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $statusCounts = [
            'all' => Report::count(),
            'pending' => Report::where('status', 'pending')->count(),
            'in_progress' => Report::where('status', 'in_progress')->count(),
            'completed' => Report::where('status', 'completed')->count(),
            'rejected' => Report::where('status', 'rejected')->count(),
        ];

        return view('admin.reports.index', compact('reports', 'statusCounts', 'search', 'status', 'category'));
    }

    public function show($id)
    {
        $report = Report::with('user')->findOrFail($id);
        return view('admin.reports.show', compact('report'));
    }

    public function updateStatus(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed,rejected'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $report->status = $validated['status'];
        $report->admin_notes = $validated['admin_notes'] ?? null;

        if ($validated['status'] === 'completed' && !$report->resolved_at) {
            $report->resolved_at = now();
        } elseif ($validated['status'] !== 'completed') {
            $report->resolved_at = null;
        }

        $report->save();

        return redirect()->back()->with('success', 'Status laporan berhasil diperbarui menjadi: ' . ucfirst(str_replace('_', ' ', $report->status)));
    }

    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        if ($report->image_path && Storage::disk('public')->exists($report->image_path)) {
            Storage::disk('public')->delete($report->image_path);
        }
        $report->delete();

        return redirect()->route('admin.reports.index')->with('success', 'Laporan berhasil dihapus dari sistem.');
    }
}
