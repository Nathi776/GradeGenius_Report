<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('subject', 255);
            $table->text('body');
            $table->string('quick_reply', 64)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_messages');
    }
};
