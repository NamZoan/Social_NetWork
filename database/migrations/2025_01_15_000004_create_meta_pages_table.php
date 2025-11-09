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
        Schema::create('meta_pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meta_account_id');
            $table->string('page_id')->unique(); // Facebook Page ID
            $table->string('page_name');
            $table->text('page_description')->nullable();
            $table->string('page_category')->nullable();
            $table->string('page_access_token')->nullable(); // Page access token
            $table->string('cover_photo_url')->nullable();
            $table->string('profile_picture_url')->nullable();
            $table->integer('followers_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->json('insights')->nullable(); // Lưu các metrics và insights
            $table->json('settings')->nullable(); // Các cài đặt cho trang
            $table->boolean('is_active')->default(true);
            $table->boolean('is_connected')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->foreign('meta_account_id')->references('id')->on('meta_accounts')->onDelete('cascade');
            $table->index('meta_account_id');
            $table->index('page_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_pages');
    }
};


