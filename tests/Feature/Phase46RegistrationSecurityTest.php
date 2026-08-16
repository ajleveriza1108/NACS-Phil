<?php

namespace Tests\Feature;

use App\Models\AdmissionApplication;
use App\Models\RegistrationInvitation;
use App\Models\User;
use App\Notifications\RegistrationInvitationNotification;
use App\Notifications\RegistrationOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class Phase46RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_invitation_requires_strong_password_and_final_email_otp(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'name' => 'NACS Super Admin',
            'email' => 'admin@example.test',
            'is_admin' => true,
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'Maria Teacher',
            'email' => 'maria.teacher@example.test',
            'role' => 'teacher',
        ])->assertRedirect(route('admin.staff.index'));

        $teacher = User::query()->where('email', 'maria.teacher@example.test')->firstOrFail();

        $this->assertFalse($teacher->is_active);
        $this->assertNull($teacher->email_verified_at);

        $token = null;

        Notification::assertSentTo(
            $teacher,
            RegistrationInvitationNotification::class,
            function (RegistrationInvitationNotification $notification) use (&$token): bool {
                $token = basename((string) parse_url($notification->registrationUrl, PHP_URL_PATH));

                return preg_match('/\A[a-f0-9]{64}\z/', (string) $token) === 1;
            }
        );

        Auth::logout();

        $this->post(route('registration.password.store', ['token' => $token]), [
            'password' => 'weakpassword',
            'password_confirmation' => 'weakpassword',
        ])->assertSessionHasErrors('password');

        $strongPassword = 'Faithful!Learning2026';

        $this->post(route('registration.password.store', ['token' => $token]), [
            'password' => $strongPassword,
            'password_confirmation' => $strongPassword,
        ])->assertRedirect(route('registration.otp.show', ['token' => $token]));

        $teacher->refresh();

        $this->assertTrue(Hash::check($strongPassword, $teacher->password));
        $this->assertFalse($teacher->is_active);
        $this->assertNull($teacher->email_verified_at);

        $otp = null;

        Notification::assertSentTo(
            $teacher,
            RegistrationOtpNotification::class,
            function (RegistrationOtpNotification $notification) use (&$otp): bool {
                $otp = $notification->code;

                return preg_match('/\A\d{6}\z/', $notification->code) === 1;
            }
        );

        $invitation = RegistrationInvitation::query()
            ->where('user_id', $teacher->id)
            ->firstOrFail();

        $this->assertNotSame($otp, $invitation->otp_hash);
        $this->assertSame(64, strlen((string) $invitation->otp_hash));

        $this->post(route('registration.otp.verify', ['token' => $token]), [
            'otp' => $otp,
        ])->assertRedirect(route('admin.login'));

        $teacher->refresh();
        $invitation->refresh();

        $this->assertTrue($teacher->is_active);
        $this->assertNotNull($teacher->email_verified_at);
        $this->assertNotNull($invitation->completed_at);
        $this->assertNull($invitation->token_hash);
        $this->assertNull($invitation->otp_hash);
    }

    public function test_student_portal_registration_creates_inactive_invited_account(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'name' => 'NACS Super Admin',
            'email' => 'admin2@example.test',
            'is_admin' => true,
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.students.store'), [
            'student_number' => 'NACS-2026-001',
            'first_name' => 'Joshua',
            'middle_name' => '',
            'last_name' => 'Learner',
            'preferred_name' => '',
            'date_of_birth' => '2014-01-15',
            'gender' => 'Male',
            'phone' => '',
            'home_address' => 'Sariaya, Quezon',
            'grade_level' => AdmissionApplication::LEVELS[0],
            'section' => 'A',
            'school_year' => '2026-2027',
            'status' => 'active',
            'student_email' => 'joshua.learner@example.test',
        ])->assertRedirect();

        $studentUser = User::query()
            ->where('email', 'joshua.learner@example.test')
            ->firstOrFail();

        $this->assertSame('student', $studentUser->role);
        $this->assertFalse($studentUser->is_active);
        $this->assertNull($studentUser->email_verified_at);

        Notification::assertSentTo($studentUser, RegistrationInvitationNotification::class);
        $this->assertDatabaseHas('registration_invitations', ['user_id' => $studentUser->id]);
    }

    public function test_five_wrong_codes_keep_the_account_inactive(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'name' => 'NACS Super Admin',
            'email' => 'admin3@example.test',
            'is_admin' => true,
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'Test Teacher',
            'email' => 'test.teacher@example.test',
            'role' => 'teacher',
        ]);

        $teacher = User::query()->where('email', 'test.teacher@example.test')->firstOrFail();
        $token = null;

        Notification::assertSentTo(
            $teacher,
            RegistrationInvitationNotification::class,
            function (RegistrationInvitationNotification $notification) use (&$token): bool {
                $token = basename((string) parse_url($notification->registrationUrl, PHP_URL_PATH));
                return true;
            }
        );

        Auth::logout();

        $this->post(route('registration.password.store', ['token' => $token]), [
            'password' => 'Harbor!Cedar2026',
            'password_confirmation' => 'Harbor!Cedar2026',
        ])->assertRedirect(route('registration.otp.show', ['token' => $token]));

        $actualOtp = null;

        Notification::assertSentTo(
            $teacher,
            RegistrationOtpNotification::class,
            function (RegistrationOtpNotification $notification) use (&$actualOtp): bool {
                $actualOtp = $notification->code;
                return true;
            }
        );

        $wrongOtp = $actualOtp === '000000' ? '111111' : '000000';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('registration.otp.verify', ['token' => $token]), [
                'otp' => $wrongOtp,
            ])->assertSessionHasErrors('otp');
        }

        $invitation = RegistrationInvitation::query()
            ->where('user_id', $teacher->id)
            ->firstOrFail();

        $this->assertSame(5, $invitation->otp_attempts);
        $this->assertFalse($teacher->fresh()->is_active);
        $this->assertNull($teacher->fresh()->email_verified_at);
    }
}
