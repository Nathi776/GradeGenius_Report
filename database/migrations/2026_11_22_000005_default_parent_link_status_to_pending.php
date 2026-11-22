<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * New parent links must start as pending unless code says otherwise. Existing rows keep their status.
 * This is a separate migration so databases that already ran the earlier one end up the same as fresh ones.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parent_student_links', function (Blueprint $table): void {
            $table->string('status', 32)->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('parent_student_links', function (Blueprint $table): void {
            $table->string('status', 32)->default('verified')->change();
        });
    }
};
