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
            $table->renameColumn('id', 'id_cap_phat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yeu_cau_cap_phat', function (Blueprint $table) {
            $table->renameColumn('id_cap_phat', 'id');
        });
    }
};
