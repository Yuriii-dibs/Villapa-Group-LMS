<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                if (!Schema::hasColumn('messages', 'subject_offering_id')) {
                    $table->foreignId('subject_offering_id')->nullable()->after('course_id')->constrained('subject_offerings')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('messages', 'subject_id')) {
                    $table->foreignId('subject_id')->nullable()->after('subject_offering_id')->constrained('subjects')->cascadeOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                if (Schema::hasColumn('messages', 'subject_id')) {
                    $table->dropForeign(['subject_id']);
                    $table->dropColumn('subject_id');
                }
                if (Schema::hasColumn('messages', 'subject_offering_id')) {
                    $table->dropForeign(['subject_offering_id']);
                    $table->dropColumn('subject_offering_id');
                }
            });
        }
    }
};
