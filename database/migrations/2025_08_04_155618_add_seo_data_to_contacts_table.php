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
        Schema::table('contacts', function (Blueprint $table) {
            $table->integer('seo_position')->nullable()->after('can_command');
            $table->json('seo_data')->nullable()->after('seo_position');
            $table->timestamp('seo_analyzed_at')->nullable()->after('seo_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['seo_position', 'seo_data', 'seo_analyzed_at']);
        });
    }
};
