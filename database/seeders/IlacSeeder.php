<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class IlacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kullaniciId = DB::table('kullanicilar')->insertGetId([
            'ad_soyad' => 'Test Hastası',
            'eposta' => 'hasta@test.com',
            'sifre' => Hash::make('123456'),
            'rol' => 'hasta',
            'olusturulma_tarihi' => now(),
            'guncellenme_tarihi' => now(),
        ]);

        DB::table('ilaclar')->insert([
            [
                'kullanici_id' => $kullaniciId,
                'ilac_adi' => 'Parol',
                'dozaj' => '500mg',
                'olusturulma_tarihi' => now(),
                'guncellenme_tarihi' => now(),
            ],
            [
                'kullanici_id' => $kullaniciId,
                'ilac_adi' => 'Augmentin',
                'dozaj' => '1000mg',
                'olusturulma_tarihi' => now(),
                'guncellenme_tarihi' => now(),
            ]
        ]);
    }
}
