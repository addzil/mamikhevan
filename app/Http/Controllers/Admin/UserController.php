<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $staffUsers = User::query()
            ->whereIn('role', User::ADMIN_ROLES)
            ->latest()
            ->paginate(15, ['*'], 'admin_page')
            ->appends($request->except('admin_page'));
        $customers = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->latest()
            ->paginate(15, ['*'], 'customer_page')
            ->appends($request->except('customer_page'));

        return view('admin.users.index', compact('staffUsers', 'customers'));
    }

    public function create(): View
    {
        $user = new User;
        $roles = User::ROLE_LABELS;

        return view('admin.users.create', compact('user', 'roles'));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $role = $validated['role'];

        $user = new User;
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
        ]);
        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Akun berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        $roles = User::ROLE_LABELS;

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $role = $validated['role'];
        $result = DB::transaction(function () use ($user, $validated, $role): array {
            $administrators = User::query()
                ->where('role', User::ROLE_ADMINISTRATOR)
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->hasRole(User::ROLE_ADMINISTRATOR)
                && $role !== User::ROLE_ADMINISTRATOR
                && $administrators->count() <= 1) {
                return ['error' => 'Akun Administrator terakhir tidak dapat diturunkan perannya.'];
            }

            $emailChanged = $lockedUser->email !== $validated['email'];
            $lockedUser->name = $validated['name'];
            $lockedUser->email = $validated['email'];
            $lockedUser->role = $role;

            if (filled($validated['password'] ?? null)) {
                $lockedUser->password = Hash::make($validated['password']);
            }

            if ($emailChanged) {
                $lockedUser->email_verified_at = null;
            }

            $lockedUser->save();

            return ['email_changed' => $emailChanged];
        });

        if (isset($result['error'])) {
            return back()->withInput()->with('error', $result['error']);
        }

        if ($result['email_changed']) {
            $user->refresh()->sendEmailVerificationNotification();
        }

        return redirect()->route('admin.users.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        $deleted = DB::transaction(function () use ($user): bool {
            $administrators = User::query()
                ->where('role', User::ROLE_ADMINISTRATOR)
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->hasRole(User::ROLE_ADMINISTRATOR) && $administrators->count() <= 1) {
                return false;
            }

            $lockedUser->delete();

            return true;
        });

        if (! $deleted) {
            return back()->with('error', 'Akun Administrator terakhir tidak dapat dihapus.');
        }

        return redirect()->route('admin.users.index')->with('success', 'Akun berhasil dihapus.');
    }
}
