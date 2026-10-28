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
        // Drop in reverse child-first order in case of partial creation
        Schema::dropIfExists('events');
        Schema::dropIfExists('class_attendances');
        Schema::dropIfExists('class_sessions');
        Schema::dropIfExists('messages');

        // 1. Messages table for course chat rooms and direct messaging
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['course_id', 'id']);
            $table->index(['sender_id', 'recipient_id', 'id']);
            $table->index(['recipient_id', 'sender_id', 'id']);
        });

        // 2. Class sessions table for online live classes (Jitsi / external)
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('provider')->default('jitsi'); // jitsi or external
            $table->string('meeting_url')->nullable();
            $table->string('room_code', 64)->unique();
            $table->string('status', 32)->default('scheduled'); // scheduled, live, ended
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['course_id', 'status', 'starts_at']);
        });

        // 3. Class attendance tracking table
        Schema::create('class_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('class_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('joined_at');
            $table->timestamps();

            $table->unique(['session_id', 'user_id']);
        });

        // 4. Calendar events table (course, personal, general)
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 32)->default('general'); // class, exam, personal, general
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete(); // for personal events
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['course_id', 'starts_at']);
            $table->index(['user_id', 'starts_at']);
            $table->index(['type', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('class_attendances');
        Schema::dropIfExists('class_sessions');
        Schema::dropIfExists('messages');
    }
};
