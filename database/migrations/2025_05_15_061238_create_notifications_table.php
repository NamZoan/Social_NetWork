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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type', 50); // 'friend_request', 'comment', 'like', 'tag', 'event', 'group_invite', etc.
            $table->bigInteger('reference_id'); // ID của đối tượng tham chiếu (post, comment, etc.)
            $table->string('reference_type', 50); // 'post', 'comment', 'friendship', etc.
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('message')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('action_url', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
