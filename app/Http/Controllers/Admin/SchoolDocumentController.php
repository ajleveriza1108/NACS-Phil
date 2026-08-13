<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolDocument;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SchoolDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $category = trim($request->string('category')->toString());

        $query = SchoolDocument::query()->latest();

        if ($category !== '') {
            $query->where('category', $category);
        }

        return view('admin.documents.index', [
            'documents' => $query->paginate(20)->withQueryString(),
            'category' => $category,
        ]);
    }

    public function create(): View
    {
        return view('admin.documents.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, true);
        $file = $request->file('file');
        $storedName = bin2hex(random_bytes(16)).'.'.$file->getClientOriginalExtension();
        $this->disk()->putFileAs('', $file, $storedName);

        $data['file_path'] = $storedName;
        $data['original_name'] = $file->getClientOriginalName();
        $data['mime_type'] = $file->getMimeType();
        $data['file_size'] = $file->getSize();
        $data['uploaded_by_user_id'] = $request->user()?->id;
        $data['published_at'] = $request->boolean('is_published') ? now() : null;

        SchoolDocument::create($data);

        return redirect()->route('admin.documents.index')->with('success', 'School document saved.');
    }

    public function edit(SchoolDocument $document): View
    {
        return view('admin.documents.form', compact('document'));
    }

    public function update(Request $request, SchoolDocument $document): RedirectResponse
    {
        $data = $this->validated($request, false);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $storedName = bin2hex(random_bytes(16)).'.'.$file->getClientOriginalExtension();
            $this->disk()->putFileAs('', $file, $storedName);
            $oldPath = $document->file_path;

            $data['file_path'] = $storedName;
            $data['original_name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getMimeType();
            $data['file_size'] = $file->getSize();

            $document->update($data);
            $this->disk()->delete($oldPath);
        } else {
            $document->update($data);
        }

        if ($request->boolean('is_published')) {
            $document->forceFill(['published_at' => $document->published_at ?? now()])->save();
        } else {
            $document->forceFill(['published_at' => null])->save();
        }

        return redirect()->route('admin.documents.index')->with('success', 'School document updated.');
    }

    public function download(SchoolDocument $document): StreamedResponse
    {
        abort_unless($this->disk()->exists($document->file_path), 404);

        return $this->disk()->download($document->file_path, $document->original_name);
    }

    public function destroy(SchoolDocument $document): RedirectResponse
    {
        $document->delete();

        return redirect()->route('admin.documents.index')->with('success', 'Document archived. The private file was retained for recovery.');
    }

    private function validated(Request $request, bool $fileRequired): array
    {
        $data = $request->validate([
            'title' => ['required','string','max:180'],
            'description' => ['nullable','string','max:5000'],
            'category' => ['required','string','max:100'],
            'file' => [$fileRequired ? 'required' : 'nullable','file','mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png','max:15360'],
            'school_year' => ['nullable','string','max:30'],
            'audience' => ['required', Rule::in(['public','parents','applicants','staff'])],
            'expires_at' => ['nullable','date'],
            'sort_order' => ['required','integer','min:0','max:9999'],
        ]);

        unset($data['file']);

        return $data;
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::build([
            'driver' => 'local',
            'root' => storage_path('app/private/documents'),
            'throw' => true,
        ]);
    }
}
