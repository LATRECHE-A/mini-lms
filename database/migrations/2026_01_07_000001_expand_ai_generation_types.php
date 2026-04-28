<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Expands ai_generations.type to allow 'full'.
 *
 * The AI controllers store 'full' on the generation row as a marker meaning
 * "course + quiz + auto-generate flashcards on import". The original CHECK
 * constraint only permitted 'course','quiz','mixed', so the UPDATE that
 * promotes the row to 'full' violated the constraint.
 *
 * SQLite doesn't support modifying a CHECK constraint in place, so we
 * follow the standard rebuild-rename pattern. MySQL just gets an enum
 * widening.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::transaction(function () {
                DB::statement("CREATE TABLE ai_generations_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    prompt TEXT NOT NULL,
                    generated_content TEXT NOT NULL,
                    type TEXT NOT NULL DEFAULT 'mixed' CHECK (type IN ('course','quiz','mixed','full')),
                    status TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','published','validated')),
                    validated_at DATETIME NULL,
                    deleted_at DATETIME NULL,
                    created_at DATETIME NULL,
                    updated_at DATETIME NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )");

                DB::statement('INSERT INTO ai_generations_new
                    (id, user_id, prompt, generated_content, type, status, validated_at, deleted_at, created_at, updated_at)
                    SELECT id, user_id, prompt, generated_content, type, status, validated_at, deleted_at, created_at, updated_at
                    FROM ai_generations');

                DB::statement('DROP TABLE ai_generations');
                DB::statement('ALTER TABLE ai_generations_new RENAME TO ai_generations');
                DB::statement('CREATE INDEX ai_generations_user_id_status_index ON ai_generations (user_id, status)');
            });
        } else {
            DB::statement("ALTER TABLE ai_generations MODIFY COLUMN type ENUM('course','quiz','mixed','full') NOT NULL DEFAULT 'mixed'");
        }
    }

    public function down(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::transaction(function () {
                // Coerce any 'full' rows back to 'mixed' before tightening the constraint.
                DB::statement("UPDATE ai_generations SET type = 'mixed' WHERE type = 'full'");

                DB::statement("CREATE TABLE ai_generations_old (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    prompt TEXT NOT NULL,
                    generated_content TEXT NOT NULL,
                    type TEXT NOT NULL DEFAULT 'mixed' CHECK (type IN ('course','quiz','mixed')),
                    status TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','published','validated')),
                    validated_at DATETIME NULL,
                    deleted_at DATETIME NULL,
                    created_at DATETIME NULL,
                    updated_at DATETIME NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )");

                DB::statement('INSERT INTO ai_generations_old
                    (id, user_id, prompt, generated_content, type, status, validated_at, deleted_at, created_at, updated_at)
                    SELECT id, user_id, prompt, generated_content, type, status, validated_at, deleted_at, created_at, updated_at
                    FROM ai_generations');

                DB::statement('DROP TABLE ai_generations');
                DB::statement('ALTER TABLE ai_generations_old RENAME TO ai_generations');
                DB::statement('CREATE INDEX ai_generations_user_id_status_index ON ai_generations (user_id, status)');
            });
        } else {
            DB::statement("UPDATE ai_generations SET type = 'mixed' WHERE type = 'full'");
            DB::statement("ALTER TABLE ai_generations MODIFY COLUMN type ENUM('course','quiz','mixed') NOT NULL DEFAULT 'mixed'");
        }
    }
};
