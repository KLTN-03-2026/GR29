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
        Schema::table('thanh_vien_doi', function (Blueprint $table) {
            $table->string('vai_tro_trong_doi', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thanh_vien_doi', function (Blueprint $table) {
            $table->integer('vai_tro_trong_doi')->nullable()->change();
        });
    }
};
