<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestSession extends Model
{
    protected $table = 'guest_sessions';
    protected $fillable = [
        'device_id',
        'so_dien_thoai',
        'guest_name',
        'is_linked',
        'last_active_at',
    ];

    protected $casts = [
        'is_linked' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    /**
     * Liên kết với các yêu cầu cứu hộ của guest này
     */
    public function yeuCauCuuHos()
    {
        return $this->hasMany(YeuCauCuuHo::class, 'guest_session_id', 'id');
    }
}
