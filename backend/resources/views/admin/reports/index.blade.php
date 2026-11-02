@extends('layouts.app')

@section('title', 'Manajemen Laporan Sampah')
@section('page_title', 'Manajemen & Monitoring Laporan Sampah')

@section('content')
<div class="space-y-6">

    <!-- Filters & Search Bar -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="space-y-4">
            <!-- Search & Dropdown Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="md:col-span-2 relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul laporan, deskripsi, alamat, atau nama pelapor..."
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 transition">
                </div>

                <div>
                    <select name="category" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white text-sm focus:outline-none focus:border-emerald-500 transition">
                        <option value="all" {{ $category === 'all' || !$category ? 'selected' : '' }}>Semua Kategori Sampah</option>
                        <option value="Organic Waste" {{ $category === 'Organic Waste' ? 'selected' : '' }}>Organic Waste (Organik)</option>
                        <option value="Plastic Waste" {{ $category === 'Plastic Waste' ? 'selected' : '' }}>Plastic Waste (Plastik)</option>
                        <option value="Paper Waste" {{ $category === 'Paper Waste' ? 'selected' : '' }}>Paper Waste (Kertas)</option>
                        <option value="Unclassified" {{ $category === 'Unclassified' ? 'selected' : '' }}>Unclassified</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-filter text-xs"></i>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('admin.reports.index') }}" class="py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm transition flex items-center justify-center">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </div>

            <!-- Status Tabs -->
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-800/80 text-xs">
                <span class="text-slate-400 font-semibold mr-1">Status:</span>
                <a href="{{ route('admin.reports.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
                   class="px-3 py-1.5 rounded-lg font-medium transition {{ $status === 'all' || !$status ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-slate-800 text-slate-400 hover:text-white' }}">
                    Semua ({{ $statusCounts['all'] }})
                </a>
                <a href="{{ route('admin.reports.index', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}"
                   class="px-3 py-1.5 rounded-lg font-medium transition {{ $status === 'pending' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-slate-800 text-slate-400 hover:text-white' }}">
                    Pending ({{ $statusCounts['pending'] }})
                </a>
                <a href="{{ route('admin.reports.index', array_merge(request()->except('status', 'page'), ['status' => 'in_progress'])) }}"
                   class="px-3 py-1.5 rounded-lg font-medium transition {{ $status === 'in_progress' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/40' : 'bg-slate-800 text-slate-400 hover:text-white' }}">
                    In Progress ({{ $statusCounts['in_progress'] }})
                </a>
                <a href="{{ route('admin.reports.index', array_merge(request()->except('status', 'page'), ['status' => 'completed'])) }}"
                   class="px-3 py-1.5 rounded-lg font-medium transition {{ $status === 'completed' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-slate-800 text-slate-400 hover:text-white' }}">
                    Completed ({{ $statusCounts['completed'] }})
                </a>
                <a href="{{ route('admin.reports.index', array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}"
                   class="px-3 py-1.5 rounded-lg font-medium transition {{ $status === 'rejected' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : 'bg-slate-800 text-slate-400 hover:text-white' }}">
                    Rejected ({{ $statusCounts['rejected'] }})
                </a>
            </div>
        </form>
    </div>

    <!-- Reports Data Table -->
    <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-4 px-4">Foto</th>
                        <th class="py-4 px-4">Judul & Lokasi</th>
                        <th class="py-4 px-4">Kategori Sampah (AI)</th>
                        <th class="py-4 px-4">Pelapor</th>
                        <th class="py-4 px-4">Status</th>
                        <th class="py-4 px-4">Tanggal Masuk</th>
                        <th class="py-4 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($reports as $report)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 px-4">
                            <img src="{{ $report->image_url }}" alt="Thumbnail" class="w-14 h-14 object-cover rounded-xl border border-slate-800 shadow-sm">
                        </td>
                        <td class="py-3 px-4 max-w-xs">
                            <a href="{{ route('admin.reports.show', $report->id) }}" class="font-bold text-white hover:text-emerald-400 text-sm block truncate transition">
                                {{ $report->title }}
                            </a>
                            <div class="text-[11px] text-slate-400 mt-0.5 truncate flex items-center gap-1">
                                <i class="fa-solid fa-location-dot text-slate-500 text-[10px]"></i>
                                <span>{{ $report->address_note ?? ($report->latitude . ', ' . $report->longitude) }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold 
                                {{ $report->waste_category === 'Plastic Waste' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30' : '' }}
                                {{ $report->waste_category === 'Organic Waste' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : '' }}
                                {{ $report->waste_category === 'Paper Waste' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : '' }}
                                {{ $report->waste_category === 'Unclassified' ? 'bg-slate-500/10 text-slate-400 border border-slate-500/30' : '' }}">
                                {{ $report->waste_category }}
                            </span>
                            @if($report->ai_confidence)
                                <span class="text-[10px] text-emerald-400/80 block mt-1 font-medium">
                                    <i class="fa-solid fa-brain text-[9px] mr-0.5"></i> {{ round($report->ai_confidence * 100, 1) }}% Match
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-slate-200">{{ $report->user->name ?? 'Anonim' }}</div>
                            <div class="text-[10px] text-slate-500">{{ $report->user->phone ?? $report->user->email ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            @if($report->status === 'pending')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30 inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Pending
                                </span>
                            @elseif($report->status === 'in_progress')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/30 inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> In Progress
                                </span>
                            @elseif($report->status === 'completed')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Completed
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30 inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Rejected
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-400 text-[11px]">
                            <div>{{ $report->created_at->format('d M Y') }}</div>
                            <div class="text-[10px] text-slate-500">{{ $report->created_at->format('H:i') }} WIB</div>
                        </td>
                        <td class="py-3 px-4 text-right space-x-2">
                            <a href="{{ route('admin.reports.show', $report->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 font-semibold text-xs border border-emerald-500/30 transition">
                                <span>Detail</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500">
                            <i class="fa-regular fa-folder-open text-3xl mb-2 block"></i>
                            Tidak ada laporan yang sesuai dengan kriteria filter atau pencarian Anda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            {{ $reports->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
