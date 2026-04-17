<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RescueReport extends Model
{
    protected $table = 'rescue_reports';
    protected $primaryKey = 'id_bao_cao';

    protected $fillable = [
        'id_yeu_cau',
        'id_doi_cuu_ho',
        'ket_qua',
        'ly_do_that_bai',
        'hinh_anh',
        'mo_ta_hien_truong',
    ];

    /**
     * Relationship with YeuCauCuuHo
     */
    public function yeuCau()
    {
        return $this->belongsTo(YeuCauCuuHo::class, 'id_yeu_cau', 'id_yeu_cau');
    }

    /**
     * Relationship with DoiCuuHo
     */
    public function doiCuuHo()
    {
        return $this->belongsTo(DoiCuuHo::class, 'id_doi_cuu_ho', 'id_doi_cuu_ho');
    }
}
