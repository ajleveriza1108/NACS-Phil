<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $query = Inquiry::latest();

        if (in_array($status, ['new', 'contacted', 'awaiting_response', 'closed'], true)) {
            $query->where('status', $status);
        }

        return view('admin.inquiries.index', [
            'inquiries' => $query->paginate(20)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function show(Inquiry $inquiry): View
    {
        return view('admin.inquiries.show', compact('inquiry'));
    }

    public function update(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'contacted', 'awaiting_response', 'closed'])],
            'admin_notes' => ['nullable', 'string', 'max:10000'],
        ]);

        $inquiry->update($data);

        return redirect()->route('admin.inquiries.show', $inquiry)->with('success', 'Inquiry status and notes updated.');
    }
}
