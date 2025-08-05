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
        Schema::create('seo_queries', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de la requête ex: "Pizzeria Marseille"
            $table->string('query'); // La requête exacte ex: "pizzeria marseille"
            $table->string('location')->nullable(); // Localisation ex: "Marseille, France"
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->boolean('is_active')->default(true);
            $table->json('target_campaigns')->nullable(); // IDs des campagnes ciblées
            $table->text('description')->nullable();
            $table->timestamp('last_analyzed_at')->nullable();
            $table->timestamp('next_analysis_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_queries');
    }
};
