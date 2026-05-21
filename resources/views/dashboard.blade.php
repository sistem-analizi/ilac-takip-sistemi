@extends('layout')

@push('styles')
    <style>
        .hero-banner {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 24px;
            padding: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 30px -10px rgba(var(--bs-primary-rgb), 0.3);
            margin-bottom: 1.5rem;
            z-index: 1;
        }

        .hero-banner::before {
            content: ''; position: absolute; top: -50%; right: -5%;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            border-radius: 50%; z-index: 0;
        }

        .hero-content { position: relative; z-index: 2; }

        .ticker-wrap {
            display: flex;
            overflow: hidden;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.6rem 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px -10px rgba(0,0,0,0.05);
        }

        .ticker-title {
            font-weight: 700;
            color: var(--primary);
            padding-right: 1rem;
            border-right: 2px solid var(--border-color);
            margin-right: 1rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 10;
            background: var(--bg-surface);
            white-space: nowrap;
        }

        .ticker-content {
            flex: 1;
            overflow: hidden;
            position: relative;
            display: flex;
            align-items: center;
        }

        .ticker-track {
            display: inline-block;
            white-space: nowrap;
            padding-left: 100%;
            animation: ticker-scroll 18s linear infinite;
        }

        .ticker-track:hover { animation-play-state: paused; }

        @keyframes ticker-scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }

        .ticker-item {
            display: inline-flex;
            align-items: center;
            margin-right: 3rem;
            font-size: 0.95rem;
            color: var(--text-main);
        }

        .ticker-item i { margin-right: 6px; color: var(--primary); font-size: 1.1rem; }

        .premium-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 8px 25px -10px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease; z-index: 2; position: relative;
        }

        .timeline-item {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 14px; padding: 0.8rem 1rem;
            transition: all 0.2s ease; position: relative; margin-bottom: 0.5rem;
        }

        .timeline-item::before {
            content: ''; position: absolute; left: -1px; top: 20%; bottom: 20%;
            width: 4px; border-radius: 0 4px 4px 0; background: var(--border-color); transition: all 0.3s ease;
        }

        .timeline-item:hover {
            border-color: var(--primary-light);
            box-shadow: 0 6px 15px -5px rgba(var(--bs-primary-rgb), 0.15); transform: translateX(3px);
        }

        .timeline-item:hover::before {
            background: var(--primary); box-shadow: 1px 0 5px rgba(var(--bs-primary-rgb), 0.4);
        }

        .alert-success-sleek, .alert-urgent {
            border-radius: 14px; padding: 0.85rem 1rem; margin-bottom: 0.5rem;
            border: 1px solid var(--border-color);
        }

        .alert-success-sleek { background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.2); }
        [data-bg-mode="dark"] .alert-success-sleek { background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.5); }

        .alert-urgent { background: rgba(225, 29, 72, 0.05); border-color: rgba(225, 29, 72, 0.2); }
        [data-bg-mode="dark"] .alert-urgent { background: rgba(225, 29, 72, 0.15); border-color: rgba(225, 29, 72, 0.5); }

        .modern-log {
            padding: 0.75rem 0; border-bottom: 1px dashed var(--border-color); transition: all 0.2s ease;
        }

        .modern-log:last-child { border-bottom: none; }
        .modern-log:hover { transform: translateX(2px); }

        .glass-clock {
            background: rgba(0, 0, 0, 0.15); backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1); color: white;
            font-size: 1.15rem; letter-spacing: 0.5px; padding: 0.5rem 1.2rem !important;
        }

        .btn-theme-outline {
            background-color: var(--bg-canvas);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .btn-theme-outline:hover {
            background-color: var(--border-color);
            color: var(--text-main);
        }
    </style>
@endpush

@section('content')
    <div class="hero-banner">
        <div class="hero-content d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-white rounded-pill px-3 py-1 mb-2 shadow-sm" style="font-size: 0.85rem; color: var(--text-main);">
                    {{ now()->locale('tr')->translatedFormat('d F Y, l') }}
                </span>
                <h5 class="fw-bold mb-1">Hoş Geldiniz</h5>
                <p class="text-white opacity-75 mb-0" style="font-size: 0.95rem;">Sistem donanımla devrede, bugünkü planınız hazır.</p>
            </div>
            <div class="d-inline-flex align-items-center glass-clock rounded-pill shadow-sm">
                <i class="bi bi-clock me-2 opacity-75"></i>
                <span id="live-clock" class="fw-bold">{{ now()->format('H:i') }}</span>
            </div>
        </div>
    </div>

    @if($tumYaklasanlar->isNotEmpty())
        <div class="ticker-wrap animate__animated animate__fadeIn">
            <div class="ticker-title">
                <i class="bi bi-activity"></i> Yaklaşanlar
            </div>
            <div class="ticker-content">
                <div class="ticker-track">
                    @foreach($tumYaklasanlar as $yaklasan)
                        @php
                            $hedefSaatObj = \Carbon\Carbon::createFromFormat('H:i', \Carbon\Carbon::parse($yaklasan->alinacak_saat)->format('H:i'));
                            $kalanDakika = intval(now()->diffInMinutes($hedefSaatObj, false));

                            if ($kalanDakika >= 60) {
                                $saat = intval($kalanDakika / 60);
                                $dakika = $kalanDakika % 60;
                                $kalanMetin = $saat . ' saat ' . ($dakika > 0 ? $dakika . ' dk' : '') . ' kaldı';
                            } else {
                                $kalanMetin = $kalanDakika . ' dk kaldı';
                            }
                        @endphp
                        <div class="ticker-item">
                            <i class="bi bi-capsule-pill"></i>
                            <span class="fw-bold">{{ $yaklasan->ilac->ilac_adi }}</span>
                            <span class="text-muted ms-1" style="font-size: 0.85rem;">
                                (Bölme {{ $yaklasan->bolme->bolme_no }} • {{ \Carbon\Carbon::parse($yaklasan->alinacak_saat)->format('H:i') }} / <span class="fw-medium">{{ $kalanMetin }}</span>)
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="premium-card p-4 h-100 d-flex flex-column">
                <h6 class="fw-bold text-dark mb-3 text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px;">
                    Acil Bildirimler
                </h6>
                <div class="flex-grow-1 d-flex flex-column gap-2 overflow-auto" style="max-height: 250px;">
                    @forelse($aktifZamanlamalar as $aktif)
                        <div class="alert-urgent d-flex align-items-center mb-0">
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-danger mb-0" style="font-size: 0.95rem;">{{ $aktif->ilac->ilac_adi }}</h6>
                                <span class="text-danger opacity-75 d-block" style="font-size: 0.85rem;">Bölme {{ $aktif->bolme->bolme_no }} • Vakti geldi</span>
                            </div>
                        </div>
                    @empty
                        <div class="alert-success-sleek d-flex align-items-center h-100 justify-content-center border-0 text-center" style="background: transparent;">
                            <div>
                                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 50px; height: 50px; background-color: rgba(16, 185, 129, 0.1); color: #10b981;">
                                    <i class="bi bi-check2-all fs-3"></i>
                                </div>
                                <h6 class="fw-bold text-success mb-0" style="font-size: 0.95rem;">Tümü Alındı</h6>
                                <span class="text-success opacity-75 d-block" style="font-size: 0.85rem;">Bekleyen ilaç yok.</span>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="premium-card p-4 h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0 text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px;">
                        Son İşlemler
                    </h6>
                </div>

                <div class="d-flex flex-column flex-grow-1">
                    @forelse($sonIslemler as $log)
                        <div class="modern-log d-flex align-items-start">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 32px; height: 32px; color: var(--primary); background-color: rgba(var(--bs-primary-rgb), 0.1);">
                                <i class="bi bi-check" style="font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <p class="fw-bold text-dark mb-0" style="font-size: 0.9rem;">{{ $log->durum }}</p>
                                <span class="text-muted" style="font-size: 0.8rem;">{{ \Carbon\Carbon::parse($log->islem_zamani)->locale('tr')->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3 d-flex flex-column h-100 justify-content-center">
                            <p class="text-muted mb-0" style="font-size: 0.9rem;">Kayıt bulunmuyor.</p>
                        </div>
                    @endforelse
                </div>

                @if($sistemKaydiSayisi > 0)
                    <a href="{{ route('log.index') }}" class="btn btn-theme-outline w-100 mt-3 rounded-pill fw-medium shadow-sm" style="font-size: 0.9rem; padding: 0.5rem;">Tümünü Gör</a>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="premium-card h-100">
                <div class="d-flex justify-content-between align-items-center p-4 border-bottom border-light">
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-theme-outline rounded-circle shadow-sm d-flex align-items-center justify-content-center p-0" style="width: 40px; height: 40px; color: var(--primary) !important;" onclick="gunDegistir(-1)">
                            <i class="bi bi-chevron-left" style="font-size: 1rem;"></i>
                        </button>
                        <h6 class="fw-bold mb-0 text-dark" id="gunBaslik" style="min-width: 140px; text-align: center; font-size: 1.15rem;">
                            {{ $bugunIsim }}
                        </h6>
                        <button class="btn btn-theme-outline rounded-circle shadow-sm d-flex align-items-center justify-content-center p-0" style="width: 40px; height: 40px; color: var(--primary) !important;" onclick="gunDegistir(1)">
                            <i class="bi bi-chevron-right" style="font-size: 1rem;"></i>
                        </button>
                    </div>
                    <a href="{{ route('ilac.index') }}" class="btn btn-theme-outline rounded-pill d-none d-sm-block fw-medium" style="font-size: 0.95rem; padding: 0.5rem 1.5rem;">
                        Programı Düzenle
                    </a>
                </div>

                <div class="p-4" style="min-height: 300px;">
                    @foreach($hafta as $index => $gunAdi)
                        <div class="gun-slayti animate__animated animate__fadeIn {{ $index == $bugunIndex ? 'd-block' : 'd-none' }}" data-index="{{ $index }}" data-isim="{{ $gunAdi }}">
                            <div class="row g-3">
                                @forelse($haftalikPlan[$gunAdi] as $z)
                                    <div class="col-12 col-xl-6">
                                        <div class="timeline-item d-flex align-items-center h-100">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm flex-shrink-0" style="width: 48px; height: 48px; color: var(--primary); background-color: rgba(var(--bs-primary-rgb), 0.1);">
                                                <i class="bi bi-capsule" style="font-size: 1.4rem;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold text-dark mb-0" style="font-size: 1.1rem;">{{ $z->ilac->ilac_adi }}</h6>
                                                <div class="d-flex align-items-center gap-2 text-muted mt-1" style="font-size: 0.9rem;">
                                                    <span>Doz: {{ $z->ilac->dozaj }} &bull; Bölme {{ $z->bolme_id }}</span>
                                                </div>
                                            </div>
                                            <div class="text-end ps-3 border-start border-light">
                                                <div class="text-muted fw-bold text-uppercase mb-0" style="font-size: 0.75rem; letter-spacing: 0.5px;">Planlanan</div>
                                                <div class="fw-bold text-dark" style="font-size: 1.3rem; letter-spacing: -0.5px;">
                                                    {{ \Carbon\Carbon::parse($z->alinacak_saat)->format('H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 d-flex flex-column justify-content-center align-items-center py-5 text-center">
                                        <h5 class="fw-bold text-dark mb-2" style="font-size: 1.35rem;">Plan Yok</h5>
                                        <p class="text-muted mb-0" style="font-size: 1.05rem;">Bu güne atanmış ilaç bulunmuyor.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let aktifGunIndex = {{ $bugunIndex }};
        const toplamGun = 7;
        const gunIsimleri = @json($hafta);

        function gunDegistir(yon) {
            const eskiSlayt = document.querySelector(`.gun-slayti[data-index="${aktifGunIndex}"]`);
            eskiSlayt.classList.remove('animate__fadeIn');
            eskiSlayt.classList.replace('d-block', 'd-none');

            aktifGunIndex = (aktifGunIndex + yon + toplamGun) % toplamGun;

            const yeniSlayt = document.querySelector(`.gun-slayti[data-index="${aktifGunIndex}"]`);
            yeniSlayt.classList.replace('d-none', 'd-block');

            void yeniSlayt.offsetWidth;
            yeniSlayt.classList.add('animate__fadeIn');

            const baslikElementi = document.getElementById('gunBaslik');
            baslikElementi.innerText = gunIsimleri[aktifGunIndex];
        }

        function updateClock() {
            const now = new Date();
            const clock = document.getElementById('live-clock');
            if(clock) clock.innerText = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
@endpush
