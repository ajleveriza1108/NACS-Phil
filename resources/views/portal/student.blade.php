@extends('portal.layout', ['title' => $student->fullName()])

@section('content')
<div class="sis-page-head">
    <div>
        <a href="{{ route('portal.dashboard') }}" class="sis-back">&larr; Portal home</a>
        <div class="sis-kicker">Confidential student record</div>
        <h1>{{ $student->fullName() }}</h1>
        <p>{{ $student->student_number }} · {{ $student->grade_level }}{{ $student->section ? ' · '.$student->section : '' }} · {{ $student->school_year }}</p>
    </div>
</div>

<div class="sis-summary-grid">
    <article class="sis-panel"><small>Status</small><strong>{{ ucfirst($student->status) }}</strong></article>
    <article class="sis-panel"><small>Grade / Section</small><strong>{{ $student->grade_level }}{{ $student->section ? ' · '.$student->section : '' }}</strong></article>
    @if($canViewFinance)
        <article class="sis-panel"><small>Current balance</small><strong>₱{{ number_format($student->balance(), 2) }}</strong></article>
    @endif
</div>

<section class="sis-panel sis-section">
    <h2>Student Profile</h2>
    <div class="sis-summary-grid">
        <article><small>Date of birth</small><strong>{{ optional($student->date_of_birth)->format('M j, Y') ?: 'Not recorded' }}</strong></article>
        <article><small>Phone</small><strong>{{ $student->phone ?: 'Not recorded' }}</strong></article>
        <article><small>School year</small><strong>{{ $student->school_year }}</strong></article>
    </div>
    <p><strong>Home address:</strong> {{ $student->home_address ?: 'Not recorded' }}</p>
</section>

<section class="sis-panel sis-section">
    <h2>Grades &amp; Exam Results</h2>
    <div class="sis-table-wrap">
        <table class="sis-table">
            <thead><tr><th>Date</th><th>Subject</th><th>Term</th><th>Assessment</th><th>Result</th></tr></thead>
            <tbody>
            @forelse($student->grades as $grade)
                <tr>
                    <td>{{ optional($grade->assessment_date)->format('M j, Y') ?: '—' }}</td>
                    <td>{{ $grade->subject }}</td>
                    <td>{{ $grade->term }}</td>
                    <td>{{ $grade->assessment_name }}</td>
                    <td>
                        @if($grade->grade_percentage !== null)
                            {{ number_format((float) $grade->grade_percentage, 2) }}%
                        @elseif($grade->score !== null)
                            {{ $grade->score }}{{ $grade->max_score !== null ? ' / '.$grade->max_score : '' }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No grade or exam results have been published to this record yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="sis-panel sis-section">
    <h2>Attendance</h2>
    <div class="sis-table-wrap">
        <table class="sis-table">
            <thead><tr><th>Date</th><th>Status</th><th>Remarks</th></tr></thead>
            <tbody>
            @forelse($student->attendances as $attendance)
                <tr>
                    <td>{{ $attendance->attendance_date->format('M j, Y') }}</td>
                    <td>{{ ucfirst($attendance->status) }}</td>
                    <td>{{ $attendance->remarks ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3">No attendance entries are available yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

@if($canViewFinance)
<section class="sis-panel sis-section">
    <h2>Account Balance &amp; Payment History</h2>
    <div class="sis-table-wrap">
        <table class="sis-table">
            <thead><tr><th>Date</th><th>Description</th><th>Type</th><th>Amount</th></tr></thead>
            <tbody>
            @forelse($student->financialEntries as $entry)
                <tr>
                    <td>{{ $entry->entry_date->format('M j, Y') }}</td>
                    <td>{{ $entry->description }}</td>
                    <td>{{ ucfirst($entry->entry_type) }}</td>
                    <td>₱{{ number_format((float) $entry->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No financial entries are available for this school year.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endif

<p class="sis-classification">CONFIDENTIAL · This record is private to authorized NACS-Phil users. Do not share screenshots or access credentials.</p>
@endsection
