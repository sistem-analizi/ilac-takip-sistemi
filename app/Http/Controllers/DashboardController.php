<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bolme;
use App\Models\Ilac;
use App\Models\Zamanlama;
use App\Models\SistemKaydi;
use App\Models\User; // User modeli eklendi
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $bolmeler = Bolme::with(['zamanlamalar.ilac'])->get();
        $loglar = SistemKaydi::with('bolme')->orderBy('islem_zamani', 'desc')->take(10)->get();

        $hafta = ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'];
        $haftalikPlan = array_fill_keys($hafta, []);

        foreach($bolmeler as $bolme) {
            foreach($bolme->zamanlamalar as $z) {
                $gunler = is_array($z->gunler) ? $z->gunler : (array)($z->gunler ?? []);
                foreach($gunler as $g) {
                    if(isset($haftalikPlan[$g])) {
                        $haftalikPlan[$g][] = clone $z;
                    }
                }
            }
        }

        foreach($haftalikPlan as &$planlar) {
            usort($planlar, fn($a, $b) => strcmp($a->alinacak_saat, $b->alinacak_saat));
        }

        $bugunIsim = now()->locale('tr')->translatedFormat('l');
        $bugunIndex = array_search($bugunIsim, $hafta);
        if($bugunIndex === false) $bugunIndex = 0;

        $suAnkiSaat = now()->format('H:i');

        $tumYaklasanlar = Zamanlama::where('aktif_mi', 0)
            ->with('ilac', 'bolme')
            ->get()
            ->filter(function($z) use ($bugunIsim, $suAnkiSaat) {
                $gunler = is_array($z->gunler) ? $z->gunler : (array)$z->gunler;
                return in_array($bugunIsim, $gunler) && $z->alinacak_saat > $suAnkiSaat;
            })
            ->sortBy('alinacak_saat');

        $aktifZamanlamalar = Zamanlama::where('aktif_mi', 1)->with('ilac', 'bolme')->get();
        $sonIslemler = SistemKaydi::orderBy('id', 'desc')->take(4)->get();
        $sistemKaydiSayisi = SistemKaydi::count();

        return view('dashboard', compact(
            'bolmeler', 'loglar', 'hafta', 'haftalikPlan', 'bugunIsim', 'bugunIndex',
            'tumYaklasanlar', 'aktifZamanlamalar', 'sonIslemler', 'sistemKaydiSayisi'
        ));
    }

    public function ilacEkle(Request $request)
    {
        $request->validate([
            'bolme_id' => 'required|exists:bolmeler,id',
            'ilac_adi' => 'required|string|max:120',
            'dozaj' => 'required|string|max:120',
            'alinacak_saat' => 'required|date_format:H:i'
        ]);

        DB::beginTransaction();
        try {
            $ilac = Ilac::create([
                'kullanici_id' => 1,
                'ilac_adi' => $request->ilac_adi,
                'dozaj' => $request->dozaj
            ]);

            Zamanlama::create([
                'bolme_id' => $request->bolme_id,
                'ilac_id' => $ilac->id,
                'alinacak_saat' => $request->alinacak_saat,
                'aktif_mi' => 0
            ]);

            DB::commit();
            return back()->with('success', 'İlaç başarıyla eklendi ve ayarlandı!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function kutuTemizle($bolme_id)
    {
        $zamanlamalar = Zamanlama::where('bolme_id', $bolme_id)->get();

        foreach($zamanlamalar as $zamanlama) {
            Ilac::where('id', $zamanlama->ilac_id)->delete();
            $zamanlama->delete();
        }

        return back()->with('success', 'Kutu başarıyla boşaltıldı.');
    }

    public function tumKayitlar()
    {
        $loglar = SistemKaydi::with('bolme')->orderBy('islem_zamani', 'desc')->paginate(15);
        return view('sistem_kayitlari', compact('loglar'));
    }

    public function ilacGecmisi()
    {
        $gecmisKayitlar = SistemKaydi::with('bolme')
            ->whereNotNull('ilac_adi')
            ->orderBy('islem_zamani', 'desc')
            ->paginate(6);

        $son7Gun = SistemKaydi::selectRaw('DATE(islem_zamani) as tarih, COUNT(*) as adet')
            ->whereNotNull('ilac_adi')
            ->where('islem_zamani', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('tarih')
            ->get();

        $grafikTarihler = [];
        $grafikVeriler = [];
        for ($i = 6; $i >= 0; $i--) {
            $tarih = now()->subDays($i)->format('Y-m-d');
            $grafikTarihler[] = now()->subDays($i)->locale('tr')->translatedFormat('d M');
            $gunlukVeri = $son7Gun->firstWhere('tarih', $tarih);
            $grafikVeriler[] = $gunlukVeri ? $gunlukVeri->adet : 0;
        }

        $kategoriSorgusu = SistemKaydi::select('ilac_adi', DB::raw('count(*) as adet'))
            ->whereNotNull('ilac_adi')
            ->groupBy('ilac_adi')
            ->orderByDesc('adet')
            ->get();

        $kategoriDagilimi = [];
        $limit = 5;
        $sayac = 0;
        $digerSayisi = 0;

        foreach ($kategoriSorgusu as $k) {
            if ($sayac < $limit) {
                $kategoriDagilimi[$k->ilac_adi] = $k->adet;
            } else {
                $digerSayisi += $k->adet;
            }
            $sayac++;
        }
        if ($digerSayisi > 0) {
            $kategoriDagilimi['Diğer İlaçlar'] = $digerSayisi;
        }

        $toplamAlinan = SistemKaydi::whereNotNull('ilac_adi')->count();

        $ilacBazliKayitlar = SistemKaydi::with('bolme')
            ->whereNotNull('ilac_adi')
            ->orderBy('islem_zamani', 'desc')
            ->get()
            ->groupBy('ilac_adi');

        $tumGecmisKayitlar = SistemKaydi::with('bolme')
            ->whereNotNull('ilac_adi')
            ->orderBy('islem_zamani', 'desc')
            ->get();

        return view('gecmis', compact(
            'gecmisKayitlar', 'grafikTarihler', 'grafikVeriler', 'kategoriDagilimi',
            'toplamAlinan', 'ilacBazliKayitlar', 'tumGecmisKayitlar'
        ));
    }

    public function ayarlar()
    {
        $ilaclar = \App\Models\Ilac::all();
        return view('ayarlar', compact('ilaclar'));
    }

    public function ilacKatalogGuncelle(Request $request, $id)
    {
        $request->validate([
            'ilac_adi' => 'required|string|max:120',
            'dozaj_miktar' => 'required|numeric',
            'dozaj_birim' => 'required|string'
        ]);

        $ilac = \App\Models\Ilac::findOrFail($id);
        $ilac->update([
            'ilac_adi' => $request->ilac_adi,
            'dozaj' => $request->dozaj_miktar . ' ' . $request->dozaj_birim
        ]);

        return back()->with('success', 'İlaç başarıyla güncellendi!');
    }

    public function ilacKatalogEkle(Request $request)
    {
        $request->validate([
            'ilac_adi' => 'required|string|max:120',
            'dozaj_miktar' => 'required|numeric',
            'dozaj_birim' => 'required|string'
        ]);

        \App\Models\Ilac::create([
            'kullanici_id' => 1,
            'ilac_adi' => $request->ilac_adi,
            'dozaj' => $request->dozaj_miktar . ' ' . $request->dozaj_birim
        ]);

        return back()->with('success', 'İlaç kütüphaneye başarıyla eklendi!');
    }

    public function ilacKatalogSil($id)
    {
        $ilac = \App\Models\Ilac::findOrFail($id);
        $ilac->delete();
        return back()->with('success', 'İlaç sistemden kaldırıldı.');
    }

    public function profil()
    {
        // ÖNEMLİ: Hata veren kısım düzeltildi.
        // ID 1 yoksa ilk kullanıcıyı al, o da yoksa boş nesne oluştur.
        $user = User::find(1) ?? User::first() ?? new User();

        $toplamKullanim = SistemKaydi::whereNotNull('ilac_adi')->count();
        $ilacKullanimlari = SistemKaydi::select('ilac_adi', DB::raw('count(*) as adet'))
            ->whereNotNull('ilac_adi')
            ->groupBy('ilac_adi')
            ->orderByDesc('adet')
            ->get();
        $enCokKullanilan = $ilacKullanimlari->first();
        $planliIlacSayisi = Zamanlama::count();

        return view('profil', compact('user', 'toplamKullanim', 'ilacKullanimlari', 'enCokKullanilan', 'planliIlacSayisi'));
    }

    public function profilGuncelle(Request $request)
    {
        $user = User::find(1) ?? User::first();

        if(!$user) {
            return back()->with('error', 'Güncellenecek kullanıcı bulunamadı.');
        }

        $user->update($request->only(['boy', 'kilo', 'yas', 'kan_grubu']));
        return back()->with('success', 'Sağlık verileri güncellendi!');
    }
}
