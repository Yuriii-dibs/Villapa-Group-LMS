<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Academic Terms Table
        if (!Schema::hasTable('academic_terms')) {
            Schema::create('academic_terms', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g. "1st Semester AY 2026-2027"
                $table->string('academic_year')->default('2026-2027');
                $table->string('semester')->default('1st'); // 1st, 2nd, Summer
                $table->date('start_date');
                $table->date('end_date');
                $table->boolean('is_current')->default(false);
                $table->timestamps();
            });
        }

        // 2. Sections Table
        if (!Schema::hasTable('sections')) {
            Schema::create('sections', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g. "IT3-J", "IT3-A"
                $table->string('program')->default('BSIT'); // BSIT, BSCrim, etc.
                $table->unsignedTinyInteger('year_level')->default(3); // 1, 2, 3, 4
                $table->timestamps();
            });
        }

        // 3. Add units to subjects table if missing
        if (Schema::hasTable('subjects') && !Schema::hasColumn('subjects', 'units')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->unsignedTinyInteger('units')->default(3)->after('title');
            });
        }

        // 4. Subject Offerings Table
        if (!Schema::hasTable('subject_offerings')) {
            Schema::create('subject_offerings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('academic_term_id')->nullable()->constrained('academic_terms')->nullOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
                $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('instructor_name')->nullable();
                $table->json('schedules')->nullable();
                $table->string('room')->nullable();
                $table->string('meet_link')->nullable();
                $table->string('status')->default('active'); // active, cancelled, completed
                $table->timestamps();
            });
        }

        // 5. Offering Enrollments Table
        if (!Schema::hasTable('offering_enrollments')) {
            Schema::create('offering_enrollments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('subject_offering_id')->constrained('subject_offerings')->cascadeOnDelete();
                $table->string('status')->default('enrolled'); // enrolled, dropped, completed
                $table->dateTime('enrolled_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'subject_offering_id']);
            });
        }

        // 6. Update subject_submissions for multi-mode submissions (file, text, link)
        if (Schema::hasTable('subject_submissions')) {
            Schema::table('subject_submissions', function (Blueprint $table) {
                if (!Schema::hasColumn('subject_submissions', 'submission_type')) {
                    $table->string('submission_type')->default('file')->after('user_id'); // 'file', 'text', 'link'
                }
                if (!Schema::hasColumn('subject_submissions', 'link_url')) {
                    $table->text('link_url')->nullable()->after('file_name');
                }
                if (!Schema::hasColumn('subject_submissions', 'content_text')) {
                    $table->longText('content_text')->nullable()->after('link_url');
                }
            });
        }

        // 7. Update announcements table for scoped audiences
        if (Schema::hasTable('announcements')) {
            Schema::table('announcements', function (Blueprint $table) {
                if (!Schema::hasColumn('announcements', 'target_type')) {
                    $table->string('target_type')->default('global')->after('content'); // global, year_level, offering
                }
                if (!Schema::hasColumn('announcements', 'year_level')) {
                    $table->unsignedTinyInteger('year_level')->nullable()->after('target_type');
                }
                if (!Schema::hasColumn('announcements', 'subject_id')) {
                    $table->foreignId('subject_id')->nullable()->after('year_level')->constrained('subjects')->nullOnDelete();
                }
                if (!Schema::hasColumn('announcements', 'subject_offering_id')) {
                    $table->foreignId('subject_offering_id')->nullable()->after('subject_id')->constrained('subject_offerings')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('announcements')) {
            Schema::table('announcements', function (Blueprint $table) {
                if (Schema::hasColumn('announcements', 'subject_offering_id')) {
                    $table->dropForeign(['subject_offering_id']);
                    $table->dropColumn('subject_offering_id');
                }
                if (Schema::hasColumn('announcements', 'subject_id')) {
                    $table->dropForeign(['subject_id']);
                    $table->dropColumn('subject_id');
                }
                if (Schema::hasColumn('announcements', 'year_level')) {
                    $table->dropColumn('year_level');
                }
                if (Schema::hasColumn('announcements', 'target_type')) {
                    $table->dropColumn('target_type');
                }
            });
        }

        if (Schema::hasTable('subject_submissions')) {
            Schema::table('subject_submissions', function (Blueprint $table) {
                if (Schema::hasColumn('subject_submissions', 'content_text')) {
                    $table->dropColumn('content_text');
                }
                if (Schema::hasColumn('subject_submissions', 'link_url')) {
                    $table->dropColumn('link_url');
                }
                if (Schema::hasColumn('subject_submissions', 'submission_type')) {
                    $table->dropColumn('submission_type');
                }
            });
        }

        Schema::dropIfExists('offering_enrollments');
        Schema::dropIfExists('subject_offerings');

        if (Schema::hasTable('subjects') && Schema::hasColumn('subjects', 'units')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropColumn('units');
            });
        }

        Schema::dropIfExists('sections');
        Schema::dropIfExists('academic_terms');
    }
};
