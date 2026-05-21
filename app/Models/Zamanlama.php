<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zamanlama extends Model
{
    protected $table = 'zamanlamalar';

    protected $casts = [
        'gunler' => 'array',
    ];

    const CREATED_AT = 'olusturulma_tarihi';
    const UPDATED_AT = 'guncellenme_tarihi';

    protected $fillable = [
        'bolme_id',
        'ilac_id',
        'alinacak_saat',
        'gunler',
        'aktif_mi'
    ];

    public function bolme()
    {
        return $this->belongsTo(Bolme::class, 'bolme_id');
    }

    public function ilac()
    {
        return $this->belongsTo(Ilac::class, 'ilac_id');
    }
}
