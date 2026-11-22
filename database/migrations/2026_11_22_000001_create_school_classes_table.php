<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('subject');
            $table->unsignedInteger('grade')->default(12);
            $table->unsignedInteger('target')->nullable();
            $table->timestamps();
        });

        Schema::create('class_learner', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('learner_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['school_class_id', 'learner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_learner');
        Schema::dropIfExists('school_classes');
    }
};
