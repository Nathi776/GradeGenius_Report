<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_encouragements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('message', 280);
            $table->timestamps();

            $table->index(['student_id', 'created_at']);
            $table->index(['parent_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_encouragements');
    }
};
