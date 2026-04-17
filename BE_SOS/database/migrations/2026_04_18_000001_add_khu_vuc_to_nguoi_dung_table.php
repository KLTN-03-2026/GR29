<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            if (!Schema::hasColumn('nguoi_dung', 'khu_vuc')) {
                $table->string('khu_vuc', 255)->nullable()->after('trang_thai');
            }
            if (!Schema::hasColumn('nguoi_dung', 'dia_chi')) {
                $table->string('dia_chi', 500)->nullable()->after('khu_vuc');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            if (Schema::hasColumn('nguoi_dung', 'khu_vuc')) {
                $table->dropColumn('khu_vuc');
            }
            if (Schema::hasColumn('nguoi_dung', 'dia_chi')) {
                $table->dropColumn('dia_chi');
            }
        });
    }
};
