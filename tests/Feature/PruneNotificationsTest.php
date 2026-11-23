<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PruneNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function notification(User $user, string $createdAt, ?string $readAt): void
    {
        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => '{}',
            'read_at' => $readAt,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    public function test_old_notifications_are_deleted_and_recent_ones_kept(): void
    {
        $user = User::factory()->create();
        $this->notification($user, now()->subDays(120)->toDateTimeString(), now()->subDays(110)->toDateTimeString());
        $this->notification($user, now()->subDays(10)->toDateTimeString(), now()->subDays(5)->toDateTimeString());
        $this->notification($user, now()->subDays(120)->toDateTimeString(), null);
        $this->notification($user, now()->subDays(400)->toDateTimeString(), null);

        $this->artisan('app:prune-notifications')->assertSuccessful();

        $this->assertSame(2, DB::table('notifications')->count());
    }
}
