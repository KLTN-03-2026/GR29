<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaiNguyenCuuHo extends Model
{
    protected $table = 'tai_nguyen_cuu_ho';
    protected $primaryKey = 'id_tai_nguyen';
    protected $fillable = ['id_doi_cuu_ho', 'ten_tai_nguyen', 'loai_tai_nguyen', 'so_luong', 'trang_thai', 'dang_su_dung_cho_nhiem_vu', 'so_luong_dang_su_dung'];

    public function doiCuuHo()
    {
        return $this->belongsTo(DoiCuuHo::class, 'id_doi_cuu_ho', 'id_doi_cuu_ho');
    }
}
