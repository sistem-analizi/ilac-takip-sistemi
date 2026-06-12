<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bolme;
use App\Models\Ilac;
use App\Models\Zamanlama;

class IlacController extends Controller
{
    public function index()
    {
        $bolmeler = Bolme::with(['zamanlamalar.ilac'])->get();
        return view('ilaclar', compact('bolmeler'));
    }

    public function create()
    {
        $bolmeler = Bolme::with('zamanlamalar')->get();
        $ilaclar = Ilac::all(); // Seçim için tüm kütüphaneyi gönderiyoruz
        return view('ilac_ekle', compact('bolmeler', 'ilaclar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bolme_id' => 'required|exists:bolmeler,id',
            'ilac_id' => 'required|exists:ilaclar,id',
            'alinacak_saat' => 'required',
            'gunler' => 'required|array|min:1',
        ]);

        Zamanlama::create([
            'bolme_id' => $request->bolme_id,
            'ilac_id' => $request->ilac_id,
            'alinacak_saat' => $request->alinacak_saat,
            'gunler' => $request->gunler,
            'aktif_mi' => 0
        ]);

        return redirect()->route('ilac.index')->with('success', 'İlaç programı başarıyla oluşturuldu!');
    }

    public function temizle($bolme_id)
    {
        $zamanlama = Zamanlama::where('bolme_id', $bolme_id)->first();
        if ($zamanlama) {
            $zamanlama->delete();
            return redirect()->route('ilac.index')->with('success', 'Bölme başarıyla boşaltıldı!');
        }
        return redirect()->route('ilac.index')->with('error', 'Bu bölme zaten boş.');
    }

    public function edit($id)
    {
        $zamanlama = Zamanlama::with('ilac', 'bolme')->findOrFail($id);
        $bolmeler = Bolme::with('zamanlamalar')->get();
        $ilaclar = Ilac::all();
        return view('ilac_duzenle', compact('zamanlama', 'bolmeler', 'ilaclar'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'bolme_id' => 'required|exists:bolmeler,id',
            'ilac_id' => 'required|exists:ilaclar,id',
            'alinacak_saat' => 'required',
            'gunler' => 'required|array|min:1',
        ]);

        $zamanlama = Zamanlama::findOrFail($id);

        $zamanlama->update([
            'bolme_id' => $request->bolme_id,
            'ilac_id' => $request->ilac_id,
            'alinacak_saat' => $request->alinacak_saat,
            'gunler' => $request->gunler,
        ]);

        return redirect()->route('ilac.index')->with('success', 'İlaç programı başarıyla güncellendi!');
    }
}
