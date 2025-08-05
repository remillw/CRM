<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_queries', function (Blueprint $table) {
            $table->integer('max_pages')->default(20)->after('location')
                ->comment('Nombre de pages Google à scanner (défaut: 20 = 200 positions)');
        });
    }

    public function down(): void
    {
        Schema::table('seo_queries', function (Blueprint $table) {
            $table->dropColumn('max_pages');
        });
    }
};