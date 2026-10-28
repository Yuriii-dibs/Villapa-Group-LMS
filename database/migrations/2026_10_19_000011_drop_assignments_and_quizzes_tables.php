<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drops assignments, quizzes, submissions, questions, and grading tables
     * in reverse foreign key order (child tables first).
     */
    public function up(): void
    {
        // Assignment and grading child-first drop sequence
        Schema::dropIfExists('grade_histories');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('submission_files');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('assignments');

        // Quiz and question child-first drop sequence
        Schema::dropIfExists('quiz_answers');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('quizzes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversible without recreating the original table schemas.
    }
};
