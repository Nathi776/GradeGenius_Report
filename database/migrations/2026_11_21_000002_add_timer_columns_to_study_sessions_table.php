<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_sessions', function (Blueprint $table): void {
            if (! Schema::hasColumn('study_sessions', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('completed_at');
            }
            if (! Schema::hasColumn('study_sessions', 'finished_at')) {
                $table->timestamp('finished_at')->nullable()->after('started_at');
            }
            if (! Schema::hasColumn('study_sessions', 'actual_minutes')) {
                $table->unsignedInteger('actual_minutes')->default(0)->after('finished_at');
            }
            if (! Schema::hasColumn('study_sessions', 'is_counted')) {
                $table->boolean('is_counted')->default(false)->after('actual_minutes');
            }
            if (! Schema::hasColumn('study_sessions', 'is_paused')) {
                $table->boolean('is_paused')->default(false)->after('is_counted');
            }
            if (! Schema::hasColumn('study_sessions', 'paused_at')) {
                $table->timestamp('paused_at')->nullable()->after('is_paused');
            }
            if (! Schema::hasColumn('study_sessions', 'paused_seconds')) {
                $table->unsignedInteger('paused_seconds')->default(0)->after('paused_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('study_sessions', function (Blueprint $table): void {
            $table->dropColumn([
                'started_at',
                'finished_at',
                'actual_minutes',
                'is_counted',
                'is_paused',
                'paused_at',
                'paused_seconds',
            ]);
        });
    }
};
