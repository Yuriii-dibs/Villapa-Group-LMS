<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('subjects', 'meet_link')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->string('meet_link')->nullable()->after('schedules');
                $table->text('description')->nullable()->after('meet_link');
            });
        }

        Schema::create('subject_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type')->default('pdf');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('external_url')->nullable();
            $table->timestamps();
        });

        Schema::create('subject_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->unsignedInteger('total_points')->default(100);
            $table->timestamps();
        });

        Schema::create('subject_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_assignment_id')->constrained('subject_assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('submitted');
            $table->dateTime('submitted_at');
            $table->unsignedInteger('grade')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_submissions');
        Schema::dropIfExists('subject_assignments');
        Schema::dropIfExists('subject_resources');

        if (Schema::hasColumn('subjects', 'meet_link')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropColumn(['meet_link', 'description']);
            });
        }
    }
};
