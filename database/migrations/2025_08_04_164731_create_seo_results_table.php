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
        Schema::create('seo_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_query_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->string('query_used'); // La requête exacte utilisée
            $table->integer('position')->nullable(); // Position trouvée (null si non trouvé)
            $table->string('url_found')->nullable(); // URL trouvée dans les résultats
            $table->json('serp_data')->nullable(); // Données complètes SERP
            $table->integer('total_results')->nullable(); // Nombre total de résultats
            $table->json('competitors')->nullable(); // Top 10 des concurrents
            $table->boolean('found')->default(false); // Trouvé ou non
            $table->timestamp('analyzed_at');
            $table->timestamps();
            
            $table->index(['seo_query_id', 'contact_id', 'analyzed_at']);
            $table->index(['contact_id', 'analyzed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_results');
    }
};
