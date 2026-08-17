@extends('admin.layouts.app', ['title' => $student->exists ? 'Edit Student' : 'Register Student'])

@section('content')
<div class="sis-page-head">
    <div>
        <a href="{{ $student->exists ? route('admin.students.show', $student) : route('admin.students.index') }}" class="sis-back">&larr; Student records</a>
        <div class="sis-kicker">Confidential student profile</div>
        <h1>{{ $student->exists ? 'Edit Student' : 'Register Student' }}</h1>
        <p>Structured records stay in the database; confidential documents are not uploaded to the web host.</p>
    </div>
</div>

@if(!$student->exists && \App\Support\StudentAccess::canRequestExistingStudent(auth()->user()))
<section class="sis-panel sis-section">
    <h2>Already registered by another teacher?</h2>
    <p class="sis-help">Do not create a duplicate student. Verify the existing student's number and date of birth, choose the subject you teach, and request access. The student stays hidden until Principal / Super Administrator approval.</p>
    <form method="POST" action="{{ route('admin.students.assignments.request-existing') }}" class="sis-inline-form">
        @csrf
        <input name="student_number" value="{{ old('student_number') }}" placeholder="Existing student number" required maxlength="64">
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
        <input name="subject" value="{{ old('subject') }}" placeholder="Your subject" required maxlength="100">
        <button type="submit">Verify &amp; request assignment</button>
    </form>
</section>
@endif

<form method="POST" action="{{ $student->exists ? route('admin.students.update', $student) : route('admin.students.store') }}" class="sis-panel sis-form">
    @csrf
    @if($student->exists) @method('PATCH') @endif

    <div class="sis-form-grid">
        <label><span>Student number</span><input name="student_number" value="{{ old('student_number', $student->student_number) }}" required maxlength="64"></label>
        <label><span>First name</span><input name="first_name" value="{{ old('first_name', $student->first_name) }}" required maxlength="100"></label>
        <label><span>Middle name</span><input name="middle_name" value="{{ old('middle_name', $student->middle_name) }}" maxlength="100"></label>
        <label><span>Last name</span><input name="last_name" value="{{ old('last_name', $student->last_name) }}" required maxlength="100"></label>
        <label><span>Preferred name</span><input name="preferred_name" value="{{ old('preferred_name', $student->preferred_name) }}" maxlength="100"></label>
        <label><span>Date of birth</span><input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($student->date_of_birth)->format('Y-m-d')) }}"></label>
        <label><span>Gender</span><input name="gender" value="{{ old('gender', $student->gender) }}" maxlength="32"></label>
        <label><span>Phone</span><input name="phone" value="{{ old('phone', $student->phone) }}" maxlength="64"></label>
        <label><span>Grade level</span><select name="grade_level" required>@foreach($levels as $level)<option value="{{ $level }}" @selected(old('grade_level', $student->grade_level) === $level)>{{ $level }}</option>@endforeach</select></label>
        <label><span>Section</span><input name="section" value="{{ old('section', $student->section) }}" maxlength="100"></label>
        <label><span>School year</span><input name="school_year" value="{{ old('school_year', $student->school_year) }}" placeholder="2026-2027" required maxlength="32"></label>
        <label><span>Status</span><select name="status" required>@foreach(['active','inactive','graduated','withdrawn'] as $status)<option value="{{ $status }}" @selected(old('status', $student->status ?: 'active') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
    </div>

    <label><span>Home address</span><textarea name="home_address" rows="3" maxlength="1000">{{ old('home_address', $student->home_address) }}</textarea></label>

    @unless($student->exists)
    <fieldset class="sis-fieldset">
        <legend>Student portal account</legend>
        <p>When a registered student email is supplied, the portal account stays inactive until the student creates a strong password and verifies a 6-digit OTP sent to that email.</p>
        <div class="sis-form-grid">
            <label><span>Registered student email</span><input type="email" name="student_email" value="{{ old('student_email') }}" maxlength="150" autocomplete="email"><small>If a school email domain is configured, this address must use it.</small></label>
        </div>
        <p class="sis-help"><strong>Password security:</strong> 12 to 128 characters, uppercase and lowercase letters, number, symbol, confirmation, and no student name/email/student number.</p>
    </fieldset>
    @endunless

    <button type="submit" class="sis-primary">{{ $student->exists ? 'Save student profile' : 'Register student' }}</button>
</form>
@endsection
