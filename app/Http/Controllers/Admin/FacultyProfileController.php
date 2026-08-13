<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacultyProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FacultyProfileController extends Controller
{
    public function index(Request $request): View
    {
        $department = trim($request->string('department')->toString());

        $query = FacultyProfile::query()->orderBy('sort_order')->orderBy('name');

        if ($department !== '') {
            $query->where('department', $department);
        }

        return view('admin.faculty.index', [
            'profiles' => $query->paginate(20)->withQueryString(),
            'department' => $department,
        ]);
    }

    public function create(): View
    {
        return view('admin.faculty.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('faculty', 'public');
        }

        $data['created_by_user_id'] = $request->user()?->id;
        FacultyProfile::create($data);

        return redirect()->route('admin.faculty.index')->with('success', 'Faculty or staff profile created.');
    }

    public function edit(FacultyProfile $faculty): View
    {
        return view('admin.faculty.form', ['profile' => $faculty]);
    }

    public function update(Request $request, FacultyProfile $faculty): RedirectResponse
    {
        $data = $this->validated($request, $faculty);

        if ($request->hasFile('photo')) {
            $newPath = $request->file('photo')->store('faculty', 'public');
            $oldPath = $faculty->photo_path;
            $data['photo_path'] = $newPath;
            $faculty->update($data);

            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        } else {
            $faculty->update($data);
        }

        return redirect()->route('admin.faculty.index')->with('success', 'Faculty or staff profile updated.');
    }

    public function destroy(FacultyProfile $faculty): RedirectResponse
    {
        $faculty->delete();

        return redirect()->route('admin.faculty.index')->with('success', 'Faculty or staff profile archived.');
    }

    private function validated(Request $request, ?FacultyProfile $existing): array
    {
        $data = $request->validate([
            'name' => ['required','string','max:180'],
            'position' => ['required','string','max:180'],
            'department' => ['nullable','string','max:120'],
            'biography' => ['nullable','string','max:6000'],
            'credentials' => ['nullable','string','max:3000'],
            'grade_subject' => ['nullable','string','max:180'],
            'photo' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'alt_text' => ['nullable','string','max:250'],
            'sort_order' => ['required','integer','min:0','max:9999'],
            'consent_confirmed' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $request->boolean('is_published') && ($request->hasFile('photo') || filled($existing?->photo_path)) && blank($existing?->consent_confirmed_at)),
                'accepted',
            ],
        ]);

        unset($data['photo'], $data['consent_confirmed']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_published'] = $request->boolean('is_published');

        if ($request->boolean('consent_confirmed')) {
            $data['consent_confirmed_at'] = now();
        } elseif (! $data['is_published']) {
            $data['consent_confirmed_at'] = null;
        }

        return $data;
    }
}
