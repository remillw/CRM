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
        Schema::create('contact_list_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('contact_lists')->onDelete('cascade');
            $table->string('name');
            $table->json('conditions');
            $table->integer('contact_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_list_segments');
    }
};
