<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('yeu_cau_cap_phat')) {
            Schema::create('yeu_cau_cap_phat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_doi_cuu_ho');
            $table->unsignedBigInteger('id_nguoi_yeu_cau');
            $table->string('slug_tai_nguyen', 100);
            $table->unsignedInteger('so_luong_yeu_cau');
            $table->text('ghi_chu')->nullable();
            $table->string('trang_thai', 32)->default('CHO_DUYET');
            $table->unsignedBigInteger('id_nguoi_duyet')->nullable();
            $table->timestamp('thoi_gian_duyet')->nullable();
            $table->timestamps();

            $table->foreign('id_doi_cuu_ho')->references('id_doi_cuu_ho')->on('doi_cuu_ho')->cascadeOnDelete();
            $table->foreign('id_nguoi_yeu_cau')->references('id_thanh_vien_doi')->on('thanh_vien_doi')->cascadeOnDelete();
            $table->foreign('id_nguoi_duyet')->references('id_admin')->on('admin')->nullOnDelete();
            $table->index(['trang_thai', 'created_at']);
            $table->index(['id_doi_cuu_ho', 'trang_thai']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('yeu_cau_cap_phat');
    }
};
