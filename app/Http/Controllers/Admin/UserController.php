<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    private const ROLES = ['admin', 'super_admin'];

    public function index()
    {
        $users = User::whereIn('role', self::ROLES)->latest()->get();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create', ['roles' => self::ROLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'nik' => ['required', 'string', 'digits:16', Rule::unique(User::class)],
            'role' => ['required', Rule::in(self::ROLES)],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nik' => $validated['nik'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'status' => 'approved',
        ]);

        event(new Registered($user));

        return redirect()->route('users.index')
            ->with('success', 'Akun '.$user->name.' berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    /**
     * Ubah data akun yang sudah ada. Ini jalur untuk mengisi NIK akun lama,
     * termasuk akun Super Admin bawaan yang dibuat sebelum kolom NIK ada.
     *
     * Role dan status sengaja tidak diubah dari sini supaya hak akses tidak
     * bisa bergeser tanpa sengaja.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'nik' => ['required', 'string', 'digits:16', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nik' => $validated['nik'],
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Data akun '.$user->name.' berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus akun Super Admin.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus akun yang sedang digunakan.');
        }

        DB::transaction(function () use ($user) {
            $user->passkeys()->delete();

            DB::table('sessions')->where('user_id', $user->id)->delete();

            $user->delete();
        });

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
