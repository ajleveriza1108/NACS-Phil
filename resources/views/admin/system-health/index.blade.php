@extends('admin.layouts.app', ['title' => 'System Health'])
@section('content')
<section class="cm-page-head"><div><span class="cm-eyebrow">Super Admin only</span><h1>System Health</h1><p>A simple operational dashboard for the school website, private storage, content workload, security readiness, and local backups.</p></div></section>
<div class="p12-health">
<article><small>Database</small><strong>{{ $databaseOk ? 'OK' : 'ERROR' }}</strong></article>
<article><small>Private Admissions Storage</small><strong>{{ $privateAdmissionsOk ? 'Writable' : 'Check' }}</strong></article>
<article><small>Private Documents Storage</small><strong>{{ $privateDocumentsOk ? 'Writable' : 'Check' }}</strong></article>
<article><small>Environment</small><strong>{{ $environment }}</strong><small>Debug: {{ $debugEnabled ? 'ON' : 'OFF' }}</small></article>
<article><small>Leadership without 2FA</small><strong>{{ $twoFactorMissing }}</strong><small>Recommended: 0 before deployment</small></article>
<article><small>Latest Local Backup</small><strong style="font-size:14px">{{ $latestBackup ?: 'None detected' }}</strong></article>
</div>
<section class="cm-panel" style="margin-top:18px"><h2>Operational Counts</h2><table class="p12-table"><tbody>@foreach($counts as $label=>$value)<tr><th>{{ str_replace('_',' ',ucwords($label,'_')) }}</th><td>{{ $value }}</td></tr>@endforeach</tbody></table></section>
@if($debugEnabled)<div class="cm-alert cm-alert--error"><strong>Deployment reminder:</strong> APP_DEBUG must be false on the public production website.</div>@endif
@endsection
