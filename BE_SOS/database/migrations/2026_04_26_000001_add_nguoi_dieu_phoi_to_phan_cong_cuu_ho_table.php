<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phan_cong_cuu_ho', function (Blueprint $table) {
            $table->string('nguoi_dieu_phoi', 255)->nullable()->after('mo_ta');
        });
    }

    public function down(): void
    {
        Schema::table('phan_cong_cuu_ho', function (Blueprint $table) {
            $table->dropColumn('nguoi_dieu_phoi');
        });
    }
};
