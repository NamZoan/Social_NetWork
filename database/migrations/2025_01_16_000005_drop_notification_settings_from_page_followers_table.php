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
        if (Schema::hasColumn('page_followers', 'notification_settings')) {
            Schema::table('page_followers', function (Blueprint $table) {
                $table->dropColumn('notification_settings');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('page_followers', 'notification_settings')) {
            Schema::table('page_followers', function (Blueprint $table) {
                $table->enum('notification_settings', ['all', 'highlights', 'none'])->default('all');
            });
        }
    }
};

