@extends('admin.layouts.app', ['title' => $student->fullName()])

@section('content')
<div class="sis-page-head">
    <div>
        <a href="{{ route('admin.students.index') }}" class="sis-back">&larr; Student records</a>
        <div class="sis-kicker">CONFIDENTIAL STUDENT RECORD</div>
        <h1>{{ $student->fullName() }}</h1>
        <p>{{ $student->student_number }} · {{ $student->grade_level }}{{ $student->section ? ' · '.$student->section : '' }} · {{ $student->school_year }}</p>
    </div>
    @if($canManageProfile)
        <a href="{{ route('admin.students.edit', $student) }}" class="sis-secondary sis-link-button">Edit profile</a>
    @endif
</div>

<div class="sis-inline-form" style="margin-bottom:18px">
    <a href="{{ route('admin.students.report-card', $student) }}" target="_blank" rel="noopener" class="sis-secondary sis-link-button">Report Card</a>
    <a href="{{ route('admin.students.transcript', $student) }}" target="_blank" rel="noopener" class="sis-secondary sis-link-button">Transcript / TOR Draft</a>
    @if(\App\Support\StudentAccess::isLeadership(auth()->user()))
        <a href="{{ route('admin.students.transcript', [$student, 'official' => 1]) }}" target="_blank" rel="noopener" class="sis-primary sis-link-button">Official TOR Print View</a>
    @endif
</div>

<div class="sis-summary-grid">
    <article class="sis-panel">
        <small>Portal email</small>
        <strong>{{ $student->user?->email ?: 'Not created' }}</strong>
        @if($student->user && !$student->user->email_verified_at)
            <span class="p12-badge p12-badge--warn">Registration / email OTP pending</span>
            @if($canManageProfile)
                <form method="POST" action="{{ route('admin.students.resend-registration', $student) }}" style="margin-top:10px">
                    @csrf
                    <button type="submit" class="sis-secondary">Resend registration email</button>
                </form>
            @endif
        @elseif($student->user?->email_verified_at)
            <span class="p12-badge p12-badge--good">Email verified</span>
        @endif
    </article>
    <article class="sis-panel"><small>Status</small><strong>{{ ucfirst($student->status) }}</strong></article>
    @if($canManageFinance)
        <article class="sis-panel"><small>Current balance</small><strong>₱{{ number_format($student->balance(), 2) }}</strong></article>
    @else
        <article class="sis-panel"><small>Financial access</small><strong>Restricted to leadership</strong></article>
    @endif
</div>

<section class="sis-panel sis-section">
    <h2>Student Profile</h2>

    <div style="display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap;margin-bottom:16px">
        <div style="width:128px">
            @if($student->profile_photo_path)
                <img src="{{ route('admin.students.photo', $student) }}" alt="{{ $student->fullName() }} profile photo" style="width:128px;height:128px;object-fit:cover;border-radius:16px;border:1px solid #d9dee7">
            @else
                <div style="width:128px;height:128px;border-radius:16px;border:1px dashed #b8c1cf;display:grid;place-items:center;text-align:center;padding:10px;color:#687386">No private profile photo</div>
            @endif
        </div>

        @if($canManageProfile)
            <div style="flex:1;min-width:240px">
                <form method="POST" enctype="multipart/form-data" action="{{ route('admin.students.photo.store', $student) }}" class="sis-inline-form">
                    @csrf
                    <input type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" required>
                    <button type="submit">Upload private profile photo</button>
                </form>
                <p class="sis-help">JPG/PNG/WebP · maximum 1 MB · minimum 400 x 400 px. Stored outside public web storage and served only after authorization.</p>
                @if($student->profile_photo_path)
                    <form method="POST" action="{{ route('admin.students.photo.destroy', $student) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="sis-secondary">Remove profile photo</button>
                    </form>
                @endif
            </div>
        @endif
    </div>

    <div class="sis-summary-grid">
        <article><small>Date of birth</small><strong>{{ optional($student->date_of_birth)->format('M j, Y') ?: 'Not recorded' }}</strong></article>
        <article><small>Gender</small><strong>{{ $student->gender ?: 'Not recorded' }}</strong></article>
        <article><small>Phone</small><strong>{{ $student->phone ?: 'Not recorded' }}</strong></article>
    </div>
    <p><strong>Home address:</strong> {{ $student->home_address ?: 'Not recorded' }}</p>
</section>

