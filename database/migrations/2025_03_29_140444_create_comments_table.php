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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->unsignedInteger('user_id'); // Đảm bảo cùng kiểu dữ liệu với users.id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('content');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('parent_comment_id')->nullable();
            $table->foreign('parent_comment_id')->references('id')->on('comments')->onDelete('cascade');
            $table->boolean('is_hidden')->default(false);
            $table->string('attachment_url', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
