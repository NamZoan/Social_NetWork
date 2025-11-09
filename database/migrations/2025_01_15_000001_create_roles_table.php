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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // administrator, editor, advertiser, analyst, moderator
            $table->string('display_name'); // Quản trị viên, Biên tập viên, Nhà quảng cáo, Nhà phân tích, Người kiểm duyệt
            $table->text('description')->nullable();
            $table->json('permissions')->nullable(); // Danh sách quyền dưới dạng JSON
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};


