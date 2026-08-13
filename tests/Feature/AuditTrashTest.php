<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\ContentAudit;
use App\Models\GalleryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuditTrashTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'super_admin'): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_content_create_and_trash_actions_are_audited(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $announcement = Announcement::create([
            'title' => 'Audit Test Announcement',
            'body' => 'Test body',
            'type' => 'info',
            'sort_order' => 0,
        ]);

        $announcement->delete();

        $this->assertDatabaseHas('content_audits', [
            'actor_id' => $admin->id,
            'action' => 'created',
            'auditable_type' => Announcement::class,
            'auditable_id' => $announcement->id,
        ]);

        $this->assertDatabaseHas('content_audits', [
            'actor_id' => $admin->id,
            'action' => 'trashed',
            'auditable_type' => Announcement::class,
            'auditable_id' => $announcement->id,
        ]);
    }

    public function test_deleted_announcement_can_be_restored_by_principal(): void
    {
        $announcement = Announcement::create([
            'title' => 'Recover Me',
            'body' => 'Recoverable content',
            'type' => 'info',
            'sort_order' => 0,
        ]);
        $announcement->delete();

        $principal = $this->admin('principal');

        $this->actingAs($principal)
            ->patch('/admin/trash/announcement/'.$announcement->id.'/restore')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'deleted_at' => null,
        ]);
    }

    public function test_teacher_cannot_open_trash_or_audit_history(): void
    {
        $teacher = $this->admin('teacher');

        $this->actingAs($teacher)->get('/admin/trash')->assertForbidden();
        $this->actingAs($teacher)->get('/admin/audit')->assertForbidden();
    }

    public function test_only_super_admin_can_permanently_delete(): void
    {
        $announcement = Announcement::create([
            'title' => 'Permanent Delete Test',
            'body' => 'Test',
            'type' => 'info',
            'sort_order' => 0,
        ]);
        $announcement->delete();

        $principal = $this->admin('principal');
        $this->actingAs($principal)
            ->delete('/admin/trash/announcement/'.$announcement->id)
            ->assertForbidden();

        $super = $this->admin('super_admin');
        $this->actingAs($super)
            ->delete('/admin/trash/announcement/'.$announcement->id)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_moving_gallery_photo_to_trash_keeps_image_file_for_restore(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('gallery/test.jpg', 'image-bytes');

        $photo = GalleryItem::create([
            'title' => 'Recoverable Photo',
            'category' => 'School Life',
            'image_path' => 'gallery/test.jpg',
            'alt_text' => 'Test',
            'is_published' => false,
            'sort_order' => 0,
        ]);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->delete('/admin/gallery/'.$photo->id)
            ->assertRedirect();

        $this->assertSoftDeleted('gallery_items', ['id' => $photo->id]);
        Storage::disk('public')->assertExists('gallery/test.jpg');
    }

    public function test_principal_can_view_audit_history(): void
    {
        $principal = $this->admin('principal');

        $this->actingAs($principal)
            ->get('/admin/audit')
            ->assertOk()
            ->assertSee('Audit History');
    }
}