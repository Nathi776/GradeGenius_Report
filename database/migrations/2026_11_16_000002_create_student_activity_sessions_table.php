<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_activity_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->char('session_key', 64)->unique();
            $table->timestamp('login_at');
            $table->timestamp('last_activity_at');
            $table->timestamp('logout_at')->nullable();
            $table->string('browser', 32);
            $table->string('platform', 32);
            $table->timestamps();
            $table->index(['student_id', 'login_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_activity_sessions');
    }
};
