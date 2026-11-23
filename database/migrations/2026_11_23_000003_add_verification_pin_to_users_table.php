<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('verification_code')->nullable()->after('email_verified_at');
            $table->timestamp('verification_code_expires_at')->nullable()->after('verification_code');
            $table->timestamp('verification_code_sent_at')->nullable()->after('verification_code_expires_at');
            $table->unsignedTinyInteger('verification_code_attempts')->default(0)->after('verification_code_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'verification_code',
                'verification_code_expires_at',
                'verification_code_sent_at',
                'verification_code_attempts',
            ]);
        });
    }
};
