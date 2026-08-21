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
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->string('label')->unique(); // e.g. '2026-2027'
            $table->boolean('is_active')->default(false);
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });

        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. 'Kindergarten', 'Grade 1'
            $table->unsignedTinyInteger('order')->unique(); // 0 for Kinder, 1-6
            $table->timestamps();
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. 'Mabini', 'Rizal'
            $table->foreignId('adviser_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['school_year_id', 'grade_level_id', 'name']);
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();
            $table->unique(['grade_level_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('grade_levels');
        Schema::dropIfExists('school_years');
    }
};
