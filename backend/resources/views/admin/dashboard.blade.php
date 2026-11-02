@extends('layouts.app')

@section('title', 'Dashboard Statistik & Pemantauan Sampah')
@section('page_title', 'Dashboard Statistik & GIS Pemantauan Sampah')

@section('content')
<div class="space-y-6">

    <!-- 1. Metric Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Total Laporan -->
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span>Total Laporan</span>
                <i class="fa-solid fa-layer-group text-slate-500"></i>
            </div>
            <div class="text-2xl font-bold text-white">{{ $stats['total_reports'] }}</div>
            <div class="text-[10px] text-slate-500 mt-1">Seluruh laporan tercatat</div>
        </div>

        <!-- Pending -->
        <div class="p-4 rounded-2xl bg-amber-950/20 border border-amber-500/20 shadow-sm">
            <div class="flex items-center justify-between text-amber-400 text-xs mb-2">
                <span>Menunggu</span>
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="text-2xl font-bold text-amber-400">{{ $stats['pending'] }}</div>
            <div class="text-[10px] text-amber-500/70 mt-1">Perlu diverifikasi</div>
        </div>

        <!-- In Progress -->
        <div class="p-4 rounded-2xl bg-blue-950/20 border border-blue-500/20 shadow-sm">
            <div class="flex items-center justify-between text-blue-400 text-xs mb-2">
                <span>Diproses</span>
                <i class="fa-solid fa-person-digging"></i>
            </div>
            <div class="text-2xl font-bold text-blue-400">{{ $stats['in_progress'] }}</div>
            <div class="text-[10px] text-blue-500/70 mt-1">Penanganan lapangan</div>
        </div>

        <!-- Selesai -->
        <div class="p-4 rounded-2xl bg-emerald-950/20 border border-emerald-500/20 shadow-sm">
            <div class="flex items-center justify-between text-emerald-400 text-xs mb-2">
                <span>Selesai</span>
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="text-2xl font-bold text-emerald-400">{{ $stats['completed'] }}</div>
            <div class="text-[10px] text-emerald-500/70 mt-1">Berhasil dibersihkan</div>
        </div>

        <!-- Ditolak -->
        <div class="p-4 rounded-2xl bg-rose-950/20 border border-rose-500/20 shadow-sm">
            <div class="flex items-center justify-between text-rose-400 text-xs mb-2">
                <span>Ditolak</span>
                <i class="fa-solid fa-ban"></i>
            </div>
            <div class="text-2xl font-bold text-rose-400">{{ $stats['rejected'] }}</div>
            <div class="text-[10px] text-rose-500/70 mt-1">Laporan tidak valid</div>
        </div>

        <!-- Total Warga -->
        <div class="p-4 rounded-2xl bg-indigo-950/20 border border-indigo-500/20 shadow-sm">
            <div class="flex items-center justify-between text-indigo-400 text-xs mb-2">
                <span>Warga Terdaftar</span>
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="text-2xl font-bold text-indigo-400">{{ $stats['total_users'] }}</div>
            <div class="text-[10px] text-indigo-500/70 mt-1">Pelapor aktif</div>
        </div>
    </div>

    <!-- 2. Interactive GIS Map Section -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-map-location-dot text-emerald-400"></i>
                    <span>Peta Sebaran Lokasi Sampah (GIS)</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Pemantauan titik koordinat GPS sampah yang dilaporkan masyarakat secara real-time.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-400"></span> Pending</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-400"></span> In Progress</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-400"></span> Completed</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-400"></span> Rejected</span>
            </div>
        </div>
        <div id="wasteMap" class="w-full h-80 rounded-2xl border border-slate-800 overflow-hidden z-10"></div>
    </div>

    <!-- 3. Chart Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Donut Chart: Waste Category Distribution -->
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
            <h3 class="text-sm font-bold text-white flex items-center gap-2 mb-4">
                <i class="fa-solid fa-chart-pie text-emerald-400"></i>
                <span>Distribusi Kategori Sampah (AI)</span>
            </h3>
            <div class="h-64 flex items-center justify-center">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Line Chart: Monthly Report Trend -->
        <div class="lg:col-span-2 p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
            <h3 class="text-sm font-bold text-white flex items-center gap-2 mb-4">
                <i class="fa-solid fa-chart-line text-emerald-400"></i>
                <span>Tren Laporan Sampah Masuk</span>
            </h3>
            <div class="h-64">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 4. Recent Reports Table -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-emerald-400"></i>
                <span>Laporan Terbaru Masuk</span>
            </h3>
            <a href="{{ route('admin.reports.index') }}" class="text-xs text-emerald-400 hover:text-emerald-300 font-semibold flex items-center gap-1">
                <span>Lihat Semua Laporan</span>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 font-medium">
                        <th class="py-3 px-3">Foto</th>
                        <th class="py-3 px-3">Judul & Lokasi</th>
                        <th class="py-3 px-3">Kategori AI</th>
                        <th class="py-3 px-3">Pelapor</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3">Waktu</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($recentReports as $report)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-3">
                            <img src="{{ $report->image_url }}" alt="Thumbnail" class="w-12 h-12 object-cover rounded-xl border border-slate-800">
                        </td>
                        <td class="py-3 px-3 max-w-xs">
                            <div class="font-bold text-white truncate">{{ $report->title }}</div>
                            <div class="text-[11px] text-slate-400 truncate">{{ $report->address_note ?? ($report->latitude . ', ' . $report->longitude) }}</div>
                        </td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold 
                                {{ $report->waste_category === 'Plastic Waste' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30' : '' }}
                                {{ $report->waste_category === 'Organic Waste' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : '' }}
                                {{ $report->waste_category === 'Paper Waste' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : '' }}
                                {{ $report->waste_category === 'Unclassified' ? 'bg-slate-500/10 text-slate-400 border border-slate-500/30' : '' }}">
                                {{ $report->waste_category }}
                            </span>
                            @if($report->ai_confidence)
                                <span class="text-[10px] text-slate-400 block mt-0.5">{{ round($report->ai_confidence * 100, 1) }}% match</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-slate-300">
                            {{ $report->user->name ?? 'Anonim' }}
                        </td>
                        <td class="py-3 px-3">
                            @if($report->status === 'pending')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Pending</span>
                            @elseif($report->status === 'in_progress')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">In Progress</span>
                            @elseif($report->status === 'completed')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Completed</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">Rejected</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-slate-400 text-[11px]">
                            {{ $report->created_at->diffForHumans() }}
                        </td>
                        <td class="py-3 px-3 text-right">
                            <a href="{{ route('admin.reports.show', $report->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 font-semibold text-xs border border-emerald-500/30 transition">
                                <span>Detail</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">Belum ada laporan sampah tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // --- 1. LEAFLET GIS MAP ---
        var reportsData = @json($geolocatedReports);
        
        // Default center on Jakarta or first report
        var defaultLat = reportsData.length > 0 ? reportsData[0].latitude : -6.2088;
        var defaultLng = reportsData.length > 0 ? reportsData[0].longitude : 106.8456;

        var map = L.map('wasteMap').setView([defaultLat, defaultLng], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Marker Colors mapping
        function getMarkerColor(status) {
            switch(status) {
                case 'pending': return '#fbbf24';
                case 'in_progress': return '#60a5fa';
                case 'completed': return '#34d399';
                case 'rejected': return '#f87171';
                default: return '#94a3b8';
            }
        }

        reportsData.forEach(function (rep) {
            if (rep.latitude && rep.longitude) {
                var color = getMarkerColor(rep.status);
                var circleMarker = L.circleMarker([rep.latitude, rep.longitude], {
                    radius: 9,
                    fillColor: color,
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.85
                }).addTo(map);

                var popupContent = `
                    <div style="font-family: inherit; width: 200px; color: #0f172a;">
                        <img src="${rep.image_url}" style="width: 100%; height: 110px; object-fit: cover; border-radius: 8px; margin-bottom: 6px;">
                        <strong style="font-size: 13px; display: block; line-height: 1.2;">${rep.title}</strong>
                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Kategori: <b>${rep.waste_category}</b></div>
                        <div style="font-size: 11px; margin-top: 2px;">Status: <b style="text-transform: capitalize; color: ${color};">${rep.status}</b></div>
                        <a href="/admin/reports/${rep.id}" style="display: block; text-align: center; margin-top: 8px; padding: 4px 8px; background: #10b981; color: white; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: bold;">Buka Detail</a>
                    </div>
                `;
                circleMarker.bindPopup(popupContent);
            }
        });

        // --- 2. CATEGORY DONUT CHART ---
        var catCtx = document.getElementById('categoryChart').getContext('2d');
        var catCounts = @json($categoryCounts);
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(catCounts),
                datasets: [{
                    data: Object.values(catCounts),
                    backgroundColor: ['#10b981', '#06b6d4', '#f59e0b', '#64748b'],
                    borderColor: '#0f172a',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#94a3b8', font: { size: 11 } }
                    }
                }
            }
        });

        // --- 3. MONTHLY TREND LINE CHART ---
        var trendCtx = document.getElementById('trendChart').getContext('2d');
        var trends = @json($monthlyTrends);
        var labels = trends.map(t => t.month);
        var counts = trends.map(t => t.count);

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['Bulan Ini'],
                datasets: [{
                    label: 'Jumlah Laporan',
                    data: counts.length ? counts : [reportsData.length],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(51, 65, 85, 0.3)' } },
                    y: { ticks: { color: '#94a3b8', precision: 0 }, grid: { color: 'rgba(51, 65, 85, 0.3)' } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endpush
@endsection
