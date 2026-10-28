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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->constrained('modules')->nullOnDelete();
            $table->string('title');
            $table->longText('description');
            $table->unsignedInteger('max_score')->default(100);
            $table->timestamp('due_date')->nullable();
            $table->boolean('allow_late_submissions')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['course_id', 'is_published']);
        });

        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->longText('submission_text')->nullable();
            $table->string('status')->default('submitted'); // submitted, graded, resubmitted
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->unique(['assignment_id', 'user_id']);
            $table->index(['assignment_id', 'status']);
        });

        Schema::create('submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_file_name');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type')->nullable();
            $table->timestamps();
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->unique()->constrained('submissions')->cascadeOnDelete();
            $table->foreignId('grader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('score', 5, 2);
            $table->text('feedback')->nullable();
            $table->timestamp('graded_at');
            $table->timestamps();
        });

        Schema::create('grade_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete();
            $table->foreignId('changed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('old_score', 5, 2)->nullable();
            $table->decimal('new_score', 5, 2);
            $table->text('old_feedback')->nullable();
            $table->text('new_feedback')->nullable();
            $table->string('change_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_histories');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('submission_files');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('assignments');
    }
};
