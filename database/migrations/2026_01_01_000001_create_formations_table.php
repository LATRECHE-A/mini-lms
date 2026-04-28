<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Migration to create the 'formations' table with fields for name, description, level, duration, status, and timestamps.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('level', ['débutant', 'intermédiaire', 'avancé'])->default('débutant');
            $table->integer('duration_hours')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft')->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