<section class="sis-panel sis-section">
    <h2>Academic Grades &amp; Exam Results</h2>
    @if($canManageGrades)
    <form method="POST" action="{{ route('admin.students.grades.store', $student) }}" class="sis-inline-form">
        @csrf
        <input name="subject" placeholder="Subject" required maxlength="100">
        <select name="term" required>
            @foreach(['Q1','Q2','Q3','Q4','Final'] as $term)<option>{{ $term }}</option>@endforeach
        </select>
        <select name="category" required>
            @foreach(['written_work','performance_task','quiz','exam','project','final_grade','other'] as $category)
                <option value="{{ $category }}">{{ str_replace('_', ' ', ucfirst($category)) }}</option>
            @endforeach
        </select>
        <input name="assessment_name" placeholder="Assessment / exam name" required maxlength="160">
        <input type="number" step="0.01" min="0" name="score" placeholder="Score">
        <input type="number" step="0.01" min="0.01" name="max_score" placeholder="Max">
        <input type="number" step="0.01" min="0" max="100" name="grade_percentage" placeholder="Grade %">
        <input type="date" name="assessment_date">
        <button type="submit">Record</button>
    </form>
    @else
        <p class="sis-help">This account can view the assigned student but cannot add grade records.</p>
    @endif

    <div class="sis-table-wrap">
        <table class="sis-table">
            <thead><tr><th>Date</th><th>Subject</th><th>Term</th><th>Assessment</th><th>Result</th><th>Teacher</th></tr></thead>
            <tbody>
            @forelse($student->grades->sortByDesc(fn($grade) => $grade->assessment_date?->timestamp ?? $grade->id) as $grade)
                <tr>
                    <td>{{ optional($grade->assessment_date)->format('M j, Y') ?: '—' }}</td>
                    <td>{{ $grade->subject }}</td>
                    <td>{{ $grade->term }}</td>
                    <td>{{ $grade->assessment_name }}</td>
                    <td>{{ $grade->grade_percentage !== null ? number_format((float) $grade->grade_percentage, 2).'%' : ($grade->score !== null ? $grade->score.($grade->max_score !== null ? ' / '.$grade->max_score : '') : '—') }}</td>
                    <td>{{ $grade->teacher?->name ?: 'School record' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No academic entries yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="sis-panel sis-section">
    <h2>Attendance</h2>
    @if($canManageAttendance)
    <form method="POST" action="{{ route('admin.students.attendance.store', $student) }}" class="sis-inline-form">
        @csrf
        <input type="date" name="attendance_date" required>
        <select name="status" required>
            @foreach(['present','absent','late','excused'] as $status)<option value="{{ $status }}">{{ ucfirst($status) }}</option>@endforeach
        </select>
        <input name="remarks" placeholder="Remarks" maxlength="1000">
        <button type="submit">Save attendance</button>
    </form>
    @else
        <p class="sis-help">Attendance entry is not enabled for this teacher assignment.</p>
    @endif

    <div class="sis-table-wrap">
        <table class="sis-table">
            <thead><tr><th>Date</th><th>Status</th><th>Remarks</th><th>Recorded by</th></tr></thead>
            <tbody>
            @forelse($student->attendances->sortByDesc('attendance_date') as $attendance)
                <tr>
                    <td>{{ $attendance->attendance_date->format('M j, Y') }}</td>
                    <td>{{ ucfirst($attendance->status) }}</td>
                    <td>{{ $attendance->remarks ?: '—' }}</td>
                    <td>{{ $attendance->recorder?->name ?: 'School record' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No attendance entries yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

@if($canManageAssignments)
<section class="sis-panel sis-section">
    <h2>Teacher Assignments</h2>
    <form method="POST" action="{{ route('admin.students.assignments.store', $student) }}" class="sis-inline-form">
        @csrf
        <select name="teacher_id" required>
            <option value="">Select teacher</option>
            @foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }} · {{ $teacher->email }}</option>@endforeach
        </select>
        <input name="subject" placeholder="Subject (blank = broad assignment)" maxlength="100">
        <label class="sis-check"><input type="checkbox" name="is_adviser" value="1"><span>Adviser</span></label>
        <label class="sis-check"><input type="checkbox" name="can_manage_profile" value="1"><span>Profile</span></label>
        <label class="sis-check"><input type="checkbox" name="can_manage_grades" value="1" checked><span>Grades</span></label>
        <label class="sis-check"><input type="checkbox" name="can_manage_attendance" value="1" checked><span>Attendance</span></label>
        <button type="submit">Assign teacher</button>
    </form>

    <ul class="sis-list">
        @forelse($student->assignments as $assignment)
            <li>
                <strong>{{ $assignment->teacher?->name ?: 'Removed teacher' }}</strong>
                <span>
                    {{ $assignment->subject ?: 'All assigned subjects' }}
                    {{ $assignment->is_adviser ? ' · Adviser' : '' }}
                    · {{ ucfirst($assignment->status ?? 'active') }}
                </span>
                @if(($assignment->status ?? 'active') === 'pending')
                    <div class="sis-inline-form" style="margin-top:8px">
                        <form method="POST" action="{{ route('admin.students.assignments.approve', [$student, $assignment]) }}">
                            @csrf @method('PATCH')
                            <button type="submit">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.students.assignments.reject', [$student, $assignment]) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="sis-secondary">Reject</button>
                        </form>
                    </div>
                @endif
            </li>
        @empty
            <li>No teacher assignments yet.</li>
        @endforelse
    </ul>
</section>
@endif

@if($canManageGuardians)
<section class="sis-panel sis-section">
    <h2>Parent / Guardian Accounts</h2>
    <form method="POST" action="{{ route('admin.students.guardians.store', $student) }}" class="sis-inline-form">
        @csrf
        <input name="guardian_name" placeholder="Parent / guardian name" required maxlength="120">
        <input type="email" name="guardian_email" placeholder="Email" required maxlength="150">
        <input name="relationship" placeholder="Relationship" required maxlength="64">
        <input type="password" name="temporary_password" placeholder="Temporary password if new">
        <input type="password" name="temporary_password_confirmation" placeholder="Confirm temporary password">
        <label class="sis-check"><input type="checkbox" name="is_primary" value="1"><span>Primary</span></label>
        <label class="sis-check"><input type="checkbox" name="can_view_finance" value="1" checked><span>May view balance</span></label>
        <button type="submit">Save guardian access</button>
    </form>

    <ul class="sis-list">
        @forelse($student->guardians as $guardian)
            <li>
                <strong>{{ $guardian->user?->name ?: 'Removed account' }}</strong>
                <span>{{ $guardian->relationship }} · {{ $guardian->user?->email }}</span>
            </li>
        @empty
            <li>No parent or guardian portal account is linked yet.</li>
        @endforelse
    </ul>
</section>
@endif

@if($canManageFinance)
<section class="sis-panel sis-section">
    <h2>Highly Confidential Financial Ledger</h2>
    <form method="POST" action="{{ route('admin.students.finance.store', $student) }}" class="sis-inline-form">
        @csrf
        <select name="entry_type" required>
            @foreach(['charge','payment','credit','adjustment'] as $type)<option value="{{ $type }}">{{ ucfirst($type) }}</option>@endforeach
        </select>
        <input name="description" placeholder="Description" required maxlength="180">
        <input type="number" step="0.01" min="0.01" name="amount" placeholder="Amount" required>
        <input name="reference_number" placeholder="Receipt / reference" maxlength="100">
        <input type="date" name="entry_date" required>
        <input type="date" name="due_date">
        <button type="submit">Record financial entry</button>
    </form>

    <div class="sis-table-wrap">
        <table class="sis-table">
            <thead><tr><th>Date</th><th>Type</th><th>Description</th><th>Amount</th><th>Reference</th></tr></thead>
            <tbody>
            @forelse($student->financialEntries->sortByDesc('entry_date') as $entry)
                <tr>
                    <td>{{ $entry->entry_date->format('M j, Y') }}</td>
                    <td>{{ ucfirst($entry->entry_type) }}</td>
                    <td>{{ $entry->description }}</td>
                    <td>₱{{ number_format((float) $entry->amount, 2) }}</td>
                    <td>{{ $entry->reference_number ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No financial entries yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endif

@if($canManageDocuments)
<section class="sis-panel sis-section">
    <h2>Highly Confidential External Documents</h2>
    <p class="sis-help">No file is stored on the website host. Register only the identifier of a document already stored in the approved private Google-managed storage.</p>

    <form method="POST" action="{{ route('admin.students.documents.store', $student) }}" class="sis-inline-form">
        @csrf
        <input name="document_type" placeholder="Document type" required maxlength="80">
        <select name="provider" required>
            <option value="google_drive">Google Drive / Shared Drive</option>
            <option value="google_cloud_storage">Google Cloud Storage</option>
            <option value="other_external">Other approved external storage</option>
        </select>
        <input name="external_id" placeholder="Private external file ID (not a public URL)" required maxlength="512">
        <input name="display_name" placeholder="Display name" required maxlength="255">
        <select name="classification" required>
            <option value="highly_confidential">Highly Confidential</option>
            <option value="confidential">Confidential</option>
        </select>
        <button type="submit">Register external document</button>
    </form>

    <ul class="sis-list">
        @forelse($student->documents as $document)
            <li>
                <strong>{{ $document->display_name }}</strong>
                <span>{{ $document->document_type }} · {{ $document->provider }} · {{ str_replace('_', ' ', ucfirst($document->classification)) }}</span>
            </li>
        @empty
            <li>No external document references registered.</li>
        @endforelse
    </ul>
</section>
@endif

@if(\App\Support\StudentAccess::isLeadership(auth()->user()))
<section class="sis-panel sis-section">
    <h2>Audit History</h2>
    <ul class="sis-list">
        @forelse($student->audits->sortByDesc('created_at')->take(50) as $audit)
            <li>
                <strong>{{ $audit->summary }}</strong>
                <span>{{ $audit->actor?->name ?: 'System' }} · {{ $audit->created_at->format('M j, Y g:i A') }}</span>
            </li>
        @empty
            <li>No student-record audit entries yet.</li>
        @endforelse
    </ul>
</section>
@endif

<p class="sis-classification">CONFIDENTIAL · Student records are server-authorized. Teachers cannot access unrelated students, and financial/document controls remain leadership-only.</p>
@endsection
