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
        Schema::create('contact_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('list_id')->constrained('contact_lists')->onDelete('cascade');
            $table->foreignId('segment_id')->nullable()->constrained('contact_list_segments')->onDelete('set null');
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();
            
            $table->unique(['contact_id', 'list_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_list_items');
    }
};
