<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdmissionApplication;
use App\Models\Student;
use App\Models\User;
use App\Support\StudentAccess;
use App\Services\RegistrationInvitationService;
use App\Support\StudentAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $students = StudentAccess::visibleStudentQuery($request->user())
            ->with('user:id,name,email')
            ->search($request->string('q')->toString())
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.students.index', compact('students'));
    }

    public function create(Request $request): View
    {
        abort_unless(StudentAccess::canCreateStudent($request->user()), 403);

        return view('admin.students.form', [
            'student' => new Student(),
            'levels' => AdmissionApplication::LEVELS,
        ]);
    }

    public function store(Request $request, RegistrationInvitationService $registration): RedirectResponse
    {
        $actor = $request->user();
        abort_unless(StudentAccess::canCreateStudent($actor), 403);

        $data = $request->validate([
            'student_number' => ['required','string','max:64','unique:students,student_number'],
            'first_name' => ['required','string','max:100'],
            'middle_name' => ['nullable','string','max:100'],
            'last_name' => ['required','string','max:100'],
            'preferred_name' => ['nullable','string','max:100'],
            'date_of_birth' => ['nullable','date','before_or_equal:today'],
            'gender' => ['nullable','string','max:32'],
            'phone' => ['nullable','string','max:64'],
            'home_address' => ['nullable','string','max:1000'],
            'grade_level' => ['required', Rule::in(AdmissionApplication::LEVELS)],
            'section' => ['nullable','string','max:100'],
            'school_year' => ['required','string','max:32'],
            'status' => ['required', Rule::in(['active','inactive','graduated','withdrawn'])],
            'student_email' => [
                'nullable','email:rfc','max:150','unique:users,email',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $domain = Str::lower(trim((string) config('student_portal.school_email_domain')));

                    if ($domain !== '' && ! Str::endsWith(Str::lower((string) $value), '@'.$domain)) {
                        $fail("The student email must use the configured school domain @{$domain}.");
                    }
                },
            ],

        ]);

        $student = DB::transaction(function () use ($actor, $data): Student {
            $studentUser = null;

            if (! empty($data['student_email'])) {
                $studentUser = User::create([
                    'name' => trim($data['first_name'].' '.$data['last_name']),
                    'email' => Str::lower($data['student_email']),
                    'password' => Hash::make(Str::random(64)),
                    'is_admin' => false,
                    'role' => 'student',
                    'is_active' => false,
                    'email_verified_at' => null,
                    'password_changed_at' => null,
                    'force_password_reset' => false,
                ]);
            }

            $student = Student::create([
                'user_id' => $studentUser?->id,
                'student_number' => $data['student_number'],
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'preferred_name' => $data['preferred_name'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
                'home_address' => $data['home_address'] ?? null,
                'grade_level' => $data['grade_level'],
                'section' => $data['section'] ?? null,
                'school_year' => $data['school_year'],
                'status' => $data['status'],
                'classification' => 'confidential',
                'created_by' => $actor->id,
            ]);

            if (StudentAccess::isTeacher($actor)) {
                $student->assignments()->create([
                    'teacher_id' => $actor->id,
                    'school_year' => $student->school_year,
                    'subject' => null,
                    'is_adviser' => true,
                    'can_manage_profile' => true,
                    'can_manage_grades' => true,
                    'can_manage_attendance' => true,
                ]);
            }

            StudentAudit::record(
                $actor,
                $student,
                'student.created',
                Student::class,
                $student,
                ['student_number','name','grade_level','section','school_year','status','user_id'],
                'Student record created.'
            );

            return $student;
        });

        if ($student->user) {
            try {
                $registration->issue($student->user, $actor);
            } catch (\Throwable $exception) {
                report($exception);

                return redirect()->route('admin.students.show', $student)
                    ->with('warning', 'Student record was created, but the portal registration email could not be sent. Use Resend registration email after mail is configured.');
            }
        }

        return redirect()->route('admin.students.show', $student)
            ->with('success', $student->user
                ? 'Student record created. The portal account stays inactive until the strong password and email OTP steps are completed.'
                : 'Student record created.');
    }


    public function resendPortalRegistration(
        Request $request,
        Student $student,
        RegistrationInvitationService $registration
    ): RedirectResponse {
        abort_unless(StudentAccess::canManageProfile($request->user(), $student), 403);

        $student->loadMissing('user');

        if (! $student->user) {
            return back()->withErrors(['registration' => 'This student does not have a portal email account to verify.']);
        }

        if ($student->user->email_verified_at !== null || $student->user->is_active === true) {
            return back()->withErrors(['registration' => 'This student portal account is already verified or active.']);
        }

        try {
            $registration->issue($student->user, $request->user());
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['registration' => 'The registration email could not be sent. Check the school mail configuration and try again.']);
        }

        return back()->with('success', 'A fresh student portal registration invitation was sent to '.$student->user->email.'.');
    }
    public function show(Request $request, Student $student): View
    {
        $actor = $request->user();
        abort_unless(StudentAccess::canViewStudent($actor, $student), 403);

        $student->load([
            'user:id,name,email,is_active,role,email_verified_at',
            'assignments.teacher:id,name,email,role,is_active',
            'grades.teacher:id,name',
            'attendances.recorder:id,name',
            'guardians.user:id,name,email,is_active,role',
        ]);

        $canManageProfile = StudentAccess::canManageProfile($actor, $student);
        $canManageGrades = StudentAccess::canManageAnyGrades($actor, $student);
        $canManageAttendance = StudentAccess::canManageAttendance($actor, $student);
        $canManageFinance = StudentAccess::canManageFinance($actor);
        $canManageAssignments = StudentAccess::canManageAssignments($actor);
        $canManageGuardians = StudentAccess::canManageGuardians($actor);
        $canManageDocuments = StudentAccess::canManageDocuments($actor);

        if ($canManageFinance) {
            $student->load(['financialEntries.recorder:id,name']);
        }

        if ($canManageDocuments) {
            $student->load(['documents.registrar:id,name']);
        }

        if (StudentAccess::isLeadership($actor)) {
            $student->load(['audits.actor:id,name']);
        }

        $teachers = $canManageAssignments
            ? User::query()
                ->where('is_admin', true)
                ->where('role', 'teacher')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id','name','email'])
            : collect();

        return view('admin.students.show', compact(
            'student',
            'teachers',
            'canManageProfile',
            'canManageGrades',
            'canManageAttendance',
            'canManageFinance',
            'canManageAssignments',
            'canManageGuardians',
            'canManageDocuments'
        ));
    }

    public function edit(Request $request, Student $student): View
    {
        abort_unless(StudentAccess::canManageProfile($request->user(), $student), 403);

        return view('admin.students.form', [
            'student' => $student,
            'levels' => AdmissionApplication::LEVELS,
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $actor = $request->user();
        abort_unless(StudentAccess::canManageProfile($actor, $student), 403);

        $data = $request->validate([
            'student_number' => ['required','string','max:64', Rule::unique('students','student_number')->ignore($student->id)],
            'first_name' => ['required','string','max:100'],
            'middle_name' => ['nullable','string','max:100'],
            'last_name' => ['required','string','max:100'],
            'preferred_name' => ['nullable','string','max:100'],
            'date_of_birth' => ['nullable','date','before_or_equal:today'],
            'gender' => ['nullable','string','max:32'],
            'phone' => ['nullable','string','max:64'],
            'home_address' => ['nullable','string','max:1000'],
            'grade_level' => ['required', Rule::in(AdmissionApplication::LEVELS)],
            'section' => ['nullable','string','max:100'],
            'school_year' => ['required','string','max:32'],
            'status' => ['required', Rule::in(['active','inactive','graduated','withdrawn'])],
        ]);

        $before = $student->only(array_keys($data));
        $student->update($data);
        $changed = array_keys(array_filter(
            $student->only(array_keys($data)),
            fn (mixed $value, string $key): bool => ($before[$key] ?? null) != $value,
            ARRAY_FILTER_USE_BOTH
        ));

        StudentAudit::record(
            $actor,
            $student,
            'student.updated',
            Student::class,
            $student,
            $changed,
            'Student profile updated.'
        );

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student profile updated.');
    }
}
