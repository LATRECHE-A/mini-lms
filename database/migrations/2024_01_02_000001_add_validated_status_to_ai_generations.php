<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Migration to add 'validated' status to ai_generations and a timestamp for when it was validated.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (config('database.default') === 'sqlite') {
            // SQLite: must rebuild table to change CHECK constraint
            DB::transaction(function () {
                // 1. Create new table with correct schema
                DB::statement("CREATE TABLE ai_generations_new (
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

                // 2. Copy existing data
                DB::statement("INSERT INTO ai_generations_new (id, user_id, prompt, generated_content, type, status, deleted_at, created_at, updated_at)
                    SELECT id, user_id, prompt, generated_content, type, status, deleted_at, created_at, updated_at
                    FROM ai_generations");

                // 3. Drop old table
                DB::statement("DROP TABLE ai_generations");

                // 4. Rename new table
                DB::statement("ALTER TABLE ai_generations_new RENAME TO ai_generations");

                // 5. Recreate indexes
                DB::statement("CREATE INDEX ai_generations_user_id_status_index ON ai_generations (user_id, status)");
            });
        } else {
            // MySQL: simple enum expansion + add column
            DB::statement("ALTER TABLE ai_generations MODIFY COLUMN status ENUM('draft','published','validated') NOT NULL DEFAULT 'draft'");

            if (!Schema::hasColumn('ai_generations', 'validated_at')) {
                Schema::table('ai_generations', function (Blueprint $table) {
                    $table->timestamp('validated_at')->nullable()->after('status');
                });
            }
        }
    }

    public function down(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::transaction(function () {
                DB::statement("CREATE TABLE ai_generations_old (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    prompt TEXT NOT NULL,
                    generated_content TEXT NOT NULL,
                    type TEXT NOT NULL DEFAULT 'mixed' CHECK (type IN ('course','quiz','mixed')),
                    status TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','published')),
                    deleted_at DATETIME NULL,
                    created_at DATETIME NULL,
                    updated_at DATETIME NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )");

                DB::statement("INSERT INTO ai_generations_old (id, user_id, prompt, generated_content, type, status, deleted_at, created_at, updated_at)
                    SELECT id, user_id, prompt, generated_content, type, status, deleted_at, created_at, updated_at
                    FROM ai_generations WHERE status IN ('draft','published')");

                DB::statement("DROP TABLE ai_generations");
                DB::statement("ALTER TABLE ai_generations_old RENAME TO ai_generations");
                DB::statement("CREATE INDEX ai_generations_user_id_status_index ON ai_generations (user_id, status)");
            });
        } else {
            Schema::table('ai_generations', function (Blueprint $table) {
                $table->dropColumn('validated_at');
            });
            DB::statement("ALTER TABLE ai_generations MODIFY COLUMN status ENUM('draft','published') NOT NULL DEFAULT 'draft'");
        }
    }
};
