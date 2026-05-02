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
        Schema::table('chi_tiet_loai_su_co', function (Blueprint $table) {
            $table->unsignedTinyInteger('diem_uu_tien')->default(0)->after('mo_ta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chi_tiet_loai_su_co', function (Blueprint $table) {
            $table->dropColumn('diem_uu_tien');
        });
    }
};
