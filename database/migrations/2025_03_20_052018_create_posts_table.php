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
        Schema::create('posts', function (Blueprint $table) {
            $table->id(); // Tạo khóa chính
            $table->unsignedInteger('user_id'); // INT UNSIGNED
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('content'); // Nội dung bài viết
            
            $table->enum('privacy_setting', ['public', 'friends', 'private'])
                  ->default('public'); // Cài đặt quyền riêng tư
            
            $table->boolean('allow_comments')->default(true); // Cho phép bình luận hay không
            
            $table->foreignId('original_post_id')->nullable()->constrained('posts')->onDelete('cascade'); // Bài viết gốc nếu là bài chia sẻ
            
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('cascade'); // Nếu bài viết thuộc nhóm
            $table->timestamps(); // created_at và updated_at tự động

            // Thêm index để tăng tốc truy vấn nếu cần
            $table->index(['user_id', 'privacy_setting']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
