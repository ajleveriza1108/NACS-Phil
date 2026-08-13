<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        return view('admin.staff.index', [
            'staff' => User::query()
                ->where('is_admin', true)
                ->orderByRaw("case role when 'super_admin' then 1 when 'principal' then 2 when 'teacher' then 3 else 4 end")
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.staff.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150', 'unique:users,email'],
            'role' => ['required', Rule::in(['principal', 'teacher'])],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)->letters()->mixedCase()->numbers(),
            ],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'is_admin' => true,
            'role' => $data['role'],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff account created.');
    }

    public function edit(User $staff): View
    {
        $this->ensureEditable($staff);

        return view('admin.staff.form', compact('staff'));
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        $this->ensureEditable($staff);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email:rfc',
                'max:150',
                Rule::unique('users', 'email')->ignore($staff->id),
            ],
            'role' => ['required', Rule::in(['principal', 'teacher'])],
            'is_active' => ['nullable', 'boolean'],
            'password' => [
                'nullable',
                'confirmed',
                Password::min(12)->letters()->mixedCase()->numbers(),
            ],
        ]);

        $update = [
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active'),
            'is_admin' => true,
        ];

        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $staff->update($update);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff account updated.');
    }

    private function ensureEditable(User $staff): void
    {
        abort_unless($staff->is_admin === true, 404);
        abort_if($staff->isSuperAdmin(), 403, 'Super Admin accounts are protected from this editor.');
    }
}