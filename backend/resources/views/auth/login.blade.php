@extends('layouts.auth')

@section('title', 'Masuk ke Akun Anda')

@section('content')
<div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl shadow-emerald-950/50">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-white">Selamat Datang Kembali</h2>
        <p class="text-xs text-slate-400 mt-1">Silakan masuk dengan kredensial akun Anda.</p>
    </div>

    <!-- Demo Accounts Helper Box -->
    <div class="mb-6 p-3 rounded-2xl bg-emerald-950/40 border border-emerald-500/20 text-xs">
        <div class="font-semibold text-emerald-400 flex items-center gap-1.5 mb-1.5">
            <i class="fa-solid fa-circle-info"></i> Akun Demo Tersedia:
        </div>
        <div class="grid grid-cols-2 gap-2 text-slate-300">
            <button type="button" onclick="fillCreds('admin@smartwaste.com', 'password')" class="text-left p-1.5 rounded-lg bg-slate-800/60 hover:bg-emerald-900/40 border border-slate-700/50 hover:border-emerald-500/40 transition">
                <span class="block font-bold text-emerald-300">👑 Admin</span>
                <span class="text-[10px] text-slate-400 truncate block">admin@smartwaste.com</span>
            </button>
            <button type="button" onclick="fillCreds('user@smartwaste.com', 'password')" class="text-left p-1.5 rounded-lg bg-slate-800/60 hover:bg-emerald-900/40 border border-slate-700/50 hover:border-emerald-500/40 transition">
                <span class="block font-bold text-emerald-300">👤 Warga</span>
                <span class="text-[10px] text-slate-400 truncate block">user@smartwaste.com</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">Alamat Email</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                    <i class="fa-regular fa-envelope"></i>
                </span>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="nama@smartwaste.com"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
            </div>
        </div>

        <div>
            <label for="password" class="block text-xs font-medium text-slate-300 mb-1.5">Kata Sandi</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                    <i class="fa-solid fa-lock"></i>
                </span>
                <input type="password" id="password" name="password" required
                       placeholder="••••••••"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
            </div>
        </div>

        <div class="flex items-center justify-between text-xs pt-1">
            <label class="flex items-center text-slate-400 cursor-pointer select-none">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-emerald-500 focus:ring-emerald-500 mr-2">
                Ingat Saya
            </label>
        </div>

        <button type="submit"
                class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-semibold text-sm shadow-lg shadow-emerald-600/30 hover:shadow-emerald-500/50 transition duration-200 flex items-center justify-center gap-2 mt-2">
            <span>Masuk ke Dashboard</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </button>
    </form>

    <div class="mt-6 pt-6 border-t border-slate-800/80 text-center text-xs text-slate-400">
        Belum memiliki akun warga?
        <a href="{{ route('register') }}" class="text-emerald-400 hover:text-emerald-300 font-semibold ml-1">Daftar Sekarang</a>
    </div>
</div>

<script>
    function fillCreds(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }
</script>
@endsection
