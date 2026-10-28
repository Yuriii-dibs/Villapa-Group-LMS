<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            // Audience targeting
            if (!Schema::hasColumn('announcements', 'target_type')) {
                $table->string('target_type')->default('global')->after('is_pinned');
            }
            // year_level kept for legacy data but unused for filtering (no year_level on users)
            if (!Schema::hasColumn('announcements', 'year_level')) {
                $table->unsignedTinyInteger('year_level')->nullable()->after('target_type');
            }
            // Section-based targeting
            if (!Schema::hasColumn('announcements', 'section_id')) {
                $table->foreignId('section_id')->nullable()->after('year_level')
                      ->constrained('sections')->nullOnDelete();
            }
            if (!Schema::hasColumn('announcements', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->after('section_id')
                      ->constrained('subjects')->nullOnDelete();
            }
            if (!Schema::hasColumn('announcements', 'subject_offering_id')) {
                $table->foreignId('subject_offering_id')->nullable()->after('subject_id')
                      ->constrained('subject_offerings')->nullOnDelete();
            }
            if (!Schema::hasColumn('announcements', 'priority')) {
                $table->string('priority')->default('normal')->after('subject_offering_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('announcements', 'priority') ? 'priority' : null,
                Schema::hasColumn('announcements', 'section_id') ? 'section_id' : null,
            ]));
        });
    }
};
