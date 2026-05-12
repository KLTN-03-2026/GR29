<?php

namespace App\Services;

use App\Events\YeuCauCapPhatRealtime;
use App\Models\Admin;
use App\Models\KhoTaiNguyen;
use App\Models\TaiNguyenCuuHo;
use App\Models\ThanhVienDoi;
use App\Models\YeuCauCapPhat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class YeuCauCapPhatService
{
    /** @return array<int, string> */
    public function layDanhSachSlugKhoHopLe(): array
    {
        return ['xe_cuu_ho', 'nhu_yeu_pham', 'vat_tu_y_te', 'dung_cu_thi_cong'];
    }

    public function layTenHienThiLoai(string $slug): string
    {
        $map = [
            'xe_cuu_ho' => 'Xe cứu hộ',
            'nhu_yeu_pham' => 'Nhu yếu phẩm',
            'vat_tu_y_te' => 'Vật tư y tế',
            'dung_cu_thi_cong' => 'Dụng cụ thi công',
        ];

        return $map[$slug] ?? $slug;
    }

    public function taoYeuCauTuRescuer(ThanhVienDoi $thanhVien, string $slugTaiNguyen, int $soLuongYeuCau, ?string $ghiChu): YeuCauCapPhat
    {
        if (! in_array($slugTaiNguyen, $this->layDanhSachSlugKhoHopLe(), true)) {
            throw ValidationException::withMessages([
                'slug_tai_nguyen' => ['Loại tài nguyên không hợp lệ.'],
            ]);
        }

        if ($soLuongYeuCau < 1 || $soLuongYeuCau > 10000) {
            throw ValidationException::withMessages([
                'so_luong_yeu_cau' => ['Số lượng yêu cầu phải từ 1 đến 10000.'],
            ]);
        }

        $idDoi = (int) $thanhVien->id_doi_cuu_ho;
        if ($idDoi <= 0) {
            throw ValidationException::withMessages([
                'doi' => ['Thành viên chưa được gán vào đội cứu hộ.'],
            ]);
        }

        $yeuCau = YeuCauCapPhat::create([
            'id_doi_cuu_ho' => $idDoi,
            'id_nguoi_yeu_cau' => $thanhVien->id_thanh_vien_doi,
            'slug_tai_nguyen' => $slugTaiNguyen,
            'so_luong_yeu_cau' => $soLuongYeuCau,
            'ghi_chu' => $ghiChu,
            'trang_thai' => YeuCauCapPhat::TRANG_THAI_CHO_DUYET,
        ]);

        $yeuCau->load(['doiCuuHo', 'nguoiYeuCau']);

        event(new YeuCauCapPhatRealtime('tao_moi', $this->dongGoiBanGhi($yeuCau)));

        return $yeuCau;
    }

    public function capPhatBoiAdmin(int $idYeuCau, Admin $admin): YeuCauCapPhat
    {
        return DB::transaction(function () use ($idYeuCau, $admin) {
            /** @var YeuCauCapPhat $yeuCau */
            $yeuCau = YeuCauCapPhat::query()
                ->whereKey($idYeuCau)
                ->lockForUpdate()
                ->firstOrFail();

            if ($yeuCau->trang_thai !== YeuCauCapPhat::TRANG_THAI_CHO_DUYET) {
                throw ValidationException::withMessages([
                    'trang_thai' => ['Yêu cầu đã được xử lý trước đó.'],
                ]);
            }

            $slug = $yeuCau->slug_tai_nguyen;
            $soLuong = (int) $yeuCau->so_luong_yeu_cau;

            /** @var KhoTaiNguyen|null $kho */
            $kho = KhoTaiNguyen::query()
                ->where('slug_tai_nguyen', $slug)
                ->lockForUpdate()
                ->first();

            $tonKho = $kho ? (int) $kho->so_luong : 0;
            if ($tonKho < $soLuong) {
                throw ValidationException::withMessages([
                    'kho' => ['Kho không đủ. Tồn hiện tại: '.$tonKho.'.'],
                ]);
            }

            if ($kho) {
                $kho->so_luong = $tonKho - $soLuong;
                if ($kho->so_luong < 0) {
                    throw ValidationException::withMessages(['kho' => ['Lỗi đồng bộ kho, vui lòng thử lại.']]);
                }
                $kho->save();
            }

            $idDoi = (int) $yeuCau->id_doi_cuu_ho;
            $tenHienThi = $this->layTenHienThiLoai($slug);

            $taiNguyenDoi = TaiNguyenCuuHo::query()
                ->where('id_doi_cuu_ho', $idDoi)
                ->where('slug_tai_nguyen', $slug)
                ->lockForUpdate()
                ->first();

            if ($taiNguyenDoi) {
                $taiNguyenDoi->so_luong = (int) $taiNguyenDoi->so_luong + $soLuong;
                $taiNguyenDoi->save();
            } else {
                TaiNguyenCuuHo::create([
                    'id_doi_cuu_ho' => $idDoi,
                    'ten_tai_nguyen' => $tenHienThi,
                    'slug_tai_nguyen' => $slug,
                    'so_luong' => $soLuong,
                    'trang_thai' => 1,
                ]);
            }

            $yeuCau->trang_thai = YeuCauCapPhat::TRANG_THAI_DA_CAP_PHAT;
            $yeuCau->id_nguoi_duyet = $admin->id_admin;
            $yeuCau->thoi_gian_duyet = now();
            $yeuCau->save();

            $yeuCau->load(['doiCuuHo', 'nguoiYeuCau', 'nguoiDuyet']);

            event(new YeuCauCapPhatRealtime('cap_nhat', $this->dongGoiBanGhi($yeuCau)));

            return $yeuCau;
        });
    }

    public function tuChoiBoiAdmin(int $idYeuCau, Admin $admin, ?string $ghiChuTuChoi = null): YeuCauCapPhat
    {
        return DB::transaction(function () use ($idYeuCau, $admin, $ghiChuTuChoi) {
            /** @var YeuCauCapPhat $yeuCau */
            $yeuCau = YeuCauCapPhat::query()
                ->whereKey($idYeuCau)
                ->lockForUpdate()
                ->firstOrFail();

            if ($yeuCau->trang_thai !== YeuCauCapPhat::TRANG_THAI_CHO_DUYET) {
                throw ValidationException::withMessages([
                    'trang_thai' => ['Yêu cầu đã được xử lý trước đó.'],
                ]);
            }

            $yeuCau->trang_thai = YeuCauCapPhat::TRANG_THAI_TU_CHOI;
            $yeuCau->id_nguoi_duyet = $admin->id_admin;
            $yeuCau->thoi_gian_duyet = now();
            if ($ghiChuTuChoi !== null && $ghiChuTuChoi !== '') {
                $gop = trim((string) ($yeuCau->ghi_chu ?? '')."\n[Từ chối]: ".$ghiChuTuChoi);
                $yeuCau->ghi_chu = Str::limit($gop, 2000, '');
            }
            $yeuCau->save();

            $yeuCau->load(['doiCuuHo', 'nguoiYeuCau', 'nguoiDuyet']);

            event(new YeuCauCapPhatRealtime('cap_nhat', $this->dongGoiBanGhi($yeuCau)));

            return $yeuCau;
        });
    }

    /**
     * @param  array<string, mixed>  $boLoc
     */
    public function layDanhSachChoAdmin(array $boLoc = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $trangThai = $boLoc['trang_thai'] ?? null;
        $idDoi = $boLoc['id_doi_cuu_ho'] ?? null;

        $q = YeuCauCapPhat::query()
            ->with(['doiCuuHo', 'nguoiYeuCau', 'nguoiDuyet'])
            ->orderByDesc('created_at');

        if ($trangThai) {
            $q->where('trang_thai', $trangThai);
        }

        if ($idDoi) {
            $q->where('id_doi_cuu_ho', (int) $idDoi);
        }

        $perPage = min(max((int) ($boLoc['per_page'] ?? 30), 1), 100);

        return $q->paginate($perPage);
    }

    /**
     * Lịch sử: các bản ghi đã duyệt (cấp phát hoặc từ chối)
     *
     * @param  array<string, mixed>  $boLoc
     */
    public function layLichSuCapPhat(array $boLoc = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $idDoi = $boLoc['id_doi_cuu_ho'] ?? null;
        $trangThai = $boLoc['trang_thai'] ?? null;
        $tuNgay = $boLoc['tu_ngay'] ?? null;
        $denNgay = $boLoc['den_ngay'] ?? null;

        $q = YeuCauCapPhat::query()
            ->with(['doiCuuHo', 'nguoiYeuCau', 'nguoiDuyet'])
            ->whereIn('trang_thai', [
                YeuCauCapPhat::TRANG_THAI_DA_CAP_PHAT,
                YeuCauCapPhat::TRANG_THAI_TU_CHOI,
            ])
            ->orderByDesc('thoi_gian_duyet')
            ->orderByDesc('id_cap_phat');

        if ($idDoi) {
            $q->where('id_doi_cuu_ho', (int) $idDoi);
        }

        if ($trangThai) {
            $q->where('trang_thai', $trangThai);
        }

        if ($tuNgay) {
            $q->whereDate('thoi_gian_duyet', '>=', $tuNgay);
        }

        if ($denNgay) {
            $q->whereDate('thoi_gian_duyet', '<=', $denNgay);
        }

        $perPage = min(max((int) ($boLoc['per_page'] ?? 50), 1), 100);

        return $q->paginate($perPage);
    }

    public function laySoTonKho(string $slug): int
    {
        $k = KhoTaiNguyen::query()->where('slug_tai_nguyen', $slug)->first();

        return $k ? (int) $k->so_luong : 0;
    }

    /** @return array<string, mixed> */
    public function dongGoiBanGhi(YeuCauCapPhat $yc): array
    {
        $ton = $this->laySoTonKho($yc->slug_tai_nguyen);

        return [
            'id' => $yc->id_cap_phat,
            'id_doi_cuu_ho' => $yc->id_doi_cuu_ho,
            'ten_doi' => $yc->doiCuuHo?->ten_doi,
            'id_nguoi_yeu_cau' => $yc->id_nguoi_yeu_cau,
            'ten_nguoi_yeu_cau' => $yc->nguoiYeuCau?->ho_ten,
            'slug_tai_nguyen' => $yc->slug_tai_nguyen,
            'ten_loai_tai_nguyen' => $this->layTenHienThiLoai($yc->slug_tai_nguyen),
            'so_luong_yeu_cau' => (int) $yc->so_luong_yeu_cau,
            'so_luong_ton_kho' => $ton,
            'du_kho' => $ton >= (int) $yc->so_luong_yeu_cau,
            'ghi_chu' => $yc->ghi_chu,
            'trang_thai' => $yc->trang_thai,
            'id_nguoi_duyet' => $yc->id_nguoi_duyet,
            'ten_nguoi_duyet' => $yc->nguoiDuyet?->ho_ten,
            'thoi_gian_duyet' => $yc->thoi_gian_duyet?->toISOString(),
            'created_at' => $yc->created_at?->toISOString(),
            'updated_at' => $yc->updated_at?->toISOString(),
        ];
    }
}
