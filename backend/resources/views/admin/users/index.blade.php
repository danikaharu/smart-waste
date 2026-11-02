@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page_title', 'Manajemen Pengguna & Otorisasi Role')

@section('content')
<div class="space-y-6">

    <!-- Counter Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 block mb-1">Total Pengguna</span>
                <span class="text-2xl font-bold text-white">{{ $counts['total'] }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-indigo-950/20 border border-indigo-500/20 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-indigo-400 block mb-1">Administrator</span>
                <span class="text-2xl font-bold text-indigo-400">{{ $counts['admins'] }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-emerald-950/20 border border-emerald-500/20 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-emerald-400 block mb-1">Warga Pelapor</span>
                <span class="text-2xl font-bold text-emerald-400">{{ $counts['citizens'] }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                <i class="fa-solid fa-user-group"></i>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, email, atau nomor HP pengguna..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-emerald-500 transition">
            </div>

            <div class="w-full md:w-56">
                <select name="role" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-white text-sm focus:outline-none focus:border-emerald-500 transition">
                    <option value="all" {{ $role === 'all' || !$role ? 'selected' : '' }}>Semua Role</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin Saja</option>
                    <option value="user" {{ $role === 'user' ? 'selected' : '' }}>Warga Saja</option>
                </select>
            </div>

            <button type="submit" class="py-2.5 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter text-xs"></i>
                <span>Filter</span>
            </button>
        </form>
    </div>

    <!-- Users Table -->
    <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-4 px-4">Pengguna</th>
                        <th class="py-4 px-4">Kontak</th>
                        <th class="py-4 px-4">Role Akun</th>
                        <th class="py-4 px-4 text-center">Jumlah Laporan</th>
                        <th class="py-4 px-4">Status Akun</th>
                        <th class="py-4 px-4">Terdaftar</th>
                        <th class="py-4 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($users as $u)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-sm">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-white text-sm">{{ $u->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-300">
                            {{ $u->phone ?? 'Tidak ada nomor HP' }}
                        </td>
                        <td class="py-3 px-4">
                            @if($u->role === 'admin')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 inline-flex items-center gap-1">
                                    <i class="fa-solid fa-shield-halved text-[9px]"></i> Admin
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 inline-flex items-center gap-1">
                                    <i class="fa-solid fa-user text-[9px]"></i> Warga
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-800 text-slate-200 font-bold text-xs">
                                {{ $u->reports_count }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($u->is_active)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    Aktif
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-400 text-[11px]">
                            {{ $u->created_at->format('d M Y') }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            @if($u->id !== auth()->id())
                            <div class="inline-flex items-center gap-1.5">
                                <!-- Role Toggle Form -->
                                <form action="{{ route('admin.users.updateRole', $u->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="role" value="{{ $u->role === 'admin' ? 'user' : 'admin' }}">
                                    <button type="submit" title="Ubah Role"
                                            class="p-1.5 rounded-lg bg-slate-800 hover:bg-indigo-500/20 text-slate-300 hover:text-indigo-400 border border-slate-700/50 hover:border-indigo-500/30 transition text-xs">
                                        <i class="fa-solid fa-repeat"></i>
                                    </button>
                                </form>

                                <!-- Active Toggle Form -->
                                <form action="{{ route('admin.users.toggleActive', $u->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" title="{{ $u->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}"
                                            class="p-1.5 rounded-lg bg-slate-800 hover:bg-rose-500/20 text-slate-300 hover:text-rose-400 border border-slate-700/50 hover:border-rose-500/30 transition text-xs">
                                        <i class="fa-solid {{ $u->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>
                                </form>
                            </div>
                            @else
                            <span class="text-[10px] text-slate-500 italic">Akun Anda</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-slate-500">
                            Tidak ada data pengguna yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            {{ $users->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
