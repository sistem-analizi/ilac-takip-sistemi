<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ilac extends Model
{
    protected $table = 'ilaclar';

    const CREATED_AT = 'olusturulma_tarihi';
    const UPDATED_AT = 'guncellenme_tarihi';

    protected $fillable = [
        'kullanici_id',
        'ilac_adi',
        'dozaj'
    ];

    public function kullanici()
    {
        return $this->belongsTo(User::class, 'kullanici_id');
    }
}
