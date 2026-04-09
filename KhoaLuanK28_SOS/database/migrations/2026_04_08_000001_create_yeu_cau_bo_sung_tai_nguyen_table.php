<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yeu_cau_bo_sung_tai_nguyen', function (Blueprint $table) {
            $table->id('id_yeu_cau_bs');
            $table->unsignedBigInteger('id_doi_cuu_ho');
            $table->unsignedBigInteger('id_phan_cong')->nullable();
            $table->unsignedBigInteger('id_yeu_cau')->nullable();
            $table->text('noi_dung');
            $table->string('muc_do_khan_cap', 20)->default('MEDIUM');
            $table->string('trang_thai', 30)->default('CHO_XU_LY');
            $table->text('ghi_chu_dieu_phoi')->nullable();
            $table->timestamps();

            $table->foreign('id_doi_cuu_ho')->references('id_doi_cuu_ho')->on('doi_cuu_ho')->onDelete('cascade');
            $table->foreign('id_phan_cong')->references('id_phan_cong')->on('phan_cong_cuu_ho')->nullOnDelete();
            $table->foreign('id_yeu_cau')->references('id_yeu_cau')->on('yeu_cau_cuu_ho')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yeu_cau_bo_sung_tai_nguyen');
    }
};
