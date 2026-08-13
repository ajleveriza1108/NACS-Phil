<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_family_can_submit_a_basic_inquiry(): void
    {
        $response = $this->post('/inquiries', [
            'guardian_name' => 'Sample Parent',
            'email' => 'parent@example.test',
            'phone' => '',
            'student_name' => 'Sample Learner',
            'level_interested' => 'Elementary',
            'message' => 'Please send the official admissions requirements.',
            'privacy_consent' => '1',
            'website' => '',
        ]);

        $response->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertDatabaseHas('inquiries', [
            'guardian_name' => 'Sample Parent',
            'status' => 'new',
        ]);
    }

    public function test_inquiry_rejects_missing_consent(): void
    {
        $this->post('/inquiries', [
            'guardian_name' => 'Sample Parent',
            'email' => 'parent@example.test',
            'level_interested' => 'Elementary',
            'message' => 'Test message',
        ])->assertSessionHasErrors('privacy_consent');
    }
}
