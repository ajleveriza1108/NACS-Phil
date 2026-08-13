@extends('admin.layouts.app', ['title' => 'Staff Accounts'])

@section('content')
<section class="cm-page-head">
    <div>
        <a class="cm-back-link" href="{{ route('admin.dashboard') }}">&larr; Content Manager</a>
        <span class="cm-eyebrow">Super Admin only</span>
        <h1>Staff Accounts</h1>
        <p>Create and manage Principal and Teacher access. The existing Super Admin account remains protected.</p>
    </div>
    <a href="{{ route('admin.staff.create') }}" class="cm-button cm-button--primary">Add Staff Account</a>
</section>

<section class="cm-panel cm-panel--wide">
    <div class="p9-role-guide">
        <div><strong>Super Admin</strong><span>All website, staff, and administration tools.</span></div>
        <div><strong>Principal / School Admin</strong><span>Website settings, inquiries, announcements, events, and photos.</span></div>
        <div><strong>Teacher / Content Editor</strong><span>Announcements, events, and approved photos only.</span></div>
    </div>

    <div class="p9-staff-table-wrap">
        <table class="p9-staff-table">
            <thead><tr><th>Staff member</th><th>Role</th><th>Status</th><th>Access</th></tr></thead>
            <tbody>
            @foreach($staff as $member)
                <tr>
                    <td><strong>{{ $member->name }}</strong><small>{{ $member->email }}</small></td>
                    <td><span class="p9-role-chip">{{ $member->staffRoleLabel() }}</span></td>
                    <td><span class="p9-status {{ $member->is_active ? 'is-active' : 'is-inactive' }}">{{ $member->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        @if($member->isSuperAdmin())
                            <span class="p9-protected">Protected</span>
                        @else
                            <a href="{{ route('admin.staff.edit',$member) }}" class="p9-edit-link">Edit account &rarr;</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection