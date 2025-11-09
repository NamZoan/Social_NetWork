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
        Schema::create('call_participants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('call_id')->constrained('calls')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users');
            $t->enum('role', ['caller', 'callee', 'member'])->default('member');
            $t->enum('state', ['invited', 'ringing', 'joined', 'left', 'declined', 'failed'])->default('ringing');
            $t->timestamp('joined_at')->nullable();
            $t->timestamp('left_at')->nullable();
            $t->timestamps();

            $t->unique(['call_id', 'user_id']);
            $t->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_participants');
    }
};
