<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YeuCauCapPhat extends Model
{
    public const TRANG_THAI_CHO_DUYET = 'CHO_DUYET';

    public const TRANG_THAI_DA_CAP_PHAT = 'DA_CAP_PHAT';

    public const TRANG_THAI_TU_CHOI = 'TU_CHOI';

    protected $table = 'yeu_cau_cap_phat';

    protected $fillable = [
        'id_doi_cuu_ho',
        'id_nguoi_yeu_cau',
        'slug_tai_nguyen',
        'so_luong_yeu_cau',
        'ghi_chu',
        'trang_thai',
        'id_nguoi_duyet',
        'thoi_gian_duyet',
    ];

    protected $casts = [
        'thoi_gian_duyet' => 'datetime',
        'so_luong_yeu_cau' => 'integer',
    ];

    public function doiCuuHo(): BelongsTo
    {
        return $this->belongsTo(DoiCuuHo::class, 'id_doi_cuu_ho', 'id_doi_cuu_ho');
    }

    public function nguoiYeuCau(): BelongsTo
    {
        return $this->belongsTo(ThanhVienDoi::class, 'id_nguoi_yeu_cau', 'id_thanh_vien_doi');
    }

    public function nguoiDuyet(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'id_nguoi_duyet', 'id_admin');
    }
}
