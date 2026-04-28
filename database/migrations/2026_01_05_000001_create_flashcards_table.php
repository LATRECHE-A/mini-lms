<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Flashcards with SM-2 spaced repetition.
 * is_template: admin-created cards that get cloned to students on enrollment.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_chapter_id')->nullable()->constrained()->nullOnDelete();
            $table->text('question');
            $table->text('answer');
            $table->boolean('is_template')->default(false);
            // SM-2 fields (only meaningful for non-template cards)
            $table->unsignedTinyInteger('difficulty')->default(0);
            $table->timestamp('next_review_at')->nullable();
            $table->unsignedInteger('interval_days')->default(1);
            $table->decimal('ease_factor', 4, 2)->default(2.50);
            $table->unsignedInteger('review_count')->default(0);
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'next_review_at']);
            $table->index(['user_id', 'sub_chapter_id']);
            $table->index(['sub_chapter_id', 'is_template']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcards');
    }
};
