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
        Schema::table('danh_gia_cuu_ho', function (Blueprint $table) {
            $table->string('tags', 500)->nullable()->after('noi_dung_danh_gia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('danh_gia_cuu_ho', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
    }
};
