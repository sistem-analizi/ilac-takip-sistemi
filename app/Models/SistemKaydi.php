<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SistemKaydi extends Model
{
    protected $table = 'sistem_kayitlari';

    const CREATED_AT = 'islem_zamani';
    const UPDATED_AT = null;

    protected $fillable = [
        'cihaz_id',
        'bolme_id',
        'ilac_adi',
        'dozaj',
        'planlanan_saat',
        'durum'
    ];

    public function cihaz()
    {
        return $this->belongsTo(Cihaz::class, 'cihaz_id');
    }

    public function bolme()
    {
        return $this->belongsTo(Bolme::class, 'bolme_id');
    }
}
