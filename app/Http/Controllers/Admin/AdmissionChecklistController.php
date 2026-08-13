<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdmissionApplication;
use App\Models\AdmissionChecklistItem;
use App\Models\AdmissionEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdmissionChecklistController extends Controller
{
    public function update(Request $request, AdmissionApplication $application, AdmissionChecklistItem $item): RedirectResponse
    {
        abort_unless($item->admission_application_id === $application->id, 404);

        $data = $request->validate([
            'is_completed' => ['required','boolean'],
            'notes' => ['nullable','string','max:5000'],
        ]);

        $completed = (bool) $data['is_completed'];

        $item->update([
            'is_completed' => $completed,
            'completed_at' => $completed ? now() : null,
            'completed_by_user_id' => $completed ? $request->user()?->id : null,
            'notes' => $data['notes'] ?? null,
        ]);

        AdmissionEvent::create([
            'admission_application_id' => $application->id,
            'actor_user_id' => $request->user()?->id,
            'event_type' => $completed ? 'checklist_completed' : 'checklist_reopened',
            'public_message' => null,
            'metadata' => [
                'item_key' => $item->item_key,
                'label' => $item->label,
            ],
        ]);

        return back()->with('success', 'Admissions checklist updated.');
    }
}
