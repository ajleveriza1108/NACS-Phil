<?php

namespace App\Support;

use App\Models\Student;
use App\Models\StudentRecordAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StudentAudit
{
    public static function record(
        User $actor,
        Student $student,
        string $action,
        string $recordType,
        Model|int|null $record,
        array $changedFields,
        string $summary
    ): void {
        StudentRecordAudit::create([
            'student_id' => $student->id,
            'actor_user_id' => $actor->id,
            'action' => $action,
            'record_type' => $recordType,
            'record_id' => $record instanceof Model ? $record->getKey() : $record,
            'changed_fields' => array_values(array_unique($changedFields)),
            'summary' => $summary,
        ]);
    }
}
