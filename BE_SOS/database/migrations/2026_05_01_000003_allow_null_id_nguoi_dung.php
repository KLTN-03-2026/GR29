<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sửa bảng yeu_cau_cuu_ho: cho phép id_nguoi_dung NULL (để hỗ trợ guest gửi yêu cầu chưa đăng nhập)
     */
    public function up(): void
    {
        // Lấy tên foreign key (Laravel đặt tên theo convention: table_foreignColumn)
        $fkName = $this->findForeignKeyName('yeu_cau_cuu_ho', 'id_nguoi_dung');

        if ($fkName) {
            Schema::table('yeu_cau_cuu_ho', function (Blueprint $table) use ($fkName) {
                $table->dropForeign($fkName);
            });
        }

        // Sửa cột: cho phép NULL
        Schema::table('yeu_cau_cuu_ho', function (Blueprint $table) {
            $table->unsignedBigInteger('id_nguoi_dung')->nullable()->change();
        });

        // Thêm lại foreign key (ON DELETE SET NULL để khi xóa user, yêu cầu vẫn giữ nguyên)
        Schema::table('yeu_cau_cuu_ho', function (Blueprint $table) {
            $table->foreign('id_nguoi_dung')
                ->references('id_nguoi_dung')
                ->on('nguoi_dung')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $fkName = $this->findForeignKeyName('yeu_cau_cuu_ho', 'id_nguoi_dung');

        if ($fkName) {
            Schema::table('yeu_cau_cuu_ho', function (Blueprint $table) use ($fkName) {
                $table->dropForeign($fkName);
            });
        }

        Schema::table('yeu_cau_cuu_ho', function (Blueprint $table) {
            $table->unsignedBigInteger('id_nguoi_dung')->change();
        });

        Schema::table('yeu_cau_cuu_ho', function (Blueprint $table) {
            $table->foreign('id_nguoi_dung')
                ->references('id_nguoi_dung')
                ->on('nguoi_dung')
                ->onDelete('cascade');
        });
    }

    /**
     * Tìm tên foreign key trên một cột
     */
    private function findForeignKeyName(string $table, string $column): ?string
    {
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table, $column]);

        return $foreignKeys[0]->CONSTRAINT_NAME ?? null;
    }
};
