<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * This migration adds a new column 'mermaid_code' to the 'chapters' and 'sub_chapters' tables.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->text('mermaid_code')->nullable()->after('sources');
        });

        Schema::table('sub_chapters', function (Blueprint $table) {
            $table->text('mermaid_code')->nullable()->after('sources');
        });
    }

    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn('mermaid_code');
        });
        Schema::table('sub_chapters', function (Blueprint $table) {
            $table->dropColumn('mermaid_code');
        });
    }
};
