<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_interactions', function (Blueprint $table) {
            $table->id();
            
            // Khóa ngoại liên kết với bảng users
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Khóa ngoại liên kết với bảng posts
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            
            // Loại tương tác (Enum)
            $table->enum('interaction_type', [
                'view', 'click', 'like', 'share', 'comment', 'skip', 'profile_visit'
            ]);

            // Quan trọng: Thêm timestamp để biết hành động xảy ra khi nào (phục vụ AI)
            $table->timestamps(); 

            // Tạo index cho cặp (user_id, post_id) để truy vấn nhanh hơn
            $table->index(['user_id', 'post_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_interactions');
    }
};