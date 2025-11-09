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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('creator_id'); // User tạo trang
            $table->string('name');
            $table->string('username')->unique()->nullable();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->string('profile_picture_url')->nullable();
            $table->string('cover_photo_url')->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('location')->nullable(); // {address, city, country, latitude, longitude}
            $table->json('hours_of_operation')->nullable(); // Giờ mở cửa
            $table->boolean('verified')->default(false);
            $table->bigInteger('follower_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('creator_id');
            $table->index('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};


