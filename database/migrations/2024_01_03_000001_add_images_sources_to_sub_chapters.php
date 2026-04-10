<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Migration to add image and sources fields to sub_chapters table.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_chapters', function (Blueprint $table) {
            $table->string('image_url', 2048)->nullable()->after('content');
            $table->string('image_alt', 512)->nullable()->after('image_url');
            $table->string('image_credit', 512)->nullable()->after('image_alt');
            $table->json('sources')->nullable()->after('image_credit');
        });
    }

    public function down(): void
    {
        Schema::table('sub_chapters', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'image_alt', 'image_credit', 'sources']);
        });
    }
};
