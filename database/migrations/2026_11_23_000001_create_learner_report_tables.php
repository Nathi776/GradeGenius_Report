<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learner_subjects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('target')->nullable();
            $table->json('term_averages')->nullable();
            $table->unsignedInteger('questions_attempted')->default(0);
            $table->unsignedInteger('questions_correct')->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'name']);
        });

        Schema::create('topic_masteries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learner_subject_id')->constrained('learner_subjects')->cascadeOnDelete();
            $table->string('topic');
            $table->unsignedTinyInteger('mastery');
            $table->timestamps();

            $table->unique(['learner_subject_id', 'topic']);
        });

        Schema::create('assessment_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learner_subject_id')->constrained('learner_subjects')->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kind', 32);
            $table->string('title');
            $table->unsignedTinyInteger('score');
            $table->unsignedTinyInteger('attempts')->nullable();
            $table->unsignedTinyInteger('term');
            $table->date('taken_on');
            $table->timestamps();

            $table->index(['learner_subject_id', 'term']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_results');
        Schema::dropIfExists('topic_masteries');
        Schema::dropIfExists('learner_subjects');
    }
};
