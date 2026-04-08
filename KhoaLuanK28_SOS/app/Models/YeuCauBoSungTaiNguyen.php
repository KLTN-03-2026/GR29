<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauBoSungTaiNguyen extends Model
{
    protected $table = 'yeu_cau_bo_sung_tai_nguyen';
    protected $primaryKey = 'id_yeu_cau_bs';

    protected $fillable = [
        'id_doi_cuu_ho',
        'id_phan_cong',
        'id_yeu_cau',
        'noi_dung',
        'muc_do_khan_cap',
        'trang_thai',
        'ghi_chu_dieu_phoi',
    ];

    public function doiCuuHo()
    {
        return $this->belongsTo(DoiCuuHo::class, 'id_doi_cuu_ho', 'id_doi_cuu_ho');
    }

    public function phanCong()
    {
        return $this->belongsTo(PhanCongCuuHo::class, 'id_phan_cong', 'id_phan_cong');
    }

    public function yeuCau()
    {
        return $this->belongsTo(YeuCauCuuHo::class, 'id_yeu_cau', 'id_yeu_cau');
    }
}
