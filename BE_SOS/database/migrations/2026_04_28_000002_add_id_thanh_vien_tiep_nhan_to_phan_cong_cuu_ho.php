<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phan_cong_cuu_ho', function (Blueprint $table) {
            $table->unsignedBigInteger('id_thanh_vien_tiep_nhan')->nullable()->after('trang_thai_nhiem_vu');
            $table->foreign('id_thanh_vien_tiep_nhan')
                ->references('id_thanh_vien_doi')
                ->on('thanh_vien_doi')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('phan_cong_cuu_ho', function (Blueprint $table) {
            $table->dropForeign(['id_thanh_vien_tiep_nhan']);
            $table->dropColumn('id_thanh_vien_tiep_nhan');
        });
    }
};
