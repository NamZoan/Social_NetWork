<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('related_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('related_post_id');
            $table->float('similarity_score');
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('related_post_id')->references('id')->on('posts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('related_posts');
    }


    /**
     * Reverse the migrations.
     */

};
