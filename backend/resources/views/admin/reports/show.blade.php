@extends('layouts.app')

@section('title', 'Detail Laporan: ' . $report->title)
@section('page_title', 'Detail Laporan Sampah #' . $report->id)

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Daftar Laporan</span>
        </a>

        <div class="flex items-center gap-3">
            @if($report->status === 'pending')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span> Menunggu Verifikasi
                </span>
            @elseif($report->status === 'in_progress')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/30 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span> Dalam Penanganan
                </span>
            @elseif($report->status === 'completed')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i> Selesai Ditangani
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30 flex items-center gap-1.5">
                    <i class="fa-solid fa-xmark"></i> Ditolak
                </span>
            @endif
        </div>
    </div>

    <!-- Main Content 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Report Info & Media -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Image Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl overflow-hidden">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-camera text-emerald-400"></i>
                    <span>Bukti Foto Sampah</span>
                </h3>
                <div class="rounded-2xl overflow-hidden border border-slate-800 bg-slate-950 max-h-96 flex items-center justify-center">
                    <img src="{{ $report->image_url }}" alt="{{ $report->title }}" class="w-full h-full object-contain">
                </div>
            </div>

            <!-- Details & AI Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-5">
                <div>
                    <h2 class="text-xl font-bold text-white mb-2">{{ $report->title }}</h2>
                    <p class="text-slate-300 text-sm leading-relaxed whitespace-pre-line">{{ $report->description }}</p>
                </div>

                <!-- AI Detection Result Box -->
                <div class="p-4 rounded-2xl bg-slate-950/70 border border-emerald-500/20">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-emerald-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-brain"></i> Analisis Deteksi AI (FastAPI ML Service)
                        </span>
                        @if($report->ai_confidence)
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300">
                                {{ round($report->ai_confidence * 100, 1) }}% Keyakinan
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-xl text-sm font-bold 
                            {{ $report->waste_category === 'Plastic Waste' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : '' }}
                            {{ $report->waste_category === 'Organic Waste' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : '' }}
                            {{ $report->waste_category === 'Paper Waste' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : '' }}
                            {{ $report->waste_category === 'Unclassified' ? 'bg-slate-500/20 text-slate-300 border border-slate-500/30' : '' }}">
                            {{ $report->waste_category }}
                        </span>
                        <span class="text-xs text-slate-400">
                            @if($report->waste_category === 'Organic Waste')
                                Sampah basah/organik dapat diolah menjadi kompos atau biogas.
                            @elseif($report->waste_category === 'Plastic Waste')
                                Sampah anorganik plastik dapat didaur ulang melalui bank sampah.
                            @elseif($report->waste_category === 'Paper Waste')
                                Sampah kertas dan kardus dapat didaur ulang menjadi bubur kertas.
                            @else
                                Kategori belum ditentukan secara otomatis.
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Reporter Metadata -->
                <div class="pt-4 border-t border-slate-800 grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-500 block mb-1">Identitas Pelapor:</span>
                        <span class="font-bold text-white text-sm block">{{ $report->user->name ?? 'Anonim' }}</span>
                        <span class="text-slate-400">{{ $report->user->email ?? '-' }} ({{ $report->user->phone ?? 'Tanpa HP' }})</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-1">Waktu Pelaporan:</span>
                        <span class="font-semibold text-slate-200 block">{{ $report->created_at->format('d F Y, H:i') }} WIB</span>
                        <span class="text-slate-500">({{ $report->created_at->diffForHumans() }})</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 1 Col: GPS Map & Status Update Form -->
        <div class="space-y-6">

            <!-- Interactive GPS Map Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-emerald-400"></i>
                        <span>Lokasi Koordinat GPS</span>
                    </h3>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $report->latitude }},{{ $report->longitude }}" target="_blank"
                       class="text-[11px] text-emerald-400 hover:text-emerald-300 font-semibold flex items-center gap-1">
                        <span>Buka Google Maps</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                    </a>
                </div>

                <div id="detailMap" class="w-full h-56 rounded-2xl border border-slate-800 overflow-hidden z-10"></div>

                <div class="text-xs space-y-1">
                    <div class="text-slate-400">Catatan Alamat / Landmark:</div>
                    <div class="font-semibold text-white bg-slate-950 p-2.5 rounded-xl border border-slate-800">
                        {{ $report->address_note ?? 'Tidak ada catatan alamat spesifik' }}
                    </div>
                    <div class="text-[10px] text-slate-500 pt-1">
                        Lat: {{ $report->latitude }} | Long: {{ $report->longitude }}
                    </div>
                </div>
            </div>

            <!-- Action Status Update Form -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-emerald-400"></i>
                    <span>Tindak Lanjut & Update Status</span>
                </h3>

                <form action="{{ route('admin.reports.updateStatus', $report->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="status" class="block text-xs font-medium text-slate-300 mb-1.5">Pilih Status Baru</label>
                        <select name="status" id="status" required
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-sm focus:outline-none focus:border-emerald-500 transition">
                            <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Pending (Menunggu Verifikasi)</option>
                            <option value="in_progress" {{ $report->status === 'in_progress' ? 'selected' : '' }}>In Progress (Dalam Penanganan)</option>
                            <option value="completed" {{ $report->status === 'completed' ? 'selected' : '' }}>Completed (Selesai Dibersihkan)</option>
                            <option value="rejected" {{ $report->status === 'rejected' ? 'selected' : '' }}>Rejected (Laporan Ditolak)</option>
                        </select>
                    </div>

                    <div>
                        <label for="admin_notes" class="block text-xs font-medium text-slate-300 mb-1.5">Catatan Petugas / Tindak Lanjut</label>
                        <textarea name="admin_notes" id="admin_notes" rows="3"
                                  placeholder="Contoh: Petugas truk kebersihan no. 04 sudah diterjunkan..."
                                  class="w-full p-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-emerald-500 transition">{{ old('admin_notes', $report->admin_notes) }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Perubahan Status</span>
                    </button>
                </form>

                @if($report->resolved_at)
                <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-[11px] text-emerald-400 flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span>Telah diselesaikan pada: {{ $report->resolved_at->format('d M Y, H:i') }} WIB</span>
                </div>
                @endif
            </div>

            <!-- Delete Action -->
            <form action="{{ route('admin.reports.destroy', $report->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan ini? Data yang dihapus tidak dapat dipulihkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 font-semibold text-xs border border-rose-500/30 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-trash-can"></i>
                    <span>Hapus Laporan Ini</span>
                </button>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var lat = {{ $report->latitude }};
        var lng = {{ $report->longitude }};
        var map = L.map('detailMap').setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker = L.circleMarker([lat, lng], {
            radius: 12,
            fillColor: '#10b981',
            color: '#ffffff',
            weight: 3,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(map);

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            var div = document.createElement('div');
            div.textContent = String(str);
            return div.innerHTML;
        }

        var reportTitle = @json($report->title);
        var reportAddress = @json($report->address_note ?? '');
        var popupHtml = "<b>" + escapeHtml(reportTitle) + "</b>" + (reportAddress ? "<br>" + escapeHtml(reportAddress) : "");
        marker.bindPopup(popupHtml).openPopup();
    });
</script>
@endpush
@endsection
