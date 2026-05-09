<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kho_tai_nguyen', function (Blueprint $table) {
            $table->id('id_tai_nguyen');
            $table->string('slug_tai_nguyen', 100)->unique();
            $table->string('ten_tai_nguyen', 255);
            $table->integer('so_luong')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kho_tai_nguyen');
    }
};
