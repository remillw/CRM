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
        Schema::table('seo_queries', function (Blueprint $table) {
            $table->boolean('is_one_time')->default(false)->after('is_active');
            $table->timestamp('executed_at')->nullable()->after('is_one_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_queries', function (Blueprint $table) {
            $table->dropColumn(['is_one_time', 'executed_at']);
        });
    }
};