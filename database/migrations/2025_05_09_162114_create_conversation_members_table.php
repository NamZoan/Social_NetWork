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
        Schema::create('conversation_members', function (Blueprint $table) {
            $table->id(); // BIGINT PRIMARY KEY
            $table->unsignedBigInteger('conversation_id');
            $table->integer('user_id')->unsigned(); // INT(10) UNSIGNED
            $table->string('role', 20)->default('member');
            $table->timestamp('joined_at')->useCurrent();

            $table->unique(['conversation_id', 'user_id']);

            // Foreign keys
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_members');
    }
};
