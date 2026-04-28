<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Track the formation produced by an AI generation.
 *
 * After validate (student) or import (admin), we record which Formation
 * was created from which AiGeneration. This lets us:
 *   1. Make validate/import idempotent (re-clicking returns to the existing
 *      formation instead of creating a duplicate).
 *   2. Detect orphaned generations vs. validated ones in the UI.
 *
 * `nullOnDelete` keeps the generation row alive even if the formation it
 * produced is later deleted by the admin/student. The `formation_id` simply
 * goes back to NULL.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_generations', function (Blueprint $table) {
            $table->foreignId('formation_id')
                ->nullable()
                ->after('user_id')
                ->constrained('formations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ai_generations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('formation_id');
        });
    }
};
