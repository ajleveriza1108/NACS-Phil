<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacebookMediaItem;
use App\Support\FacebookMediaUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FacebookMediaController extends Controller
{
    public function index(): View
    {
        return view('admin.facebook-media.index', [
            'items' => FacebookMediaItem::query()
                ->latest('updated_at')
                ->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.facebook-media.form', [
            'item' => new FacebookMediaItem(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $user = $request->user();

        $status = $user?->isTeacher() ? 'draft' : $data['status'];
        $this->requirePublicConfirmationWhenPublishing($request, $status);

        FacebookMediaItem::create([
            'title' => $data['title'],
            'media_type' => $data['media_type'],
            'facebook_url' => FacebookMediaUrl::normalize($data['facebook_url']),
            'description' => $data['description'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'status' => $status,
            'is_featured' => $status === 'published'
                && ! $user?->isTeacher()
                && $request->boolean('is_featured'),
            'published_at' => $status === 'published' ? now() : null,
            'public_confirmed_at' => $status === 'published' ? now() : null,
            'created_by_user_id' => $user?->id,
            'updated_by_user_id' => $user?->id,
        ]);

        $message = $user?->isTeacher()
            ? 'Facebook media link saved as Draft. A Principal or Super Admin can publish it.'
            : 'Facebook media link saved.';

        return redirect()->route('admin.facebook-media.index')->with('success', $message);
    }

    public function edit(FacebookMediaItem $facebookMedium): View
    {
        return view('admin.facebook-media.form', [
            'item' => $facebookMedium,
        ]);
    }

    public function update(Request $request, FacebookMediaItem $facebookMedium): RedirectResponse
    {
        $data = $this->validated($request);
        $user = $request->user();

        $status = $user?->isTeacher() ? 'draft' : $data['status'];
        $this->requirePublicConfirmationWhenPublishing($request, $status);

        $normalized = FacebookMediaUrl::normalize($data['facebook_url']);
        $urlChanged = $facebookMedium->facebook_url !== $normalized;

        $facebookMedium->update([
            'title' => $data['title'],
            'media_type' => $data['media_type'],
            'facebook_url' => $normalized,
            'description' => $data['description'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'status' => $status,
            'is_featured' => $status === 'published'
                && ! $user?->isTeacher()
                && $request->boolean('is_featured'),
            'published_at' => $status === 'published'
                ? ($facebookMedium->published_at ?? now())
                : null,
            'public_confirmed_at' => $status === 'published'
                ? now()
                : ($urlChanged ? null : $facebookMedium->public_confirmed_at),
            'updated_by_user_id' => $user?->id,
        ]);

        $message = $user?->isTeacher()
            ? 'Facebook media link updated as Draft.'
            : 'Facebook media link updated.';

        return redirect()->route('admin.facebook-media.index')->with('success', $message);
    }

    public function destroy(Request $request, FacebookMediaItem $facebookMedium): RedirectResponse
    {
        if ($request->user()?->isTeacher()) {
            abort(403);
        }

        $facebookMedium->delete();

        return redirect()->route('admin.facebook-media.index')
            ->with('success', 'Facebook media link moved to Safe Trash.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required','string','max:180'],
            'media_type' => ['required', Rule::in(array_keys(FacebookMediaItem::MEDIA_TYPES))],
            'facebook_url' => [
                'required',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || FacebookMediaUrl::normalize($value) === null) {
                        $fail('Use a valid HTTPS Facebook or fb.watch public video URL.');
                    }
                },
            ],
            'description' => ['nullable','string','max:3000'],
            'starts_at' => ['nullable','date'],
            'status' => ['required', Rule::in(array_keys(FacebookMediaItem::STATUSES))],
            'is_featured' => ['nullable','boolean'],
            'public_confirmed' => ['nullable','accepted'],
        ]);
    }

    private function requirePublicConfirmationWhenPublishing(Request $request, string $status): void
    {
        if ($status === 'published' && ! $request->boolean('public_confirmed')) {
            throw ValidationException::withMessages([
                'public_confirmed' => 'Confirm that this Facebook video is Public and approved for embedding before publishing.',
            ]);
        }
    }
}
