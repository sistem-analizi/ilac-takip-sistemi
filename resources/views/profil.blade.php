@extends('layout')

@push('styles')
    <style>
        /* =========================================
           PROFİL SAYFASI ÖZEL STİLLERİ
           ========================================= */
        .premium-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 8px 25px -10px rgba(0, 0, 0, 0.02);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        /* Avatar ve Kullanıcı Bilgisi */
        .avatar-container {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.1) 0%, rgba(var(--bs-primary-rgb), 0.2) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem auto;
            border: 4px solid var(--bg-surface);
            box-shadow: 0 8px 20px rgba(var(--bs-primary-rgb), 0.15);
        }

        .avatar-container i {
            font-size: 3rem;
            color: var(--primary);
        }

        /* Sağlık Formu Elemanları */
        .health-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 6px;
        }

        .health-input {
            background: var(--bg-canvas);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.6rem;
            color: var(--text-main);
            font-weight: 600;
            text-align: center;
            width: 100%;
            transition: all 0.3s;
        }

        .health-input:focus {
            border-color: var(--primary);
            outline: none;
            background: var(--bg-surface);
            box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.1);
        }

        /* VKI Göstergesi */
        .vki-box {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 15px;
            background-color: var(--bg-canvas);
            border: 1px solid var(--border-color);
        }

        .vki-badge {
            padding: 4px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        .vki-normal { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .vki-uyari { background: rgba(245, 158, 11, 0.1); color: #d97706; }

        /* İlaç İstatistikleri */
        .highlight-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 18px;
            padding: 1.5rem;
        }

        .custom-progress-bg {
            background-color: var(--bg-canvas);
            border-radius: 10px;
            height: 10px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .custom-progress-bar {
            background: linear-gradient(90deg, var(--primary-light) 0%, var(--primary) 100%);
            height: 100%;
            border-radius: 10px;
            transition: width 1s ease-in-out;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 mt-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">Kullanıcı Profili & Sağlık Özeti</h4>
            <p class="text-muted mb-0" style="font-size: 0.95rem;">Kişisel verilerinizi yönetin ve tüketim analizini inceleyin.</p>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- SOL KOLON: Sağlık Formu -->
        <div class="col-lg-4">
            <div class="premium-card p-4 text-center h-100">
                <div class="avatar-container">
                    <i class="bi bi-person-fill"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Sistem Kullanıcısı</h5>
                <p class="text-muted mb-4" style="font-size: 0.9rem;">Fiziksel Veriler</p>

                <form action="{{ route('profil.guncelle') }}" method="POST" class="text-start">
                    @csrf
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="health-label">Boy (cm)</label>
                            <input type="number" name="boy" class="health-input" value="{{ $user->boy }}" placeholder="Örn: 175">
                        </div>
                        <div class="col-6">
                            <label class="health-label">Kilo (kg)</label>
                            <input type="number" step="0.1" name="kilo" class="health-input" value="{{ $user->kilo }}" placeholder="Örn: 72.5">
                        </div>
                        <div class="col-6">
                            <label class="health-label">Yaş</label>
                            <input type="number" name="yas" class="health-input" value="{{ $user->yas }}" placeholder="Örn: 28">
                        </div>
                        <div class="col-6">
                            <label class="health-label">Kan Grubu</label>
                            <select name="kan_grubu" class="health-input">
                                <option value="" disabled selected>-</option>
                                @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', '0+', '0-'] as $g)
                                    <option value="{{ $g }}" {{ $user->kan_grubu == $g ? 'selected' : '' }}>{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">
                                <i class="bi bi-save me-2"></i> Bilgileri Güncelle
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Vücut Kitle İndeksi Hesaplama --}}
                @if($user->boy && $user->kilo)
                    @php
                        $vki = round($user->kilo / (($user->boy / 100) * ($user->boy / 100)), 1);
                        $durum = $vki < 18.5 ? 'Zayıf' : ($vki < 25 ? 'Normal' : ($vki < 30 ? 'Fazla Kilolu' : 'Obez'));
                        $badgeClass = ($vki >= 18.5 && $vki < 25) ? 'vki-normal' : 'vki-uyari';
                    @endphp

                    <div class="vki-box">
                        <span class="health-label mb-2">Vücut Kitle İndeksi (VKI)</span>
                        <div class="d-flex align-items-center justify-content-center gap-3">
                            <h3 class="fw-bold text-dark mb-0">{{ $vki }}</h3>
                            <span class="vki-badge {{ $badgeClass }}">{{ $durum }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- SAĞ KOLON: Analizler -->
        <div class="col-lg-8">
            <div class="row g-4">
                {{-- En Çok Kullanılan Kartı --}}
                <div class="col-12">
                    <div class="highlight-card shadow-sm">
                        <h6 class="text-uppercase fw-bold opacity-75 mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">En Çok Kullanılan İlaç</h6>

                        @if($enCokKullanilan)
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="fw-bold mb-1">{{ $enCokKullanilan->ilac_adi }}</h2>
                                    <p class="mb-0 opacity-75">Sistem üzerinden bu ilacı <strong>{{ $enCokKullanilan->adet }} kez</strong> onayladınız.</p>
                                </div>
                                <i class="bi bi-trophy fs-1 opacity-25"></i>
                            </div>
                        @else
                            <h3 class="fw-bold mb-0">Veri Bulunmuyor</h3>
                            <p class="opacity-75">İlaç kullanım geçmişiniz oluştukça burada görünecektir.</p>
                        @endif
                    </div>
                </div>

                {{-- Kullanım Dağılımı --}}
                <div class="col-12">
                    <div class="premium-card p-4">
                        <h6 class="fw-bold text-dark mb-4 text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px;">İlaç Tüketim Analizi</h6>

                        @forelse($ilacKullanimlari as $ilac)
                            @php
                                $maxAdet = $enCokKullanilan->adet ?? 1;
                                $yuzde = ($ilac->adet / $maxAdet) * 100;
                            @endphp

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-end mb-2">
                                    <span class="fw-bold text-dark" style="font-size: 1rem;">{{ $ilac->ilac_adi }}</span>
                                    <span class="text-muted fw-bold" style="font-size: 0.9rem;">{{ $ilac->adet }} Doz</span>
                                </div>
                                <div class="custom-progress-bg">
                                    <div class="custom-progress-bar" style="width: {{ $yuzde }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 opacity-50">
                                <i class="bi bi-graph-up fs-1"></i>
                                <p class="mt-2 fw-bold">Henüz istatistik oluşturulacak kadar veri yok.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
