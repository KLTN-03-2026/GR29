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
        Schema::create('rescue_reports', function (Blueprint $table) {
            $table->id('id_bao_cao');
            $table->unsignedBigInteger('id_yeu_cau');
            $table->unsignedBigInteger('id_doi_cuu_ho');
            $table->string('ket_qua', 20)->comment('HOAN_THANH | THAT_BAI');
            $table->text('ly_do_that_bai')->nullable();
            $table->string('hinh_anh')->nullable();
            $table->timestamps();

            $table->foreign('id_yeu_cau')->references('id_yeu_cau')->on('yeu_cau_cuu_ho')->onDelete('cascade');
            $table->foreign('id_doi_cuu_ho')->references('id_doi_cuu_ho')->on('doi_cuu_ho')->onDelete('cascade');

            $table->index('id_yeu_cau');
            $table->index('id_doi_cuu_ho');
            $table->index('ket_qua');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rescue_reports');
    }
};
