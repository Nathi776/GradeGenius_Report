<?php

namespace Tests\Feature;

use App\Mail\VerificationPinMail;
use App\Models\User;
use App\Services\EmailVerificationPin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailVerificationPinTest extends TestCase
{
    use RefreshDatabase;

    private function capturePinFor(User $user): string
    {
        Mail::fake();
        app(EmailVerificationPin::class)->issue($user);

        $pin = null;
        Mail::assertSent(VerificationPinMail::class, function (VerificationPinMail $mail) use (&$pin): bool {
            $pin = $mail->pin;

            return true;
        });

        return (string) $pin;
    }

    public function test_correct_pin_verifies_the_account_in_the_same_session(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'student']);
        $pin = $this->capturePinFor($user);

        $this->actingAs($user)
            ->post(route('verification.verify'), ['pin' => $pin])
            ->assertRedirectToRoute('student.home');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertNull($user->fresh()->verification_code);
    }

    public function test_pin_is_stored_hashed_and_is_six_digits(): void
    {
        $user = User::factory()->unverified()->create();
        $pin = $this->capturePinFor($user);

        $this->assertMatchesRegularExpression('/^\d{6}$/', $pin);
        $this->assertNotSame($pin, $user->fresh()->verification_code);
    }

    public function test_wrong_pin_is_rejected_and_account_stays_unverified(): void
    {
        $user = User::factory()->unverified()->create();
        $pin = $this->capturePinFor($user);
        $wrong = $pin === '000000' ? '111111' : '000000';

        $this->actingAs($user)
            ->from(route('verification.notice'))
            ->post(route('verification.verify'), ['pin' => $wrong])
            ->assertRedirectToRoute('verification.notice')
            ->assertSessionHasErrors('pin');

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_pin_is_voided_after_too_many_wrong_attempts(): void
    {
        $user = User::factory()->unverified()->create();
        $pin = $this->capturePinFor($user);
        $wrong = $pin === '000000' ? '111111' : '000000';

        foreach (range(1, EmailVerificationPin::MAX_ATTEMPTS) as $ignored) {
            $this->actingAs($user)->post(route('verification.verify'), ['pin' => $wrong]);
        }

        $this->actingAs($user->fresh())
            ->post(route('verification.verify'), ['pin' => $pin])
            ->assertSessionHasErrors('pin');

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_expired_pin_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        $pin = $this->capturePinFor($user);

        $this->travel(EmailVerificationPin::VALID_MINUTES + 1)->minutes();

        $this->actingAs($user->fresh())
            ->post(route('verification.verify'), ['pin' => $pin])
            ->assertSessionHasErrors('pin');

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_pin_must_be_six_digits(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('verification.verify'), ['pin' => '12ab'])
            ->assertSessionHasErrors('pin');
    }

    public function test_resend_issues_a_new_pin_and_invalidates_the_old_one(): void
    {
        $user = User::factory()->unverified()->create();
        $oldPin = $this->capturePinFor($user);

        $this->travel(EmailVerificationPin::RESEND_SECONDS + 1)->seconds();
        Mail::fake();

        $this->actingAs($user->fresh())
            ->post(route('verification.send'))
            ->assertSessionHas('status');

        Mail::assertSent(VerificationPinMail::class, 1);

        $newPin = null;
        Mail::assertSent(VerificationPinMail::class, function (VerificationPinMail $mail) use (&$newPin): bool {
            $newPin = $mail->pin;

            return true;
        });

        if ($newPin !== $oldPin) {
            $this->actingAs($user->fresh())
                ->post(route('verification.verify'), ['pin' => $oldPin])
                ->assertSessionHasErrors('pin');
        }

        $this->actingAs($user->fresh())
            ->post(route('verification.verify'), ['pin' => (string) $newPin])
            ->assertRedirect();

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_resend_is_blocked_during_the_cooldown(): void
    {
        $user = User::factory()->unverified()->create();
        $this->capturePinFor($user);
        Mail::fake();

        $this->actingAs($user->fresh())
            ->post(route('verification.send'))
            ->assertSessionHasErrors('pin');

        Mail::assertNothingSent();
    }

    public function test_notice_page_shows_the_pin_form(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('name="pin"', false);
    }

    public function test_verified_users_skip_the_notice_page(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertRedirectToRoute('teacher.home');
    }
}
