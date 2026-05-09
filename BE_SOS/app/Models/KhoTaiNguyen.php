<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KhoTaiNguyen extends Model
{
    use HasFactory;

    protected $table = 'kho_tai_nguyen';
    protected $primaryKey = 'id_tai_nguyen';
    protected $fillable = ['slug_tai_nguyen', 'ten_tai_nguyen', 'so_luong'];
}
