<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Zamanlama;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class IlacKontrol extends Command
{
    protected $signature = 'ilac:kontrol';

    protected $description = 'İlaç saatlerini ve günlerini kontrol eder, vakti gelenlerin LED durumunu kırmızı yapar.';

    public function handle()
    {
        Carbon::setLocale('tr');

        $suAnkiSaat = now()->format('H:i');
        $bugunIsim = now()->translatedFormat('l');

        $bekleyenIlaclar = Zamanlama::where('aktif_mi', 0)->get();

        // OPTİMİZASYON: Güncellenecek ID'leri biriktiriyoruz
        $guncellenecekIdler = [];

        foreach ($bekleyenIlaclar as $zamanlama) {
            $alinacakSaat = Carbon::parse($zamanlama->alinacak_saat)->format('H:i');
            $seciliGunler = is_array($zamanlama->gunler) ? $zamanlama->gunler : (array)$zamanlama->gunler;

            if (in_array($bugunIsim, $seciliGunler) && $suAnkiSaat == $alinacakSaat) {
                $guncellenecekIdler[] = $zamanlama->id;
                $this->info("Bölme {$zamanlama->bolme_id} için ilaç saati geldi: {$bugunIsim} {$suAnkiSaat}");
            }
        }

        // Tek bir veritabanı sorgusu ile hepsini güncelliyoruz (Toplu Update)
        if (!empty($guncellenecekIdler)) {
            Zamanlama::whereIn('id', $guncellenecekIdler)->update(['aktif_mi' => 1]);

            $guncellenenSayisi = count($guncellenecekIdler);
            Log::info("{$guncellenenSayisi} adet ilacın gün ve saat vakti geldi, sistem aktif edildi. Saat: {$suAnkiSaat}");
        }
    }
}
