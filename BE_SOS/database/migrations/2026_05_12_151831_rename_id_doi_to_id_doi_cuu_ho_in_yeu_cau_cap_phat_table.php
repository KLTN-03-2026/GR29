<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('yeu_cau_cap_phat', function (Blueprint $table) {
            // Check if the old column exists before renaming
            if (Schema::hasColumn('yeu_cau_cap_phat', 'id_doi')) {
                $table->renameColumn('id_doi', 'id_doi_cuu_ho');
            }

            // Add missing slug_tai_nguyen column
            if (!Schema::hasColumn('yeu_cau_cap_phat', 'slug_tai_nguyen')) {
                $table->string('slug_tai_nguyen', 100)->after('id_nguoi_yeu_cau');
            }

            // Update so_luong_yeu_cau to unsigned integer if needed
            $table->unsignedInteger('so_luong_yeu_cau')->change();

            // Update ghi_chu to text if needed
            $table->text('ghi_chu')->nullable()->change();

            // Update trang_thai to varchar if needed
            $table->string('trang_thai', 32)->default('CHO_DUYET')->change();

            // Add foreign key constraint for id_doi_cuu_ho if it doesn't exist
            if (!collect(DB::select("SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'yeu_cau_cap_phat' AND COLUMN_NAME = 'id_doi_cuu_ho' AND REFERENCED_TABLE_NAME IS NOT NULL"))->count()) {
                $table->foreign('id_doi_cuu_ho')->references('id_doi_cuu_ho')->on('doi_cuu_ho')->cascadeOnDelete();
            }

            // Add foreign key constraint for id_nguoi_yeu_cau if it doesn't exist
            if (!collect(DB::select("SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'yeu_cau_cap_phat' AND COLUMN_NAME = 'id_nguoi_yeu_cau' AND REFERENCED_TABLE_NAME IS NOT NULL"))->count()) {
                $table->foreign('id_nguoi_yeu_cau')->references('id_thanh_vien_doi')->on('thanh_vien_doi')->cascadeOnDelete();
            }

            // Add foreign key constraint for id_nguoi_duyet if it doesn't exist
            if (!collect(DB::select("SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'yeu_cau_cap_phat' AND COLUMN_NAME = 'id_nguoi_duyet' AND REFERENCED_TABLE_NAME IS NOT NULL"))->count()) {
                $table->foreign('id_nguoi_duyet')->references('id_admin')->on('admin')->nullOnDelete();
            }

            // Drop the unused id_tai_nguyen column if it exists
            if (Schema::hasColumn('yeu_cau_cap_phat', 'id_tai_nguyen')) {
                $table->dropColumn('id_tai_nguyen');
            }

            // Add indexes only if they don't exist
            $indexes = collect(DB::select("SHOW INDEX FROM yeu_cau_cap_phat"));
            $indexNames = $indexes->pluck('Key_name')->toArray();

            if (!in_array('yeu_cau_cap_phat_trang_thai_created_at_index', $indexNames)) {
                $table->index(['trang_thai', 'created_at']);
            }

            if (!in_array('yeu_cau_cap_phat_id_doi_cuu_ho_trang_thai_index', $indexNames)) {
                $table->index(['id_doi_cuu_ho', 'trang_thai']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yeu_cau_cap_phat', function (Blueprint $table) {
            // Drop foreign key constraint if it exists
            $table->dropForeign(['id_doi_cuu_ho']);

            // Rename column back to original name
            if (Schema::hasColumn('yeu_cau_cap_phat', 'id_doi_cuu_ho')) {
                $table->renameColumn('id_doi_cuu_ho', 'id_doi');
            }
        });
    }
};
