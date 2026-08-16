<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RegistrationInvitationService;
use App\Support\StaffAccess;
use App\Support\StrongPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $staff = User::query()
            ->where('is_admin', true)
            ->get()
            ->sortBy(fn (User $person): string => sprintf(
                '%02d-%s',
                StaffAccess::roleSortOrder($person->staffRole()),
                Str::lower($person->name)
            ))
            ->values();

        return view('admin.staff.index', [
            'staff' => $staff,
            'staffingPlan' => StaffAccess::staffingPlan(),
            'roleCounts' => $staff->countBy(fn (User $person): string => $person->staffRole() ?? 'unknown'),
            'officialEmailDomain' => StaffAccess::officialEmailDomain(),
        ]);
    }

    public function create(): View
    {
        return view('admin.staff.form', [
            'roles' => StaffAccess::roles(),
            'roleDescriptions' => StaffAccess::descriptions(),
            'officialEmailDomain' => StaffAccess::officialEmailDomain(),
        ]);
    }

    public function store(Request $request, RegistrationInvitationService $registration): RedirectResponse
    {
        $this->normalizeEmail($request);

        $data = $request->validate([
            'name' => ['required','string','max:100'],
            'email' => $this->emailRules(),
            'role' => ['required', Rule::in(array_keys(StaffAccess::roles()))],
        ]);

        $staff = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make(Str::random(64)),
            'is_admin' => true,
            'role' => $data['role'],
            'is_active' => false,
            'email_verified_at' => null,
            'password_changed_at' => null,
            'force_password_reset' => false,
        ]);

        try {
            $registration->issue($staff, $request->user());
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('admin.staff.index')
                ->with('warning', 'The account was created inactive, but the registration email could not be sent. Use Resend registration email after mail is configured.');
        }

        return redirect()->route('admin.staff.index')
            ->with('success', 'Registration invitation sent. The account stays inactive until the strong password and email OTP steps are completed.');
    }

    public function edit(User $staff): View
    {
        $this->ensureEditable($staff);

        return view('admin.staff.form', [
            'staff' => $staff,
            'roles' => StaffAccess::roles(),
            'roleDescriptions' => StaffAccess::descriptions(),
            'officialEmailDomain' => StaffAccess::officialEmailDomain(),
        ]);
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        $this->ensureEditable($staff);
        $this->normalizeEmail($request);

        $data = $request->validate([
            'name' => ['required','string','max:100'],
            'email' => $this->emailRules($staff),
            'role' => ['required', Rule::in(array_keys(StaffAccess::roles()))],
            'is_active' => ['nullable','boolean'],
            'force_password_reset' => ['nullable','boolean'],
            'password' => StrongPassword::rules([$request->input('name'), $request->input('email')], 'nullable'),
        ]);

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active'),
            'force_password_reset' => $request->boolean('force_password_reset'),
            'is_admin' => true,
        ];

        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
            $update['password_changed_at'] = now();
        }

        $staff->update($update);

        return redirect()->route('admin.staff.index')->with('success', 'Staff account and security flags updated.');
    }

    public function resendRegistration(Request $request, User $staff, RegistrationInvitationService $registration): RedirectResponse
    {
        $this->ensureStaffAccount($staff);

        if ($staff->email_verified_at !== null || $staff->is_active === true) {
            return back()->withErrors(['registration' => 'Only inactive, unverified staff accounts can receive a registration invitation.']);
        }

        try {
            $registration->issue($staff, $request->user());
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['registration' => 'The registration email could not be sent. Check the school mail configuration and try again.']);
        }

        return back()->with('success', 'A fresh registration invitation was sent to '.$staff->email.'.');
    }

    public function resetTwoFactor(Request $request, User $staff): RedirectResponse
    {
        $this->ensureStaffAccount($staff);
        abort_if($request->user()?->id === $staff->id, 403, 'Use Login & Security to manage your own two-factor authentication.');

        $staff->forceFill([
            'two_factor_secret' => null,
            'two_factor_enabled_at' => null,
            'two_factor_recovery_codes' => null,
            'force_password_reset' => true,
        ])->save();

        return back()->with('success', 'Two-factor authentication reset. Require the staff member to verify identity and re-enroll.');
    }

    private function normalizeEmail(Request $request): void
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);
    }

    private function emailRules(?User $staff = null): array
    {
        $rules = [
            'required',
            'email:rfc',
            'max:150',
            $staff
                ? Rule::unique('users','email')->ignore($staff->id)
                : Rule::unique('users','email'),
        ];

        if ($domain = StaffAccess::officialEmailDomain()) {
            $rules[] = 'ends_with:@'.$domain;
        }

        return $rules;
    }

    private function ensureStaffAccount(User $staff): void
    {
        abort_unless($staff->is_admin === true, 404);
    }

    private function ensureEditable(User $staff): void
    {
        $this->ensureStaffAccount($staff);
        abort_if($staff->isSuperAdmin(), 403, 'Super Administrator accounts are protected from role/deactivation edits.');
    }
}
