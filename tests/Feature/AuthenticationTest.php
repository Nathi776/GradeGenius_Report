<?php

namespace Tests\Feature;

use App\Mail\VerificationPinMail;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_is_asked_for_an_emailed_pin(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'name' => 'Lerato Mokoena',
            'email' => 'lerato@example.com',
            'role' => 'student',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertRedirectToRoute('verification.notice');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Lerato Mokoena',
            'email' => 'lerato@example.com',
            'role' => 'student',
        ]);
        Mail::assertSent(VerificationPinMail::class, fn (VerificationPinMail $mail): bool => $mail->hasTo('lerato@example.com'));
    }

    public function test_student_can_link_to_an_existing_verified_parent_by_email(): void
    {
        Mail::fake();
        $parent = User::factory()->create(['role' => 'parent', 'email' => 'guardian@example.com']);

        $this->post('/register', [
            'name' => 'Linked Learner',
            'email' => 'linked@example.com',
            'role' => 'student',
            'parent_email' => $parent->email,
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertRedirectToRoute('verification.notice');

        $student = User::where('email', 'linked@example.com')->firstOrFail();
        $this->assertTrue($parent->students()->whereKey($student->id)->exists());
        $this->assertDatabaseHas('parent_student_links', [
            'parent_id' => $parent->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_student_cannot_link_to_an_unverified_or_non_parent_email(): void
    {
        $unverifiedParent = User::factory()->unverified()->create(['role' => 'parent']);

        $this->from('/register')->post('/register', [
            'name' => 'Unlinked Learner',
            'email' => 'unlinked@example.com',
            'role' => 'student',
            'parent_email' => $unverifiedParent->email,
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertRedirect('/register')->assertSessionHasErrors('parent_email');

        $this->assertDatabaseMissing('users', ['email' => 'unlinked@example.com']);
    }

    public function test_registration_cannot_assign_administrator_role(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Unsafe Admin',
            'email' => 'unsafe@example.com',
            'role' => 'administrator',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertRedirect('/register')
            ->assertSessionHasErrors('role');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'unsafe@example.com']);
    }

    public function test_users_are_redirected_to_their_role_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'email' => 'teacher@example.com',
            'password' => 'secret-password',
            'role' => 'teacher',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ]);

        $response->assertRedirectToRoute('teacher.home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_each_supported_role_has_a_dashboard_destination(): void
    {
        foreach ([
            'student' => 'student.home',
            'parent' => 'parent.home',
            'teacher' => 'teacher.home',
            'administrator' => 'administrator.reports',
        ] as $role => $route) {
            $user = User::factory()->create([
                'role' => $role,
                'email' => $role.'@example.com',
                'password' => 'secret-password',
            ]);

            $response = $this->post('/login', [
                'email' => $user->email,
                'password' => 'secret-password',
            ]);

            $response->assertRedirectToRoute($route);
            $this->post('/logout');
        }
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'student@example.com',
            'password' => 'secret-password',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'student@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_users_can_log_out(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirectToRoute('dashboard.landing');
        $this->assertGuest();
    }

    public function test_student_sessions_and_successful_dashboard_visits_are_recorded(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36')
            ->post('/login', ['email' => $student->email, 'password' => 'password'])
            ->assertRedirectToRoute('student.home');

        $session = DB::table('student_activity_sessions')
            ->where('student_id', $student->id)
            ->first();
        $this->assertNotNull($session);
        $this->assertSame('Chrome', $session->browser);
        $this->assertSame('Windows', $session->platform);

        $this->get('/student')->assertOk();
        $this->assertDatabaseCount('student_activity_sessions', 1);
        $this->assertSame(
            $session->login_at,
            DB::table('student_activity_sessions')->find($session->id)->login_at,
            'Dashboard loads must not reset the login timestamp.',
        );
        $this->assertDatabaseHas('student_activity_events', [
            'student_activity_session_id' => $session->id,
            'event_type' => 'Student dashboard',
        ]);

        $this->post('/logout')->assertRedirectToRoute('dashboard.landing');
        $this->assertNotNull(DB::table('student_activity_sessions')->find($session->id)->logout_at);
    }

    public function test_users_can_only_access_their_role_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($user)
            ->get('/student/report')
            ->assertOk();

        $this->actingAs($user)
            ->get('/administrator/reports')
            ->assertForbidden();
    }

    public function test_administrator_accounts_can_be_provisioned_from_the_console(): void
    {
        $this->artisan('app:create-administrator', [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            '--password' => 'secret-password',
        ])->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'administrator',
        ]);
    }

    public function test_unverified_users_are_sent_to_email_verification_before_dashboard_access(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'student']);

        $this->actingAs($user)
            ->get('/student/report')
            ->assertRedirectToRoute('verification.notice');
    }

    public function test_users_can_request_and_complete_a_password_reset(): void
    {
        Notification::fake();
        $user = User::factory()->create(['password' => 'old-password']);

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirectToRoute('login');

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }
}
