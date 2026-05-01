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
        Schema::create('guest_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 100)->unique()->comment('UUID thiết bị của guest');
            $table->string('so_dien_thoai', 20)->nullable()->comment('SĐT người dùng nhập khi gửi yêu cầu');
            $table->string('guest_name', 255)->nullable()->comment('Tên tạm của guest');
            $table->boolean('is_linked')->default(false)->comment('Đã link vào tài khoản chưa');
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->index('device_id');
            $table->index('so_dien_thoai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_sessions');
    }
};
