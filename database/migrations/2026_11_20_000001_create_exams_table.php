<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table): void {
            $table->id();
            $table->string('subject_key', 64);
            $table->unsignedTinyInteger('grade');
            $table->string('title', 120);
            $table->string('paper', 60)->nullable();
            $table->date('exam_date');
            $table->timestamps();
            $table->index(['grade', 'exam_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
