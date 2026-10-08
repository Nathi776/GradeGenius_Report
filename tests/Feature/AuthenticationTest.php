<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_is_logged_in(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Lerato Mokoena',
            'email' => 'lerato@example.com',
            'role' => 'student',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertRedirectToRoute('student.report');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Lerato Mokoena',
            'email' => 'lerato@example.com',
            'role' => 'student',
        ]);
        Notification::assertSentTo(User::where('email', 'lerato@example.com')->first(), VerifyEmail::class);
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

        $response->assertRedirectToRoute('teacher.reports');
        $this->assertAuthenticatedAs($user);
    }

    public function test_each_supported_role_has_a_dashboard_destination(): void
    {
        foreach ([
            'student' => 'student.report',
            'parent' => 'parent.reports',
            'teacher' => 'teacher.reports',
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
