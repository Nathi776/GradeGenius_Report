<?php

namespace App\Services;

use App\Mail\VerificationPinMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Issues and checks the 6-digit PIN a new user types into the page they are already on.
 */
final class EmailVerificationPin
{
    public const LENGTH = 6;

    public const VALID_MINUTES = 15;

    public const MAX_ATTEMPTS = 5;

    public const RESEND_SECONDS = 60;

    /**
     * Create a fresh PIN for the user and email it. Any earlier PIN stops working.
     */
    public function issue(User $user): void
    {
        $pin = str_pad((string) random_int(0, 10 ** self::LENGTH - 1), self::LENGTH, '0', STR_PAD_LEFT);

        $user->forceFill([
            'verification_code' => Hash::make($pin),
            'verification_code_expires_at' => now()->addMinutes(self::VALID_MINUTES),
            'verification_code_sent_at' => now(),
            'verification_code_attempts' => 0,
        ])->save();

        Mail::to($user)->send(new VerificationPinMail($user, $pin, self::VALID_MINUTES));
    }

    /**
     * Seconds the user must still wait before another PIN can be requested.
     */
    public function secondsUntilResend(User $user): int
    {
        if ($user->verification_code_sent_at === null) {
            return 0;
        }

        $elapsed = (int) $user->verification_code_sent_at->diffInSeconds(now(), true);

        return max(0, self::RESEND_SECONDS - $elapsed);
    }

    /**
     * Check a submitted PIN. A wrong guess counts towards the attempt limit; reaching it voids the PIN.
     */
    public function verify(User $user, string $submitted): PinResult
    {
        if ($user->verification_code === null || $user->verification_code_expires_at === null) {
            return PinResult::NoPin;
        }

        if ($user->verification_code_expires_at->isPast()) {
            $this->clear($user);

            return PinResult::Expired;
        }

        if ($user->verification_code_attempts >= self::MAX_ATTEMPTS) {
            $this->clear($user);

            return PinResult::TooManyAttempts;
        }

        if (! Hash::check($submitted, $user->verification_code)) {
            $user->forceFill(['verification_code_attempts' => $user->verification_code_attempts + 1])->save();

            return $user->verification_code_attempts >= self::MAX_ATTEMPTS
                ? $this->voidAfterLastAttempt($user)
                : PinResult::Wrong;
        }

        $this->clear($user);

        return PinResult::Correct;
    }

    private function voidAfterLastAttempt(User $user): PinResult
    {
        $this->clear($user);

        return PinResult::TooManyAttempts;
    }

    private function clear(User $user): void
    {
        $user->forceFill([
            'verification_code' => null,
            'verification_code_expires_at' => null,
            'verification_code_attempts' => 0,
        ])->save();
    }
}
