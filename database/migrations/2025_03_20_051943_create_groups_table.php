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
        Schema::create('groups', function (Blueprint $table) {
            $table->id(); // ID chính (INT UNSIGNED AUTO_INCREMENT)
            $table->string('name', 255); // Tên nhóm
            $table->text('description')->nullable(); // Mô tả nhóm

            $table->unsignedInteger('creator_id'); // INT UNSIGNED cho creator_id
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps(); // created_at & updated_at

            $table->tinyInteger('privacy_setting')->default(0); // 0 = public, 1 = closed, 2 = secret
            $table->string('cover_photo_url', 255)->nullable(); // Ảnh bìa
            $table->string('group_type', 50)->nullable(); // Loại nhóm
            $table->tinyInteger('post_approval_required')->default(0); // 0 = không cần, 1 = cần
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
