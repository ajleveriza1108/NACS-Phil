<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacultyProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\InputBag;

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
        $photo = $this->uploadedFile($request, 'photo');
        $payload = $this->requestPayload($request);
        $data = $this->validated($payload, null, $photo);

        if ($photo) {
            $data['photo_path'] = $photo->store('faculty', 'public');
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
        $photo = $this->uploadedFile($request, 'photo');
        $payload = $this->requestPayload($request);
        $data = $this->validated($payload, $faculty, $photo);

        if ($photo) {
            $newPath = $photo->store('faculty', 'public');
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

    private function validated(
        array $payload,
        ?FacultyProfile $existing,
        ?UploadedFile $photo
    ): array {
        if ($photo) {
            $payload['photo'] = $photo;
        }

        $publishing = $this->truthy($payload['is_published'] ?? null);
        $requiresConsent = $publishing
            && ($photo !== null || filled($existing?->photo_path))
            && blank($existing?->consent_confirmed_at);

        $data = Validator::make($payload, [
            'name' => ['required','string','max:180'],
            'position' => ['required','string','max:180'],
            'department' => ['nullable','string','max:120'],
            'biography' => ['nullable','string','max:6000'],
            'credentials' => ['nullable','string','max:3000'],
            'grade_subject' => ['nullable','string','max:180'],
            'photo' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'alt_text' => ['nullable','string','max:250'],
            'sort_order' => ['required','integer','min:0','max:9999'],
            'consent_confirmed' => $requiresConsent
                ? ['required','accepted']
                : ['sometimes','accepted'],
        ])->validate();

        unset($data['photo'], $data['consent_confirmed']);

        $data['is_featured'] = $this->truthy($payload['is_featured'] ?? null);
        $data['is_published'] = $publishing;

        if ($this->truthy($payload['consent_confirmed'] ?? null)) {
            $data['consent_confirmed_at'] = now();
        } elseif (! $data['is_published']) {
            $data['consent_confirmed_at'] = null;
        }

        return $data;
    }

    private function requestPayload(Request $request): array
    {
        $bag = $request->request;

        if ($bag instanceof InputBag) {
            return $bag->all();
        }

        return is_array($bag) ? $bag : [];
    }

    private function uploadedFile(Request $request, string $key): ?UploadedFile
    {
        $files = $request->files;

        if ($files instanceof FileBag) {
            $file = $files->get($key);
        } elseif (is_array($files)) {
            $file = $files[$key] ?? null;
        } else {
            $file = null;
        }

        return $file instanceof UploadedFile ? $file : null;
    }

    private function truthy(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
