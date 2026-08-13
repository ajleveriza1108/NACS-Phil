<?php

namespace Tests\Feature;

use App\Models\AdmissionApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdmissionPortalPhase9CTest extends TestCase
{
    use RefreshDatabase;

    private function application(string $status = 'submitted'): array
    {
        $plain = 'ABCD-EFGH-JKLM';

        $application = AdmissionApplication::create([
            'reference_code' => 'NACS-2026-TESTCODE',
            'access_code_hash' => Hash::make(AdmissionApplication::normalizeAccessCode($plain)),
            'guardian_name' => 'Sample Parent',
            'guardian_email' => 'parent@example.test',
            'student_name' => 'Sample Learner',
            'applying_for_level' => 'Grade 4',
            'school_year' => '2026-2027',
            'status' => $status,
            'privacy_consent_at' => now(),
            'application_consent_at' => now(),
            'submitted_at' => now(),
        ]);

        return [$application,$plain];
    }

    public function test_admissions_page_links_to_application_and_tracking(): void
    {
        $this->get('/admissions')
            ->assertOk()
            ->assertSee('/admissions/apply',false)
            ->assertSee('/admissions/track',false);
    }

    public function test_family_can_submit_preliminary_application_and_receives_one_time_codes(): void
    {
        $response = $this->post('/admissions/apply',[
            'guardian_name'=>'Sample Parent',
            'guardian_email'=>'parent@example.test',
            'guardian_phone'=>'',
            'student_name'=>'Sample Learner',
            'applying_for_level'=>'Grade 4',
            'school_year'=>'2026-2027',
            'family_notes'=>'Please advise us of the next step.',
            'privacy_consent'=>'1',
            'application_consent'=>'1',
            'website'=>'',
        ]);

        $application = AdmissionApplication::firstOrFail();

        $response->assertRedirect(route('admissions.receipt',$application));

        $this->get(route('admissions.receipt',$application))
            ->assertOk()
            ->assertSee($application->reference_code)
            ->assertSee('Access code');
    }

    public function test_wrong_access_code_is_rejected(): void
    {
        [$application] = $this->application();

        $this->post('/admissions/track',[
            'reference_code'=>$application->reference_code,
            'access_code'=>'WRONG-CODE',
        ])->assertSessionHasErrors('access_code');
    }

    public function test_correct_access_code_opens_private_status_page(): void
    {
        [$application,$plain] = $this->application();

        $this->post('/admissions/track',[
            'reference_code'=>$application->reference_code,
            'access_code'=>$plain,
        ])->assertRedirect(route('admissions.status',$application));

        $this->get(route('admissions.status',$application))
            ->assertOk()
            ->assertSee('Sample Learner');
    }

    public function test_document_upload_is_blocked_until_school_requests_documents(): void
    {
        [$application,$plain] = $this->application('under_review');

        $this->post('/admissions/track',[
            'reference_code'=>$application->reference_code,
            'access_code'=>$plain,
        ]);

        $this->post(route('admissions.documents.store',$application),[
            'document_type'=>'report_card',
            'document'=>UploadedFile::fake()->create('report.pdf',100,'application/pdf'),
            'document_consent'=>'1',
        ])->assertForbidden();
    }

    public function test_teacher_cannot_access_private_admissions_records(): void
    {
        $teacher=User::factory()->create([
            'is_admin'=>true,
            'role'=>'teacher',
            'is_active'=>true,
        ]);

        $this->actingAs($teacher)
            ->get('/admin/admissions')
            ->assertForbidden();
    }

    public function test_principal_can_review_private_admissions_records(): void
    {
        $principal=User::factory()->create([
            'is_admin'=>true,
            'role'=>'principal',
            'is_active'=>true,
        ]);

        $this->actingAs($principal)
            ->get('/admin/admissions')
            ->assertOk()
            ->assertSee('Admissions Applications');
    }
}