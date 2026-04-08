<?php

namespace Database\Seeders;

use App\Models\PhanCongCuuHo;
use App\Models\YeuCauCuuHo;
use App\Models\DoiCuuHo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PhanCongCuuHoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $requestsWaiting = YeuCauCuuHo::where('trang_thai', 'CHO_XU_LY')->get();
        $requestsProcessing = YeuCauCuuHo::where('trang_thai', 'DANG_XU_LY')
            ->orWhere('trang_thai', 'HOAN_THANH')
            ->get();
        $teams = DoiCuuHo::all();
        $count = 0;

        if ($teams->isEmpty()) {
            echo "⚠️ Không có đội cứu hộ, bỏ qua seeding phân công.\n";
            return;
        }

        // 1) Tạo nhiệm vụ MOI để đội vào trang home thấy và bấm TIẾP NHẬN
        foreach ($requestsWaiting->take(6) as $request) {
            PhanCongCuuHo::updateOrCreate([
                'id_yeu_cau' => $request->id_yeu_cau,
            ], [
                'id_yeu_cau' => $request->id_yeu_cau,
                'id_doi_cuu_ho' => $teams->first()->id_doi_cuu_ho, 
                'mo_ta' => "Phân công MỚI xử lý sự cố #{$request->id_yeu_cau}",
                'thoi_gian_phan_cong' => now()->subMinutes(rand(10, 120)),
                'trang_thai_nhiem_vu' => 'MOI'
            ]);
            $count++;
        }

        // 2) Tạo dữ liệu đang xử lý / hoàn thành cho dashboard admin
        foreach ($requestsProcessing->take(10) as $request) {
            PhanCongCuuHo::updateOrCreate([
                'id_yeu_cau' => $request->id_yeu_cau,
            ], [
                'id_yeu_cau' => $request->id_yeu_cau,
                'id_doi_cuu_ho' => $teams->random()->id_doi_cuu_ho,
                'mo_ta' => "Phân công xử lý sự cố #{$request->id_yeu_cau}",
                'thoi_gian_phan_cong' => now()->subHours(rand(1, 48)),
                'trang_thai_nhiem_vu' => $request->trang_thai === 'DANG_XU_LY' ? 'DANG_XU_LY' : 'HOAN_THANH'
            ]);
            $count++;
        }

        echo "✅ Phân Công Cứu Hộ Seeding: {$count} phân công\n";
    }
}
