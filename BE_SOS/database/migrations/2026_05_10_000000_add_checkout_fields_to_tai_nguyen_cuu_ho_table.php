<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tai_nguyen_cuu_ho', function (Blueprint $table) {
            $table->unsignedBigInteger('dang_su_dung_cho_nhiem_vu')
                  ->nullable()
                  ->after('trang_thai');
            $table->integer('so_luong_dang_su_dung')
                  ->default(0)
                  ->after('dang_su_dung_cho_nhiem_vu');
        });
    }

    public function down(): void
    {
        Schema::table('tai_nguyen_cuu_ho', function (Blueprint $table) {
            $table->dropColumn(['dang_su_dung_cho_nhiem_vu', 'so_luong_dang_su_dung']);
        });
    }
};
