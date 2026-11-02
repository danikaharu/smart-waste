@extends('layouts.auth')

@section('title', 'Pendaftaran Akun Warga')

@section('content')
<div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl shadow-emerald-950/50">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-white">Buat Akun Warga</h2>
        <p class="text-xs text-slate-400 mt-1">Daftarkan diri Anda untuk berpartisipasi melaporkan sampah di lingkungan sekitar.</p>
    </div>

    @if($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('register') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-xs font-medium text-slate-300 mb-1.5">Nama Lengkap</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                    <i class="fa-regular fa-user"></i>
                </span>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                       placeholder="Contoh: Ahmad Fauzi"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
            </div>
        </div>

        <div>
            <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">Alamat Email</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                    <i class="fa-regular fa-envelope"></i>
                </span>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       placeholder="ahmad@gmail.com"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
            </div>
        </div>

        <div>
            <label for="phone" class="block text-xs font-medium text-slate-300 mb-1.5">Nomor WhatsApp / HP (Opsional)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                    <i class="fa-solid fa-phone"></i>
                </span>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                       placeholder="081234567890"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
            </div>
        </div>

        <div>
            <label for="password" class="block text-xs font-medium text-slate-300 mb-1.5">Kata Sandi (Min. 6 Karakter)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                    <i class="fa-solid fa-lock"></i>
                </span>
                <input type="password" id="password" name="password" required
                       placeholder="••••••••"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
            </div>
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-medium text-slate-300 mb-1.5">Ulangi Kata Sandi</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                    <i class="fa-solid fa-lock"></i>
                </span>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       placeholder="••••••••"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
            </div>
        </div>

        <button type="submit"
                class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-semibold text-sm shadow-lg shadow-emerald-600/30 hover:shadow-emerald-500/50 transition duration-200 flex items-center justify-center gap-2 mt-4">
            <span>Daftar Akun Baru</span>
            <i class="fa-solid fa-user-plus text-xs"></i>
        </button>
    </form>

    <div class="mt-6 pt-6 border-t border-slate-800/80 text-center text-xs text-slate-400">
        Sudah memiliki akun?
        <a href="{{ route('login') }}" class="text-emerald-400 hover:text-emerald-300 font-semibold ml-1">Masuk di sini</a>
    </div>
</div>
@endsection
