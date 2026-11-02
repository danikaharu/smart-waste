@extends('layouts.app')

@section('title', 'Portal Warga - Riwayat Laporan')
@section('page_title', 'Portal Warga: Pemantauan Sampah Lingkungan')

@section('content')
<div class="space-y-6">

    <!-- Welcome & Quick Action Card -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-950/60 via-slate-900 to-teal-950/60 border border-emerald-500/30 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <span>Halo, {{ auth()->user()->name }}!</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-semibold border border-emerald-500/30">Warga Aktif</span>
            </h2>
            <p class="text-xs text-slate-300 mt-1 max-w-xl">
                Laporkan tumpukan sampah liar di sekitar Anda. Foto dan sistem AI kami akan otomatis mengklasifikasikan jenis sampah untuk ditindaklanjuti oleh petugas kebersihan.
            </p>
        </div>
        <a href="{{ route('user.reports.create') }}" class="inline-flex items-center gap-2 py-3 px-5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-0.5">
            <i class="fa-solid fa-camera"></i>
            <span>Laporkan Sampah Baru</span>
        </a>
    </div>

    <!-- Citizen Summary Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-xs text-slate-400 block mb-1">Total Laporanku</span>
            <span class="text-2xl font-bold text-white">{{ $stats['total'] }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-amber-950/20 border border-amber-500/20">
            <span class="text-xs text-amber-400 block mb-1">Menunggu Verifikasi</span>
            <span class="text-2xl font-bold text-amber-400">{{ $stats['pending'] }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-blue-950/20 border border-blue-500/20">
            <span class="text-xs text-blue-400 block mb-1">Sedang Diproses</span>
            <span class="text-2xl font-bold text-blue-400">{{ $stats['in_progress'] }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-emerald-950/20 border border-emerald-500/20">
            <span class="text-xs text-emerald-400 block mb-1">Selesai Dibersihkan</span>
            <span class="text-2xl font-bold text-emerald-400">{{ $stats['completed'] }}</span>
        </div>
    </div>

    <!-- Reports History Table -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
        <h3 class="text-sm font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-emerald-400"></i>
            <span>Riwayat & Status Laporan Anda</span>
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-3">Foto</th>
                        <th class="py-3 px-3">Judul & Keterangan</th>
                        <th class="py-3 px-3">Lokasi</th>
                        <th class="py-3 px-3">Kategori AI</th>
                        <th class="py-3 px-3">Status Penanganan</th>
                        <th class="py-3 px-3">Catatan Petugas</th>
                        <th class="py-3 px-3">Waktu Lapor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($reports as $report)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-3">
                            <img src="{{ $report->image_url }}" alt="Thumbnail" class="w-14 h-14 object-cover rounded-xl border border-slate-800">
                        </td>
                        <td class="py-3 px-3 max-w-xs">
                            <div class="font-bold text-white text-sm truncate">{{ $report->title }}</div>
                            <div class="text-[11px] text-slate-400 line-clamp-2 mt-0.5">{{ $report->description }}</div>
                        </td>
                        <td class="py-3 px-3 text-slate-300 max-w-xs">
                            <div class="truncate">{{ $report->address_note ?? ($report->latitude . ', ' . $report->longitude) }}</div>
                        </td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold 
                                {{ $report->waste_category === 'Plastic Waste' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30' : '' }}
                                {{ $report->waste_category === 'Organic Waste' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : '' }}
                                {{ $report->waste_category === 'Paper Waste' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : '' }}
                                {{ $report->waste_category === 'Unclassified' ? 'bg-slate-500/10 text-slate-400 border border-slate-500/30' : '' }}">
                                {{ $report->waste_category }}
                            </span>
                        </td>
                        <td class="py-3 px-3">
                            @if($report->status === 'pending')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">Menunggu</span>
                            @elseif($report->status === 'in_progress')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/30">Diproses</span>
                            @elseif($report->status === 'completed')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">Selesai</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">Ditolak</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-slate-300 max-w-xs">
                            {{ $report->admin_notes ?? '-' }}
                        </td>
                        <td class="py-3 px-3 text-slate-400 text-[11px] whitespace-nowrap">
                            {{ $report->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500">
                            Anda belum pernah membuat laporan sampah. Klik tombol di atas untuk membuat laporan pertama Anda!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
        <div class="pt-4 border-t border-slate-800">
            {{ $reports->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
