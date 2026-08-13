<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());

        $query = Inquiry::query()->with('assignedTo')->latest();

        if (array_key_exists($status, Inquiry::STATUSES)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('guardian_name', 'like', "%{$search}%")
                    ->orWhere('student_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return view('admin.inquiries.index', [
            'inquiries' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'search' => $search,
            'statuses' => Inquiry::STATUSES,
        ]);
    }

    public function show(Inquiry $inquiry): View
    {
        return view('admin.inquiries.show', [
            'inquiry' => $inquiry->load('assignedTo'),
            'statuses' => Inquiry::STATUSES,
            'staff' => User::query()->where('is_admin', true)->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Inquiry::STATUSES))],
            'admin_notes' => ['nullable', 'string', 'max:10000'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'follow_up_at' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:80'],
            'interest_level' => ['nullable', Rule::in(['low','medium','high'])],
            'last_contacted_at' => ['nullable', 'date'],
        ]);

        $inquiry->update($data);

        return redirect()->route('admin.inquiries.show', $inquiry)
            ->with('success', 'Inquiry workflow, assignment, and notes updated.');
    }
}
