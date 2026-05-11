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
        Schema::create('yeu_cau_cap_phat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_doi');
            $table->unsignedBigInteger('id_nguoi_yeu_cau');
            $table->unsignedBigInteger('id_tai_nguyen');
            $table->integer('so_luong_yeu_cau');
            $table->string('ghi_chu')->nullable();
            $table->enum('trang_thai', ['CHO_DUYET', 'DA_CAP_PHAT', 'TU_CHOI'])->default('CHO_DUYET');
            $table->unsignedBigInteger('id_nguoi_duyet')->nullable();
            $table->timestamp('thoi_gian_duyet')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yeu_cau_cap_phat');
    }
};
