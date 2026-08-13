<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_published_announcement(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post('/admin/announcements', [
            'title' => 'Enrollment Notice',
            'excerpt' => 'Official enrollment information.',
            'body' => 'Please contact the school office for verified requirements.',
            'type' => 'enrollment',
            'sort_order' => 0,
            'is_published' => '1',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/admin/announcements');
        $this->assertDatabaseHas('announcements', ['title' => 'Enrollment Notice']);
        $this->assertNotNull(Announcement::where('title', 'Enrollment Notice')->value('published_at'));
    }

    public function test_non_admin_cannot_create_content(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->post('/admin/announcements', [
            'title' => 'Unauthorized',
            'body' => 'This should not be stored.',
            'type' => 'info',
            'sort_order' => 0,
        ])->assertForbidden();

        $this->assertDatabaseMissing('announcements', ['title' => 'Unauthorized']);
    }
}
