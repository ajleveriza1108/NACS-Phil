<?php

namespace App\Support;

use App\Models\Student;
use App\Models\StudentTeacherAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class StudentAccess
{
    public static function isLeadership(User $user): bool
    {
        return $user->is_active !== false
            && $user->is_admin === true
            && in_array($user->staffRole(), ['super_admin', 'principal'], true);
    }

    public static function isTeacher(User $user): bool
    {
        return $user->is_active !== false
            && $user->is_admin === true
            && $user->isTeacher();
    }

    public static function isPortalStudent(User $user): bool
    {
        return $user->is_active !== false
            && $user->is_admin !== true
            && $user->role === 'student';
    }

    public static function isPortalParent(User $user): bool
    {
        return $user->is_active !== false
            && $user->is_admin !== true
            && $user->role === 'parent';
    }

    public static function visibleStudentQuery(User $user): Builder
    {
        $query = Student::query();

        if (self::isLeadership($user)) {
            return $query;
        }

        if (self::isTeacher($user)) {
            return $query->whereHas('assignments', fn (Builder $assignment): Builder =>
                $assignment
                    ->where('teacher_id', $user->id)
                    ->where('status', 'active')
            );
        }

        if (self::isPortalStudent($user)) {
            return $query->where('user_id', $user->id);
        }

        if (self::isPortalParent($user)) {
            return $query->whereHas('guardians', fn (Builder $guardian): Builder =>
                $guardian->where('user_id', $user->id)
            );
        }

        return $query->whereRaw('1 = 0');
    }

    public static function canViewStudent(User $user, Student $student): bool
    {
        if (self::isLeadership($user)) {
            return true;
        }

        if (self::isTeacher($user)) {
            return $student->assignments()
                ->where('teacher_id', $user->id)
                ->where('status', 'active')
                ->exists();
        }

        if (self::isPortalStudent($user)) {
            return $student->user_id === $user->id;
        }

        if (self::isPortalParent($user)) {
            return $student->guardians()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public static function canCreateStudent(User $user): bool
    {
        return self::isLeadership($user) || self::isTeacher($user);
    }

    public static function canRequestExistingStudent(User $user): bool
    {
        return self::isTeacher($user);
    }

    public static function canManageProfile(User $user, Student $student): bool
    {
        if (self::isLeadership($user)) {
            return true;
        }

        return self::isTeacher($user)
            && $student->assignments()
                ->where('teacher_id', $user->id)
                ->where('status', 'active')
                ->where('can_manage_profile', true)
                ->exists();
    }

    public static function canManageAnyGrades(User $user, Student $student): bool
    {
        if (self::isLeadership($user)) {
            return true;
        }

        return self::isTeacher($user)
            && $student->assignments()
                ->where('teacher_id', $user->id)
                ->where('status', 'active')
                ->where('can_manage_grades', true)
                ->exists();
    }

    public static function canManageGrades(User $user, Student $student, string $subject): bool
    {
        if (self::isLeadership($user)) {
            return true;
        }

        if (! self::isTeacher($user)) {
            return false;
        }

        $subject = Str::lower(trim($subject));

        return $student->assignments()
            ->where('teacher_id', $user->id)
            ->where('status', 'active')
            ->where('can_manage_grades', true)
            ->where(function (Builder $query) use ($subject): void {
                $query->whereNull('subject')
                    ->orWhereRaw('lower(subject) = ?', [$subject]);
            })
            ->exists();
    }

    public static function canManageAttendance(User $user, Student $student): bool
    {
        if (self::isLeadership($user)) {
            return true;
        }

        return self::isTeacher($user)
            && $student->assignments()
                ->where('teacher_id', $user->id)
                ->where('status', 'active')
                ->where('can_manage_attendance', true)
                ->exists();
    }

    public static function canManageFinance(User $user): bool
    {
        return self::isLeadership($user);
    }

    public static function canViewFinance(User $user, Student $student): bool
    {
        if (self::isLeadership($user)) {
            return true;
        }

        if (self::isPortalStudent($user)) {
            return $student->user_id === $user->id;
        }

        if (self::isPortalParent($user)) {
            return $student->guardians()
                ->where('user_id', $user->id)
                ->where('can_view_finance', true)
                ->exists();
        }

        return false;
    }

    public static function canManageAssignments(User $user): bool
    {
        return self::isLeadership($user);
    }

    public static function canManageGuardians(User $user): bool
    {
        return self::isLeadership($user);
    }

    public static function canManageDocuments(User $user): bool
    {
        return self::isLeadership($user);
    }

    public static function teacherAssignment(User $teacher, Student $student): ?StudentTeacherAssignment
    {
        if (! self::isTeacher($teacher)) {
            return null;
        }

        return $student->assignments()
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->first();
    }
}
