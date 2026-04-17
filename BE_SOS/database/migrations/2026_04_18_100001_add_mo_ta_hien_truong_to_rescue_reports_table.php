<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rescue_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('rescue_reports', 'mo_ta_hien_truong')) {
                $table->text('mo_ta_hien_truong')->nullable()->after('hinh_anh');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rescue_reports', function (Blueprint $table) {
            if (Schema::hasColumn('rescue_reports', 'mo_ta_hien_truong')) {
                $table->dropColumn('mo_ta_hien_truong');
            }
        });
    }
};
