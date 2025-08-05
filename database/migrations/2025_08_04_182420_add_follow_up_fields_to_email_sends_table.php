<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_sends', function (Blueprint $table) {
            $table->string('template_name')->nullable()->after('tracking_id');
            $table->timestamp('follow_up_sent_at')->nullable()->after('unsubscribed_at');
            $table->string('follow_up_type')->nullable()->after('follow_up_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('email_sends', function (Blueprint $table) {
            $table->dropColumn(['template_name', 'follow_up_sent_at', 'follow_up_type']);
        });
    }
};