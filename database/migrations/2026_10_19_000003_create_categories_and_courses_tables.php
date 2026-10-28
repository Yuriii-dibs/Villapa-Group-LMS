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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description');
            $table->json('learning_objectives')->nullable();
            $table->string('difficulty')->default('beginner'); // beginner, intermediate, advanced
            $table->string('thumbnail_path')->nullable();
            $table->string('approval_status')->default('draft'); // draft, pending, approved, rejected
            $table->string('publication_status')->default('draft'); // draft, published, archived
            $table->unsignedInteger('estimated_duration_minutes')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['approval_status', 'publication_status']);
            $table->index('category_id');
            $table->index('instructor_id');
        });

        Schema::create('course_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('prerequisite_course_id')->constrained('courses')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['course_id', 'prerequisite_course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_prerequisites');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('categories');
    }
};
