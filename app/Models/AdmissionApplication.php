<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdmissionApplication extends Model
{
    use HasFactory;

    public const STATUSES = [
        'submitted' => 'Submitted',
        'under_review' => 'Under review',
        'awaiting_documents' => 'Awaiting documents',
        'assessment_scheduled' => 'Assessment scheduled',
        'accepted' => 'Accepted',
        'waitlisted' => 'Waitlisted',
        'declined' => 'Declined',
        'enrolled' => 'Enrolled',
        'withdrawn' => 'Withdrawn',
    ];

    public const LEVELS = [
        'Nursery','Pre-Kindergarten','Kindergarten','Grade 1','Grade 2','Grade 3',
        'Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9','Grade 10',
    ];

    public const DEFAULT_CHECKLIST = [
        'application_received' => 'Application Received',
        'initial_review' => 'Initial Review',
        'birth_certificate' => 'Birth Certificate Requested / Reviewed',
        'previous_school_record' => 'Previous School Record Requested / Reviewed',
        'assessment_scheduled' => 'Assessment Scheduled',
        'assessment_completed' => 'Assessment Completed',
        'interview_completed' => 'Interview Completed',
        'requirements_complete' => 'Requirements Complete',
        'decision_recorded' => 'Decision Recorded',
        'enrollment_completed' => 'Enrollment Completed',
    ];

    protected $fillable = [
        'reference_code','access_code_hash','guardian_name','guardian_email',
        'guardian_phone','student_name','date_of_birth','applying_for_level',
        'school_year','previous_school','home_address','family_notes','status',
        'public_status_message','admin_notes','privacy_consent_at',
        'application_consent_at','submitted_at','last_viewed_at',
        'access_code_rotated_at','ip_hash','user_agent',
    ];

    protected $hidden = ['access_code_hash','admin_notes','ip_hash','user_agent'];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'privacy_consent_at' => 'datetime',
            'application_consent_at' => 'datetime',
            'submitted_at' => 'datetime',
            'last_viewed_at' => 'datetime',
            'access_code_rotated_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'reference_code';
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AdmissionDocument::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(AdmissionEvent::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(AdmissionChecklistItem::class)->orderBy('sort_order');
    }

    public function ensureDefaultChecklist(): void
    {
        foreach (self::DEFAULT_CHECKLIST as $key => $label) {
            $index = array_search($key, array_keys(self::DEFAULT_CHECKLIST), true);

            $this->checklistItems()->firstOrCreate(
                ['item_key' => $key],
                [
                    'label' => $label,
                    'is_required' => true,
                    'is_completed' => false,
                    'sort_order' => is_int($index) ? $index : 0,
                ]
            );
        }
    }

    public function checklistProgress(): array
    {
        $total = $this->checklistItems()->count();
        $completed = $this->checklistItems()->where('is_completed', true)->count();

        return compact('total', 'completed');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $search) use ($term): void {
            $search
                ->where('reference_code', 'like', "%{$term}%")
                ->orWhere('guardian_name', 'like', "%{$term}%")
                ->orWhere('guardian_email', 'like', "%{$term}%")
                ->orWhere('guardian_phone', 'like', "%{$term}%")
                ->orWhere('student_name', 'like', "%{$term}%");
        });
    }

    public function verifyAccessCode(string $accessCode): bool
    {
        return Hash::check(self::normalizeAccessCode($accessCode), $this->access_code_hash);
    }

    public function replaceAccessCode(string $accessCode): void
    {
        $this->forceFill([
            'access_code_hash' => Hash::make(self::normalizeAccessCode($accessCode)),
            'access_code_rotated_at' => now(),
        ])->save();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? Str::headline($this->status);
    }

    public static function createReferenceCode(): string
    {
        do {
            $reference = 'NACS-'.now()->format('Y').'-'.Str::upper(Str::random(8));
        } while (self::query()->where('reference_code', $reference)->exists());

        return $reference;
    }

    public static function createAccessCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $plain = '';

        for ($index = 0; $index < 12; $index++) {
            $plain .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return implode('-', str_split($plain, 4));
    }

    public static function normalizeAccessCode(string $accessCode): string
    {
        return Str::upper((string) preg_replace('/[^A-Za-z0-9]/', '', $accessCode));
    }
}
