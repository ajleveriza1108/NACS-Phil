@extends('admin.layouts.app', ['title' => 'Student Records'])

@section('content')
<div class="sis-page-head">
    <div>
        <div class="sis-kicker">Phase 41 · Confidential SIS</div>
        <h1>Student Records</h1>
        <p>Teachers see only assigned students. Leadership accounts can see the complete school roster.</p>
    </div>
    <a href="{{ route('admin.students.create') }}" class="sis-primary sis-link-button">Register student</a>
</div>

<form method="GET" class="sis-search">
    <label>
        <span class="sis-sr-only">Search students</span>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Student number, name, grade or section">
    </label>
    <button type="submit">Search</button>
</form>

<div class="sis-card-grid">
    @forelse($students as $student)
        <a class="sis-student-card" href="{{ route('admin.students.show', $student) }}">
            <span class="sis-badge">{{ $student->grade_level }}{{ $student->section ? ' · '.$student->section : '' }}</span>
            <strong>{{ $student->fullName() }}</strong>
            <small>{{ $student->student_number }} · {{ $student->school_year }}</small>
            <span>{{ $student->user?->email ?: 'Portal account not yet created' }}</span>
        </a>
    @empty
        <div class="sis-empty">No accessible student records match this search.</div>
    @endforelse
</div>

<div class="sis-pagination">{{ $students->links() }}</div>
@endsection
