<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bolme extends Model
{
    protected $table = 'bolmeler';

    const CREATED_AT = 'olusturulma_tarihi';
    const UPDATED_AT = 'guncellenme_tarihi';

    protected $fillable = [
        'cihaz_id',
        'bolme_no',
        'yesil_led_pin',
        'kirmizi_led_pin',
        'buton_pin_no'
    ];

    public function cihaz()
    {
        return $this->belongsTo(Cihaz::class, 'cihaz_id');
    }

    public function zamanlamalar()
    {
        return $this->hasMany(Zamanlama::class, 'bolme_id');
    }
}
