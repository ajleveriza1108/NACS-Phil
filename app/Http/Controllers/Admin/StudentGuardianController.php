<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use App\Support\StudentAccess;
use App\Support\StudentAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class StudentGuardianController extends Controller
{
    public function store(Request $request, Student $student): RedirectResponse
    {
        abort_unless(StudentAccess::canManageGuardians($request->user()), 403);

        $data = $request->validate([
            'guardian_name' => ['required','string','max:120'],
            'guardian_email' => ['required','email:rfc','max:150'],
            'relationship' => ['required','string','max:64'],
            'temporary_password' => ['nullable','confirmed', Password::min(12)->letters()->mixedCase()->numbers()->symbols()],
            'is_primary' => ['nullable','boolean'],
            'can_view_finance' => ['nullable','boolean'],
        ]);

        $guardian = DB::transaction(function () use ($request, $student, $data): StudentGuardian {
            $email = Str::lower($data['guardian_email']);
            $guardianUser = User::query()->where('email', $email)->first();

            if ($guardianUser) {
                abort_unless(
                    $guardianUser->is_admin !== true
                    && $guardianUser->role === 'parent'
                    && $guardianUser->is_active !== false,
                    422,
                    'That email already belongs to a non-parent account.'
                );
            } else {
                abort_if(
                    empty($data['temporary_password']),
                    422,
                    'A temporary password is required when creating a new parent account.'
                );

                $guardianUser = User::create([
                    'name' => $data['guardian_name'],
                    'email' => $email,
                    'password' => Hash::make($data['temporary_password']),
                    'is_admin' => false,
                    'role' => 'parent',
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'password_changed_at' => now(),
                    'force_password_reset' => true,
                ]);
            }

            $guardian = StudentGuardian::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'user_id' => $guardianUser->id,
                ],
                [
                    'relationship' => $data['relationship'],
                    'is_primary' => $request->boolean('is_primary'),
                    'can_view_finance' => $request->boolean('can_view_finance'),
                ]
            );

            StudentAudit::record(
                $request->user(),
                $student,
                'guardian.saved',
                StudentGuardian::class,
                $guardian,
                ['user_id','relationship','is_primary','can_view_finance'],
                'Parent or guardian access link saved.'
            );

            return $guardian;
        });

        return back()->with('success', 'Parent or guardian access saved.');
    }
}
