<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('subject_code');
            $table->string('title');
            $table->string('program');          // e.g. BSIT, BSCrim, BSMidwife
            $table->unsignedTinyInteger('year_level')->default(1);
            $table->string('section')->nullable();
            $table->string('block')->nullable();
            $table->string('instructor')->nullable();
            $table->json('schedules');          // [{day, time_start, time_end, type, room}]
            $table->string('fused_with')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
