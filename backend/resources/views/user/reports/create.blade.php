@extends('layouts.app')

@section('title', 'Buat Laporan Sampah Baru')
@section('page_title', 'Form Pelaporan Sampah Lingkungan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('user.dashboard') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Beranda</span>
        </a>
    </div>

    <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl space-y-6">
        <div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-camera text-emerald-400"></i>
                <span>Laporkan Tumpukan Sampah</span>
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                Lengkapi formulir di bawah ini dengan foto dan titik lokasi yang akurat. Sistem AI kami akan otomatis mengklasifikasikan jenis sampah Anda.
            </p>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('user.reports.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Judul Laporan -->
            <div>
                <label for="title" class="block text-xs font-semibold text-slate-300 mb-1.5">Judul Laporan *</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                       placeholder="Contoh: Tumpukan Botol Plastik di Pinggir Jalan Raya"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 transition">
            </div>

            <!-- Keterangan / Deskripsi -->
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-300 mb-1.5">Keterangan / Deskripsi Laporan *</label>
                <textarea id="description" name="description" rows="3" required
                          placeholder="Jelaskan kondisi tumpukan sampah, perkiraan volume, bau, atau hambatan yang ditimbulkan..."
                          class="w-full p-4 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 transition">{{ old('description') }}</textarea>
            </div>

            <!-- Upload Foto Sampah -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Unggah Foto Bukti Sampah *</label>
                <div class="border-2 border-dashed border-slate-800 hover:border-emerald-500/50 rounded-2xl p-6 text-center bg-slate-950/60 transition cursor-pointer relative"
                     onclick="document.getElementById('imageInput').click()">
                    <input type="file" id="imageInput" name="image" accept="image/*" required class="hidden" onchange="previewImage(event)">
                    <div id="uploadPrompt" class="space-y-2">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-emerald-400"></i>
                        <div class="text-sm font-semibold text-slate-200">Klik untuk memilih foto sampah</div>
                        <div class="text-[11px] text-slate-500">Mendukung format JPG, PNG, atau WebP (Maksimal 10 MB)</div>
                    </div>
                    <img id="imagePreview" src="#" alt="Preview" class="hidden max-h-64 mx-auto rounded-xl object-contain border border-slate-800">
                </div>
            </div>

            <!-- Geolocation Picker & Map -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-semibold text-slate-300">Titik Koordinat Lokasi (GPS) *</label>
                    <button type="button" onclick="getCurrentLocation()"
                            class="px-3 py-1 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 font-semibold text-xs border border-emerald-500/30 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-crosshairs"></i>
                        <span>Gunakan Lokasi Saya Saat Ini</span>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <input type="text" id="latitude" name="latitude" value="{{ old('latitude', '-6.2088000') }}" required readonly
                               class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 text-xs focus:outline-none">
                    </div>
                    <div>
                        <input type="text" id="longitude" name="longitude" value="{{ old('longitude', '106.8456000') }}" required readonly
                               class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 text-xs focus:outline-none">
                    </div>
                </div>

                <!-- Leaflet interactive picker -->
                <div class="text-[11px] text-slate-400">Geser atau klik peta untuk menyesuaikan titik lokasi:</div>
                <div id="pickerMap" class="w-full h-64 rounded-2xl border border-slate-800 overflow-hidden z-10"></div>
            </div>

            <!-- Catatan Alamat / Patokan -->
            <div>
                <label for="address_note" class="block text-xs font-semibold text-slate-300 mb-1.5">Patokan / Catatan Alamat Tambahan (Opsional)</label>
                <input type="text" id="address_note" name="address_note" value="{{ old('address_note') }}"
                       placeholder="Contoh: Di samping pos satpam RW 05, seberang warung makan..."
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 transition">
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold text-sm shadow-xl shadow-emerald-600/30 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Kirim Laporan Sampah</span>
            </button>
        </form>
    </div>

</div>

@push('scripts')
<script>
    var map, marker;
    var initLat = parseFloat(document.getElementById('latitude').value) || -6.2088;
    var initLng = parseFloat(document.getElementById('longitude').value) || 106.8456;

    document.addEventListener("DOMContentLoaded", function () {
        map = L.map('pickerMap').setView([initLat, initLng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);

        marker.on('dragend', function (e) {
            var position = marker.getLatLng();
            updateCoords(position.lat, position.lng);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            updateCoords(e.latlng.lat, e.latlng.lng);
        });
    });

    function updateCoords(lat, lng) {
        document.getElementById('latitude').value = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);
    }

    function getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (pos) {
                var lat = pos.coords.latitude;
                var lng = pos.coords.longitude;
                updateCoords(lat, lng);
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 16);
            }, function (err) {
                alert("Gagal mendeteksi lokasi otomatis. Silakan klik langsung pada peta.");
            });
        } else {
            alert("Geolocation tidak didukung oleh browser ini.");
        }
    }

    function previewImage(event) {
        var input = event.target;
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('uploadPrompt').classList.add('hidden');
                var img = document.getElementById('imagePreview');
                img.src = e.target.result;
                img.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
@endsection
