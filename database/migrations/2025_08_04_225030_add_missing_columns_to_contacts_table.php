<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            // Ajouter les colonnes manquantes si elles n'existent pas
            if (!Schema::hasColumn('contacts', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('contacts', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('city');
            }
            if (!Schema::hasColumn('contacts', 'activity_type')) {
                $table->string('activity_type')->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('contacts', 'notes')) {
                $table->text('notes')->nullable()->after('can_command');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $columns = ['city', 'postal_code', 'activity_type', 'notes'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('contacts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};