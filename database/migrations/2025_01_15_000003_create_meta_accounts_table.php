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
        Schema::create('meta_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('business_id')->nullable(); // Meta Business ID
            $table->string('account_id')->nullable(); // Meta Business Account ID
            $table->string('access_token')->nullable(); // OAuth access token
            $table->string('refresh_token')->nullable(); // OAuth refresh token
            $table->timestamp('token_expires_at')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_type')->nullable(); // business, personal
            $table->json('settings')->nullable(); // Các cài đặt tùy chỉnh
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_accounts');
    }
};


