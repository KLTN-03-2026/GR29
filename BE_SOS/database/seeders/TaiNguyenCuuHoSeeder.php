<?php

namespace Database\Seeders;

use App\Models\TaiNguyenCuuHo;
use App\Models\DoiCuuHo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaiNguyenCuuHoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $dois = DoiCuuHo::orderBy('id_doi_cuu_ho')->get();
        $count = 0;

        // Dữ liệu chuẩn theo @BE/data/MAU_DU_LIEU_DOI_CUU_HO.md (mục 5)
        // Tăng số lượng để đủ demo "toàn TP Đà Nẵng"
        $scaleByType = [
            'Vehicle' => 3,
            'Equipment' => 5,
            'Medical' => 5,
            'Communication' => 5,
            'Water' => 3,
            'Other' => 3,
        ];

        $byTeamIndex = [
            1 => [
                ['ten' => 'Thuyền cứu hộ nhôm', 'loai' => 'Equipment', 'sl' => 3],
                ['ten' => 'Máy bơm chìm công suất lớn', 'loai' => 'Equipment', 'sl' => 2],
                ['ten' => 'Xe bán tải chở thiết bị', 'loai' => 'Vehicle', 'sl' => 2],
                ['ten' => 'Bộ đàm công suất', 'loai' => 'Communication', 'sl' => 6],
            ],
            2 => [
                ['ten' => 'Dây neo — móng chống lở', 'loai' => 'Equipment', 'sl' => 10],
                ['ten' => 'Mũ cứng — dây an toàn', 'loai' => 'Equipment', 'sl' => 12],
                ['ten' => 'Xe chở đội leo núi', 'loai' => 'Vehicle', 'sl' => 1],
            ],
            3 => [
                ['ten' => 'Xe cứu thương', 'loai' => 'Vehicle', 'sl' => 1],
                ['ten' => 'Túi sơ cứu loại A', 'loai' => 'Medical', 'sl' => 8],
                ['ten' => 'Bình oxygen di động', 'loai' => 'Medical', 'sl' => 2],
            ],
            4 => [
                ['ten' => 'Máy cưa xích', 'loai' => 'Equipment', 'sl' => 4],
                ['ten' => 'Lều cứu hộ 20 người', 'loai' => 'Equipment', 'sl' => 3],
                ['ten' => 'Máy phát điện 5kVA', 'loai' => 'Equipment', 'sl' => 2],
            ],
            5 => [
                ['ten' => 'Đèn pin đội đầu', 'loai' => 'Equipment', 'sl' => 15],
                ['ten' => 'Dụng cụ phá dỡ nhẹ', 'loai' => 'Equipment', 'sl' => 2],
                ['ten' => 'Loa cầm tay', 'loai' => 'Communication', 'sl' => 4],
            ],
            6 => [
                ['ten' => 'Xe bồn 5m³', 'loai' => 'Vehicle', 'sl' => 1],
                ['ten' => 'Hệ thống lọc nước di động', 'loai' => 'Water', 'sl' => 2],
                ['ten' => 'Bồn chứa nước gấp', 'loai' => 'Equipment', 'sl' => 4],
            ],
            7 => [
                ['ten' => 'Xe địa hình', 'loai' => 'Vehicle', 'sl' => 2],
                ['ten' => 'Thang cứu hộ', 'loai' => 'Equipment', 'sl' => 2],
                ['ten' => 'Máy bơm dã chiến', 'loai' => 'Equipment', 'sl' => 2],
            ],
            8 => [
                ['ten' => 'Container thiết bị dự phòng', 'loai' => 'Equipment', 'sl' => 1],
                ['ten' => 'Xe chở đội 16 chỗ', 'loai' => 'Vehicle', 'sl' => 2],
                ['ten' => 'Bộ đàm dự phòng', 'loai' => 'Communication', 'sl' => 10],
            ],
        ];

        $teamIdx = 1;
        foreach ($dois as $doi) {
            $rows = $byTeamIndex[$teamIdx] ?? [
                ['ten' => 'Xe cứu hộ', 'loai' => 'Vehicle', 'sl' => 2],
                ['ten' => 'Bộ đàm', 'loai' => 'Communication', 'sl' => 6],
                ['ten' => 'Túi sơ cứu', 'loai' => 'Medical', 'sl' => 6],
            ];

            foreach ($rows as $r) {
                $mult = $scaleByType[$r['loai']] ?? 3;
                $qty = (int) $r['sl'] * (int) $mult;

                TaiNguyenCuuHo::updateOrCreate(
                    [
                        'id_doi_cuu_ho' => $doi->id_doi_cuu_ho,
                        'ten_tai_nguyen' => $r['ten'],
                    ],
                    [
                        'loai_tai_nguyen' => $r['loai'],
                        'so_luong' => $qty,
                        'trang_thai' => 1,
                    ]
                );
                $count++;
            }

            $teamIdx++;
        }

        echo "✅ Tài Nguyên Cứu Hộ Seeding: {$count} dòng tài nguyên (tăng số lượng cho demo Đà Nẵng)\n";
    }
}
