<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_activity_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_activity_session_id')
                ->constrained('student_activity_sessions')
                ->cascadeOnDelete();
            $table->string('event_type', 32);
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['student_activity_session_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_activity_events');
    }
};
