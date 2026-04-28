<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Backfill formations.created_by for student-imported AI formations.
 *
 * The importer already logs `formation.imported_from_ai` to activity_logs
 * with the student's user_id and the formation's id. This migration uses
 * those logs as the source of truth for ownership of any formation row
 * that still has created_by IS NULL.
 *
 * Idempotent and safe to run on a fresh DB: if there are no matching
 * activity_logs rows, nothing changes.
 */

use App\Models\Formation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_logs') || ! Schema::hasTable('formations')) {
            return;
        }
        if (! Schema::hasColumn('formations', 'created_by')) {
            return;
        }

        $logs = DB::table('activity_logs')
            ->where('action', 'formation.imported_from_ai')
            ->where('subject_type', Formation::class)
            ->whereNotNull('subject_id')
            ->whereNotNull('user_id')
            ->select('user_id', 'subject_id')
            ->get();

        foreach ($logs as $log) {
            DB::table('formations')
                ->where('id', $log->subject_id)
                ->whereNull('created_by')
                ->update(['created_by' => $log->user_id]);
        }
    }

    public function down(): void
    {
        // No-op: we don't undo a data backfill.
    }
};
