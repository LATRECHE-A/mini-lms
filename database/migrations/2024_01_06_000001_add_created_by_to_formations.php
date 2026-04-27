<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 * Adds created_by to track formation ownership (admin vs student-generated).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formations', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('formations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
