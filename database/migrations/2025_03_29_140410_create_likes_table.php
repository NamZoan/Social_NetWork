<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id'); // Đảm bảo cùng kiểu dữ liệu với users.id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('content_type', 20);
            $table->unsignedBigInteger('content_id');
            $table->string('reaction_type', 20)->default('like');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['user_id', 'content_type', 'content_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
