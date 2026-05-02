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
        Schema::table('yeu_cau_cuu_ho', function (Blueprint $table) {
            $table->string('device_id', 100)->nullable()->after('id_nguoi_dung')
                ->comment('UUID thiết bị của guest đã gửi yêu cầu');
            $table->foreignId('guest_session_id')
                ->nullable()
                ->after('device_id')
                ->constrained('guest_sessions')
                ->onDelete('set null')
                ->comment('Link tới guest_session nếu là guest chưa đăng nhập');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yeu_cau_cuu_ho', function (Blueprint $table) {
            $table->dropForeign(['guest_session_id']);
            $table->dropColumn(['device_id', 'guest_session_id']);
        });
    }
};
