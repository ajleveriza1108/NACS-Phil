@extends('portal.layout', ['title' => 'My Students'])

@section('content')
<div class="sis-page-head">
    <div>
        <div class="sis-kicker">Private record access</div>
        <h1>{{ auth()->user()->role === 'student' ? 'My Student Record' : 'My Children' }}</h1>
        <p>Only records connected to this account are shown.</p>
    </div>
</div>

<div class="sis-card-grid">
    @forelse($students as $student)
        <a class="sis-student-card" href="{{ route('portal.students.show', $student) }}">
            <span class="sis-badge">{{ $student->grade_level }}{{ $student->section ? ' · '.$student->section : '' }}</span>
            <strong>{{ $student->fullName() }}</strong>
            <small>Student No. {{ $student->student_number }}</small>
            <span>Open private record &rarr;</span>
        </a>
    @empty
        <div class="sis-empty">
            No student record is currently linked to this account. Contact the school office if you believe this is incorrect.
        </div>
    @endforelse
</div>
@endsection
