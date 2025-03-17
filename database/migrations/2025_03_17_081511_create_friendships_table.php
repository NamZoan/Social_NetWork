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
        Schema::create('friendships', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('user_id_1')->unsigned();
            $table->integer('user_id_2')->unsigned();
            $table->string('status', 20)->comment("'pending', 'accepted', 'declined', 'blocked'");
            $table->timestamps();

            // Thêm khóa ngoại
            $table->foreign('user_id_1')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id_2')->references('id')->on('users')->onDelete('cascade');

            // Chỉ mục & ràng buộc
            $table->unique(['user_id_1', 'user_id_2']);
            $table->index(['user_id_1', 'status']);
            $table->index(['user_id_2', 'status']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};
