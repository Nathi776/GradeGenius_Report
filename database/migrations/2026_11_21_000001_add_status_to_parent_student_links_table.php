<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parent_student_links', function (Blueprint $table): void {
            if (! Schema::hasColumn('parent_student_links', 'status')) {
                $table->string('status', 32)->default('pending')->after('linked_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('parent_student_links', function (Blueprint $table): void {
            if (Schema::hasColumn('parent_student_links', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
