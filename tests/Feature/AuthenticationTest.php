<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'Lerato Mokoena',
            'email' => 'lerato@example.com',
            'role' => 'student',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertRedirectToRoute('student.reports');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Lerato Mokoena',
            'email' => 'lerato@example.com',
            'role' => 'student',
        ]);
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
            'student' => 'student.reports',
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
}
