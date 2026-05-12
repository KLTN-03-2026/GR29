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
        Schema::table('yeu_cau_cap_phat', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['id_doi_cuu_ho']);
            $table->dropForeign(['id_nguoi_yeu_cau']);
            $table->dropForeign(['id_nguoi_duyet']);

            // Rename the primary key column
            $table->renameColumn('id', 'id_cap_phat');
        });

        // Recreate foreign keys after renaming
        Schema::table('yeu_cau_cap_phat', function (Blueprint $table) {
            $table->foreign('id_doi_cuu_ho')->references('id_doi_cuu_ho')->on('doi_cuu_ho');
            $table->foreign('id_nguoi_yeu_cau')->references('id_thanh_vien_doi')->on('thanh_vien_doi');
            $table->foreign('id_nguoi_duyet')->references('id_admin')->on('admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yeu_cau_cap_phat', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['id_doi_cuu_ho']);
            $table->dropForeign(['id_nguoi_yeu_cau']);
            $table->dropForeign(['id_nguoi_duyet']);

            // Rename the primary key column back to 'id'
            $table->renameColumn('id_cap_phat', 'id');
        });

        // Recreate foreign keys after renaming
        Schema::table('yeu_cau_cap_phat', function (Blueprint $table) {
            $table->foreign('id_doi_cuu_ho')->references('id_doi_cuu_ho')->on('doi_cuu_ho');
            $table->foreign('id_nguoi_yeu_cau')->references('id_thanh_vien_doi')->on('thanh_vien_doi');
            $table->foreign('id_nguoi_duyet')->references('id_admin')->on('admin');
        });
    }
};
