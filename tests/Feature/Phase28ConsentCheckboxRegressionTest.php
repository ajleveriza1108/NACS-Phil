<?php

namespace Tests\Feature;

use App\Models\FacultyProfile;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase28ConsentCheckboxRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_library_optional_consent_may_be_unchecked_while_rights_remain_required(): void
    {
        Storage::fake('public');

        $principal = $this->staff('principal');

        $this->actingAs($principal)->post(route('admin.media.store'), [
            'title' => 'Optional Consent Media',
            'file' => $this->pngUpload('optional-consent-media.png'),
            'alt_text' => 'Media with optional consent unchecked',
            'rights_confirmed' => '1',
        ])->assertRedirect(route('admin.media.index'));

        $asset = MediaAsset::where('title', 'Optional Consent Media')->firstOrFail();

        $this->assertNotNull($asset->rights_confirmed_at);
        $this->assertNull($asset->consent_confirmed_at);
        Storage::disk('public')->assertExists($asset->file_path);
    }

    public function test_faculty_without_photo_can_be_published_without_photo_consent(): void
    {
        $principal = $this->staff('principal');

        $this->actingAs($principal)->post(route('admin.faculty.store'), [
            'name' => 'Faculty Without Photo',
            'position' => 'Teacher',
            'sort_order' => 0,
            'is_published' => '1',
        ])->assertRedirect(route('admin.faculty.index'));

        $profile = FacultyProfile::where('name', 'Faculty Without Photo')->firstOrFail();

        $this->assertTrue($profile->is_published);
        $this->assertNull($profile->photo_path);
        $this->assertNull($profile->consent_confirmed_at);
    }

    public function test_published_faculty_with_photo_still_requires_explicit_consent(): void
    {
        Storage::fake('public');

        $principal = $this->staff('principal');

        $this->actingAs($principal)->post(route('admin.faculty.store'), [
            'name' => 'Faculty Photo Missing Consent',
            'position' => 'Teacher',
            'photo' => $this->pngUpload('faculty-no-consent.png'),
            'alt_text' => 'Faculty profile photo',
            'sort_order' => 0,
            'is_published' => '1',
        ])->assertSessionHasErrors('consent_confirmed');

        $this->assertDatabaseMissing('faculty_profiles', [
            'name' => 'Faculty Photo Missing Consent',
        ]);

        $this->actingAs($principal)->post(route('admin.faculty.store'), [
            'name' => 'Faculty Photo With Consent',
            'position' => 'Teacher',
            'photo' => $this->pngUpload('faculty-with-consent.png'),
            'alt_text' => 'Faculty profile photo with consent',
            'sort_order' => 0,
            'is_published' => '1',
            'consent_confirmed' => '1',
        ])->assertRedirect(route('admin.faculty.index'));

        $profile = FacultyProfile::where('name', 'Faculty Photo With Consent')->firstOrFail();

        $this->assertTrue($profile->is_published);
        $this->assertNotNull($profile->photo_path);
        $this->assertNotNull($profile->consent_confirmed_at);
        Storage::disk('public')->assertExists($profile->photo_path);
    }

    private function pngUpload(string $name): UploadedFile
    {
        $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true);

        if (! is_string($content)) {
            throw new \RuntimeException('Embedded PNG fixture could not be decoded.');
        }

        return UploadedFile::fake()->createWithContent($name, $content);
    }

    private function staff(string $role): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
