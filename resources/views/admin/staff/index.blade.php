@extends('admin.layouts.app', ['title' => 'Staff Accounts'])

@section('content')
<section class="cm-page-head">
    <div>
        <span class="cm-eyebrow">Super Administrator</span>
        <h1>Staff Access &amp; Editorial Roles</h1>
        <p>Prepare two protected administrators and seven specialized website editors. Teachers keep the existing reviewed contribution workflow, but specialized editors see only their assigned areas.</p>
    </div>
    <a class="cm-button cm-button--primary" href="{{ route('admin.staff.create') }}">Invite Staff Account</a>
</section>

<section class="cm-panel">
    <div class="p13-panel-head">
        <div><span class="cm-eyebrow">Access readiness</span><h2>2 administrators + 7 specialized editor seats</h2></div>
        @if($officialEmailDomain)
            <span class="p12-badge p12-badge--good">@{{ $officialEmailDomain }} enforced</span>
        @else
            <span class="p12-badge p12-badge--warn">Official email domain pending</span>
        @endif
    </div>

    <table class="p12-table">
        <thead><tr><th>Access seat</th><th>Target</th><th>Configured</th><th>Status / purpose</th></tr></thead>
        <tbody>
        @foreach($staffingPlan as $role => $plan)
            @php($configured = (int) ($roleCounts[$role] ?? 0))
            <tr>
                <td><strong>{{ $plan['label'] }}</strong></td>
                <td>{{ $plan['target'] }}</td>
                <td>{{ $configured }}</td>
                <td>
                    <span class="p12-badge {{ $configured >= $plan['target'] ? 'p12-badge--good' : 'p12-badge--warn' }}">{{ $configured >= $plan['target'] ? 'Ready' : 'Needs account' }}</span>
                    <br><small>{{ $plan['purpose'] }}</small>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <p><small>No placeholder accounts are created automatically. Invite only real approved staff using their real email addresses.</small></p>
</section>

<section class="cm-panel">
    <div class="p13-panel-head"><div><span class="cm-eyebrow">Privileged accounts</span><h2>Current staff access</h2></div></div>
    <table class="p12-table">
        <thead><tr><th>Name</th><th>Role</th><th>Status</th><th>Last Login</th><th>2FA</th><th>Action</th></tr></thead>
        <tbody>
        @foreach($staff as $person)
            <tr>
                <td><strong>{{ $person->name }}</strong><br><small>{{ $person->email }}</small></td>
                <td>{{ $person->staffRoleLabel() }}@if(blank($person->role))<br><span class="p12-badge p12-badge--warn">Legacy admin role fallback</span>@endif</td>
                <td>
                    @if(!$person->email_verified_at)<span class="p12-badge p12-badge--warn">Email OTP pending</span>@else{{ $person->is_active ? 'Active' : 'Inactive' }}@endif
                    @if($person->force_password_reset)<br><span class="p12-badge p12-badge--warn">Password reset required</span>@endif
                </td>
                <td>{{ $person->last_login_at?->format('M j, Y g:i A') ?: 'Not recorded' }}</td>
                <td><span class="p12-badge {{ $person->twoFactorEnabled() ? 'p12-badge--good' : 'p12-badge--warn' }}">{{ $person->twoFactorEnabled() ? 'Enabled' : 'Off' }}</span></td>
                <td>
                    <div class="p12-actions">
                        @if(!$person->isSuperAdmin())<a href="{{ route('admin.staff.edit',$person) }}">Edit</a>@else<span>Role protected</span>@endif
                        @if(!$person->email_verified_at)
                            <form method="POST" action="{{ route('admin.staff.resend-registration',$person) }}">@csrf<button>Resend registration email</button></form>
                        @endif
                        @if($person->twoFactorEnabled() && auth()->id() !== $person->id)
                            <form method="POST" action="{{ route('admin.staff.reset-two-factor',$person) }}">@csrf<button>Reset 2FA</button></form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</section>
@endsection
