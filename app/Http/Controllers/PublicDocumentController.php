<?php

namespace App\Http\Controllers;

use App\Models\SchoolDocument;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $category = trim($request->string('category')->toString());

        $query = SchoolDocument::publiclyAvailable()
            ->orderBy('sort_order')
            ->latest('published_at');

        if ($category !== '') {
            $query->where('category', $category);
        }

        return view('documents.index', [
            'documents' => $query->paginate(20)->withQueryString(),
            'categories' => SchoolDocument::publiclyAvailable()
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
            'category' => $category,
        ]);
    }

    public function download(SchoolDocument $document): StreamedResponse
    {
        abort_unless(
            SchoolDocument::publiclyAvailable()->whereKey($document->getKey())->exists(),
            404
        );

        abort_unless($this->disk()->exists($document->file_path), 404);

        return $this->disk()->download(
            $document->file_path,
            $document->original_name,
            ['Content-Type' => $document->mime_type ?: 'application/octet-stream']
        );
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
