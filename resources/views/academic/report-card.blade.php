<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Report Card - {{ $student->fullName() }}</title>
<style>
@page{size:A4 portrait;margin:12mm}
*{box-sizing:border-box}
body{font-family:Arial,Helvetica,sans-serif;margin:0;background:#eef2f6;color:#17233c}
.sheet{max-width:900px;margin:24px auto;background:#fff;border:1px solid #d7dde5;border-radius:16px;padding:28px;box-shadow:0 14px 40px rgba(23,35,60,.10)}
.head{display:grid;grid-template-columns:auto 1fr auto;gap:18px;align-items:center;border-bottom:3px solid #b89242;padding-bottom:18px}
.logo{width:64px;height:64px;object-fit:contain}
.school h1{font-size:20px;margin:0}.school p{margin:4px 0 0;font-size:12px}
.badge{font-size:11px;font-weight:700;padding:7px 10px;border:1px solid #b89242;border-radius:999px}
.student{display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;margin:20px 0}
.meta{border:1px solid #e1e5eb;border-radius:10px;padding:10px}.meta small{display:block;color:#667085}.meta strong{display:block;margin-top:3px}
table{width:100%;border-collapse:collapse;font-size:12px}th,td{border:1px solid #d8dee7;padding:8px;text-align:center}th{background:#102a4c;color:#fff}td:first-child{text-align:left;font-weight:700}
.attendance{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin:16px 0}.attendance div{border:1px solid #e1e5eb;border-radius:9px;padding:10px;text-align:center}.attendance strong{display:block;font-size:18px}
.signatures{display:grid;grid-template-columns:1fr 1fr;gap:70px;margin-top:54px}.line{border-top:1px solid #333;text-align:center;padding-top:6px;font-size:11px}
.note{margin-top:20px;font-size:11px;color:#667085}.actions{max-width:900px;margin:18px auto;display:flex;gap:10px;flex-wrap:wrap}.actions button,.actions a{padding:10px 15px;border:0;border-radius:9px;background:#102a4c;color:#fff;font-weight:700;cursor:pointer;text-decoration:none}
.actions a{background:#8a6925}
@media print{body{background:#fff}.sheet{margin:0;box-shadow:none;border:0;border-radius:0;padding:0}.actions{display:none}}
</style>
</head>
<body>
@php($pdfRoute = request()->routeIs('admin.*') ? 'admin.students.report-card.pdf' : 'portal.students.report-card.pdf')
<div class="actions">
<a href="{{ route($pdfRoute, $student) }}">Download A4 PDF</a>
<button type="button" onclick="window.print()">Print View</button>
</div>
<main class="sheet">
<header class="head">
<img class="logo" src="{{ \App\Models\SchoolSetting::logoUrl() }}" alt="">
<div class="school"><h1>{{ $branding['school_name'] }}</h1><p>Student Report Card · {{ $student->school_year }}</p></div>
<span class="badge">ACADEMIC RECORD</span>
</header>
<section class="student">
<div class="meta"><small>Student</small><strong>{{ $student->fullName() }}</strong></div>
<div class="meta"><small>Student No.</small><strong>{{ $student->student_number }}</strong></div>
<div class="meta"><small>Grade / Section</small><strong>{{ $student->grade_level }}{{ $student->section ? ' · '.$student->section : '' }}</strong></div>
</section>
<table>
<thead><tr><th>Subject</th><th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th><th>Final</th></tr></thead>
<tbody>
@forelse($record['subjects'] as $row)
<tr>
<td>{{ $row['subject'] }}</td>
@foreach(['Q1','Q2','Q3','Q4'] as $term)
<td>{{ $row['terms'][$term] !== null ? number_format($row['terms'][$term], 2) : '—' }}</td>
@endforeach
<td><strong>{{ $row['final'] !== null ? number_format($row['final'], 2) : '—' }}</strong></td>
</tr>
@empty
<tr><td colspan="6">No graded academic records are available yet.</td></tr>
@endforelse
</tbody>
</table>
<section class="attendance">
<div><small>Present</small><strong>{{ $record['attendance']['present'] }}</strong></div>
<div><small>Absent</small><strong>{{ $record['attendance']['absent'] }}</strong></div>
<div><small>Late</small><strong>{{ $record['attendance']['late'] }}</strong></div>
<div><small>Excused</small><strong>{{ $record['attendance']['excused'] }}</strong></div>
</section>
<div class="signatures"><div class="line">Class Adviser / Teacher</div><div class="line">Principal / Authorized School Official</div></div>
<p class="note">Generated from the NACS-Phil student information system by {{ $generatedBy->name }} on {{ now()->format('M j, Y g:i A') }}. Use “Download A4 PDF” for the static, watermarked school export.</p>
</main>
</body>
</html>
