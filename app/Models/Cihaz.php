<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cihaz extends Model
{
    protected $table = 'cihazlar';

    const CREATED_AT = 'olusturulma_tarihi';
    const UPDATED_AT = 'guncellenme_tarihi';

    protected $fillable = [
        'kullanici_id',
        'cihaz_kodu',
        'buzzer_pin'
    ];

    public function bolmeler()
    {
        return $this->hasMany(Bolme::class, 'cihaz_id');
    }

    public function kullanici()
    {
        return $this->belongsTo(User::class, 'kullanici_id'); // Standart 'users' tablosunu baz alarak
    }
}
