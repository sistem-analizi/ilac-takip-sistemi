<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bolme;
use App\Models\Cihaz;

class BolmeSeeder extends Seeder
{
    public function run(): void
    {
        $cihaz = Cihaz::first() ?? Cihaz::create([
            'kullanici_id' => 1,
            'cihaz_kodu' => 'SMARTPILL01',
            'buzzer_pin' => 5
        ]);

        for ($i = 1; $i <= 8; $i++) {
            Bolme::create([
                'cihaz_id' => $cihaz->id,
                'bolme_no' => $i,
                'yesil_led_pin' => 10 + $i,
                'kirmizi_led_pin' => 20 + $i,
                'buton_pin_no' => 30 + $i
            ]);
        }
    }
}
