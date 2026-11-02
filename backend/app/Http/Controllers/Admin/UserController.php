<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');

        $users = User::withCount('reports')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($role && $role !== 'all', function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $counts = [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'citizens' => User::where('role', 'user')->count(),
        ];

        return view('admin.users.index', compact('users', 'counts', 'search', 'role'));
    }

    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
        }

        $validated = $request->validate([
            'role' => ['required', 'in:admin,user'],
        ]);

        $user->role = $validated['role'];
        $user->save();

        return back()->with('success', "Role pengguna '{$user->name}' berhasil diubah menjadi " . ucfirst($user->role) . ".");
    }

    public function toggleActive($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun '{$user->name}' berhasil {$statusStr}.");
    }
}
