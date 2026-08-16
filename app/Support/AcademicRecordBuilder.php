<?php

namespace App\Support;

use App\Models\Student;
use Illuminate\Support\Collection;

final class AcademicRecordBuilder
{
    private const TERMS = ['Q1', 'Q2', 'Q3', 'Q4', 'Final'];

    public static function reportCard(Student $student): array
    {
        $grades = $student->grades
            ->filter(fn ($grade): bool => $grade->grade_percentage !== null);

        $subjects = $grades
            ->groupBy(fn ($grade): string => trim((string) $grade->subject))
            ->sortKeys()
            ->map(function (Collection $subjectGrades, string $subject): array {
                $terms = [];

                foreach (self::TERMS as $term) {
                    $termGrades = $subjectGrades->where('term', $term);
                    $terms[$term] = self::termPercentage($termGrades);
                }

                $quarterValues = collect(['Q1', 'Q2', 'Q3', 'Q4'])
                    ->map(fn (string $term) => $terms[$term])
                    ->filter(fn ($value): bool => $value !== null);

                $final = $terms['Final'];

                if ($final === null && $quarterValues->isNotEmpty()) {
                    $final = round((float) $quarterValues->avg(), 2);
                }

                return [
                    'subject' => $subject,
                    'terms' => $terms,
                    'final' => $final,
                ];
            })
            ->values()
            ->all();

        $attendance = $student->attendances
            ->countBy(fn ($record): string => (string) $record->status)
            ->map(fn (int $count): int => $count)
            ->all();

        return [
            'subjects' => $subjects,
            'attendance' => [
                'present' => $attendance['present'] ?? 0,
                'absent' => $attendance['absent'] ?? 0,
                'late' => $attendance['late'] ?? 0,
                'excused' => $attendance['excused'] ?? 0,
            ],
        ];
    }

    public static function transcript(Student $student): array
    {
        return $student->grades
            ->filter(fn ($grade): bool => $grade->grade_percentage !== null)
            ->groupBy(fn ($grade): string => (string) $grade->school_year)
            ->sortKeys()
            ->map(function (Collection $yearGrades, string $schoolYear): array {
                $subjects = $yearGrades
                    ->groupBy(fn ($grade): string => trim((string) $grade->subject))
                    ->sortKeys()
                    ->map(function (Collection $subjectGrades, string $subject): array {
                        $finalRecords = $subjectGrades
                            ->where('term', 'Final')
                            ->where('category', 'final_grade');

                        $percentage = self::termPercentage($finalRecords);

                        if ($percentage === null) {
                            $quarterRecords = $subjectGrades
                                ->whereIn('term', ['Q1', 'Q2', 'Q3', 'Q4']);

                            $quarterAverages = collect(['Q1', 'Q2', 'Q3', 'Q4'])
                                ->map(fn (string $term) => self::termPercentage($quarterRecords->where('term', $term)))
                                ->filter(fn ($value): bool => $value !== null);

                            $percentage = $quarterAverages->isNotEmpty()
                                ? round((float) $quarterAverages->avg(), 2)
                                : self::termPercentage($subjectGrades);
                        }

                        return [
                            'subject' => $subject,
                            'final' => $percentage,
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'school_year' => $schoolYear,
                    'subjects' => $subjects,
                ];
            })
            ->values()
            ->all();
    }

    private static function termPercentage(Collection $grades): ?float
    {
        $withPercentage = $grades
            ->filter(fn ($grade): bool => $grade->grade_percentage !== null);

        if ($withPercentage->isEmpty()) {
            return null;
        }

        $final = $withPercentage
            ->where('category', 'final_grade')
            ->sortByDesc('id')
            ->first();

        if ($final) {
            return round((float) $final->grade_percentage, 2);
        }

        return round((float) $withPercentage->avg('grade_percentage'), 2);
    }
}
