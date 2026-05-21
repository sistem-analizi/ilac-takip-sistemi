@extends('layout')

@push('styles')
    <style>
        .premium-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 8px 25px -10px rgba(0, 0, 0, 0.02);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .stat-badge {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white; padding: 10px 18px; border-radius: 16px;
            box-shadow: 0 6px 15px rgba(var(--bs-primary-rgb), 0.25);
            font-weight: 600; font-size: 1rem;
        }

        .id-card-item {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.2rem;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            height: 100%;
        }

        .id-card-item:hover {
            border-color: var(--primary-light);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px -8px rgba(var(--bs-primary-rgb), 0.2);
        }

        .id-card-icon {
            width: 46px; height: 46px;
            background: rgba(var(--bs-primary-rgb), 0.1); color: var(--primary);
            border-radius: 14px; display: flex; align-items: center; justify-content: center;
        }

        .search-input {
            background-color: var(--bg-canvas);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .search-input:focus {
            background-color: var(--bg-surface);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.15);
            outline: none;
        }

        .timeline-log-item {
            display: flex;
            align-items: center;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 1rem 1.2rem;
            transition: all 0.2s ease;
            margin-bottom: 8px;
        }

        .timeline-log-item:hover {
            border-color: var(--primary-light);
            box-shadow: 0 4px 15px -5px rgba(var(--bs-primary-rgb), 0.15);
            transform: translateX(3px);
        }

        .modal-content.premium-modal {
            background-color: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-timeline-line {
            position: relative;
            padding-left: 20px; margin-left: 10px;
            border-left: 2px solid var(--border-color);
        }

        .modal-timeline-dot {
            position: absolute;
            left: -7px; top: 0;
            width: 14px; height: 14px; border-radius: 50%;
            background: var(--primary); border: 3px solid var(--bg-surface);
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        .kimlik-page-btn {
            width: 36px; height: 36px;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s ease; font-size: 0.95rem;
            border: none; outline: none;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 mt-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">İstatistikler ve Geçmiş</h4>
            <p class="text-muted mb-0" style="font-size: 0.95rem;">İlaç kullanım alışkanlıklarınızı ve kayıtlarınızı inceleyin.</p>
        </div>
        <div class="stat-badge">
            <span>Toplam Tüketim: <span class="ms-1 fs-5 text-white">{{ $toplamAlinan }} Doz</span></span>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="premium-card h-100">
                <div class="p-3 border-bottom d-flex align-items-center" style="border-color: var(--border-color) !important;">
                    <h6 class="fw-bold text-dark mb-0 text-uppercase" style="font-size: 0.9rem; letter-spacing: 0.5px;">Son 7 Günlük Aktivite</h6>
                </div>
                <div class="p-3">
                    <div style="height: 240px; width: 100%;">
                        <canvas id="barGrafik"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="premium-card h-100">
                <div class="p-3 border-bottom d-flex align-items-center justify-content-center" style="border-color: var(--border-color) !important;">
                    <h6 class="fw-bold text-dark mb-0 text-uppercase" style="font-size: 0.9rem; letter-spacing: 0.5px;">Tüketim Dağılımı</h6>
                </div>
                <div class="p-3 d-flex justify-content-center align-items-center">
                    <div style="height: 200px; width: 100%; position: relative;">
                        <canvas id="kategoriGrafik"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-5">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3 gap-3">
            <div>
                <h6 class="fw-bold text-dark mb-0">İlaç Kimlikleri</h6>
                <span class="text-muted" style="font-size: 0.85rem;">Detaylar için kartlara tıklayın</span>
            </div>

            <div class="position-relative" style="width: 100%; max-width: 280px;">
                <input type="text" id="kimlikArama" class="form-control rounded-pill pe-5 search-input py-2" placeholder="İlaç ara...">
                <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted" style="font-size: 0.9rem;"></i>
            </div>
        </div>

        <div class="row g-2" id="kimlikKonteyner">
            @forelse($ilacBazliKayitlar as $ilacAdi => $kayitlar)
                @php $safeId = \Illuminate\Support\Str::slug($ilacAdi); @endphp
                <div class="col-12 col-md-4 col-lg-3 kimlik-wrapper" data-isim="{{ strtolower($ilacAdi) }}">
                    <div class="id-card-item d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modal-{{ $safeId }}">
                        <div class="id-card-icon flex-shrink-0">
                            <i class="bi bi-capsule fs-4"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 text-truncate ilac-baslik" style="font-size: 0.95rem;">{{ $ilacAdi }}</h6>
                            <span class="text-muted d-block" style="font-size: 0.8rem;">Toplam: <span class="fw-bold text-dark">{{ $kayitlar->count() }}</span></span>
                        </div>
                        <i class="bi bi-chevron-right text-muted opacity-50 flex-shrink-0" style="font-size: 0.9rem;"></i>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-3">
                    <p class="text-muted" style="font-size: 0.95rem;">Kayıtlı ilaç kimliği bulunmuyor.</p>
                </div>
            @endforelse
        </div>

        <div id="aramaBosMesaji" class="w-100 text-center py-4 d-none">
            <h6 class="fw-bold text-dark mb-1">Sonuç Bulunamadı</h6>
            <p class="text-muted" style="font-size: 0.9rem;">Aradığınız kriterde bir ilaç kimliği yok.</p>
        </div>

        <div id="kimlikSayfalama" class="d-flex justify-content-center gap-2 mt-3"></div>
    </div>

    <div class="premium-card mb-4">
        <div class="p-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2" style="border-color: var(--border-color) !important;">
            <div>
                <h6 class="fw-bold text-dark mb-0 text-uppercase" style="font-size: 0.9rem; letter-spacing: 0.5px;">Sistem İşlem Geçmişi</h6>
            </div>

            <div class="position-relative" style="width: 100%; max-width: 280px;">
                <input type="text" id="gecmisArama" class="form-control rounded-pill pe-4 search-input py-2" placeholder="Geçmiş kayıtlarda ara...">
                <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted" style="font-size: 0.9rem;"></i>
            </div>
        </div>

        <div class="p-3" style="background-color: var(--bg-canvas);">
            @if($tumGecmisKayitlar->count() > 0)
                <div id="gecmisKonteyner">
                    @foreach($tumGecmisKayitlar as $kayit)
                        <div class="gecmis-item-wrapper" data-arama="{{ strtolower(($kayit->ilac_adi ?? '') . ' ' . ($kayit->dozaj ?? '') . ' bolme ' . ($kayit->bolme->bolme_no ?? '')) }}">
                            <div class="timeline-log-item">
                                <div class="text-center px-2 border-end me-3" style="min-width: 80px; border-color: var(--border-color) !important;">
                                    <span class="d-block fs-5 fw-bold text-dark lh-1 mb-1">{{ \Carbon\Carbon::parse($kayit->islem_zamani)->format('H:i') }}</span>
                                    <span class="text-muted fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ \Carbon\Carbon::parse($kayit->islem_zamani)->locale('tr')->translatedFormat('d M') }}</span>
                                </div>

                                <div class="flex-grow-1">
                                    <h6 class="fw-bold text-dark mb-0 log-metin" style="font-size: 1.05rem;">{{ $kayit->ilac_adi ?? 'Bilinmeyen İlaç' }}</h6>

                                    <div class="d-flex flex-wrap gap-2 log-detay mt-1">
                                        <span class="text-muted" style="font-size: 0.9rem;">Doz: <span class="text-dark fw-medium">{{ $kayit->dozaj ?? '-' }}</span></span>
                                        <span class="text-muted" style="font-size: 0.9rem;">&bull;</span>
                                        <span class="text-muted" style="font-size: 0.9rem;">Bölme: <span class="text-dark fw-medium">{{ $kayit->bolme->bolme_no ?? '-' }}</span></span>
                                        @if($kayit->planlanan_saat)
                                            <span class="text-muted" style="font-size: 0.9rem;">&bull;</span>
                                            <span class="text-muted" style="font-size: 0.9rem;">Planlanan: <span class="text-dark fw-medium">{{ \Carbon\Carbon::parse($kayit->planlanan_saat)->format('H:i') }}</span></span>
                                        @endif
                                    </div>
                                </div>

                                <div class="d-none d-sm-flex px-2 text-success opacity-75">
                                    <i class="bi bi-check-circle-fill fs-4"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div id="gecmisBosMesaji" class="w-100 text-center py-4 d-none">
                    <p class="text-muted mb-0" style="font-size: 0.95rem;">Aradığınız kriterde bir işlem kaydı bulunamadı.</p>
                </div>

                <div id="gecmisSayfalama" class="d-flex justify-content-center gap-2 mt-3"></div>

            @else
                <div class="text-center py-4 opacity-50">
                    <h6 class="fw-bold text-dark mb-1">Kayıt Yok</h6>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Henüz kaydedilmiş bir geçmiş hareketi bulunmuyor.</p>
                </div>
            @endif
        </div>
    </div>

    @foreach($ilacBazliKayitlar as $ilacAdi => $kayitlar)
        @php $safeId = \Illuminate\Support\Str::slug($ilacAdi); @endphp
        <div class="modal fade" id="modal-{{ $safeId }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content premium-modal">
                    <div class="modal-header p-3" style="border-bottom: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(var(--bs-primary-rgb), 0.1); color: var(--primary);">
                                <i class="bi bi-capsule fs-5"></i>
                            </div>
                            <div>
                                <h6 class="modal-title fw-bold text-dark mb-0">{{ $ilacAdi }}</h6>
                                <span class="text-muted" style="font-size: 0.85rem;">Toplam: {{ $kayitlar->count() }} Kullanım</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4" style="background-color: var(--bg-canvas);">
                        <div class="modal-timeline-line">
                            @foreach($kayitlar as $k)
                                <div class="position-relative mb-3">
                                    <div class="modal-timeline-dot"></div>
                                    <div class="rounded-3 p-3 shadow-sm border" style="background-color: var(--bg-surface); border-color: var(--border-color) !important;">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-dark" style="font-size: 0.95rem;">{{ \Carbon\Carbon::parse($k->islem_zamani)->locale('tr')->translatedFormat('d F Y, l') }}</span>
                                            <span class="badge rounded-pill px-2 py-1 text-white" style="background-color: var(--primary); font-size: 0.8rem;">{{ \Carbon\Carbon::parse($k->islem_zamani)->format('H:i') }}</span>
                                        </div>
                                        <span class="text-muted" style="font-size: 0.85rem;">Bölme {{ $k->bolme->bolme_no ?? '-' }} üzerinden alındı.</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const kimlikWrappers = Array.from(document.querySelectorAll('.kimlik-wrapper'));
            const kimlikArama = document.getElementById('kimlikArama');
            const kimlikSayfalama = document.getElementById('kimlikSayfalama');
            const bosMesaj = document.getElementById('aramaBosMesaji');

            let aktifKimlikler = [...kimlikWrappers];
            let suAnkiSayfa = 1;
            const sayfaBasina = 8;

            function renderKimlikler() {
                kimlikWrappers.forEach(w => w.style.display = 'none');
                const baslangic = (suAnkiSayfa - 1) * sayfaBasina;
                const bitis = baslangic + sayfaBasina;
                const gosterilecekler = aktifKimlikler.slice(baslangic, bitis);

                gosterilecekler.forEach(w => w.style.display = 'block');

                if(aktifKimlikler.length === 0) {
                    bosMesaj.classList.remove('d-none');
                    kimlikSayfalama.innerHTML = '';
                } else {
                    bosMesaj.classList.add('d-none');
                    renderSayfalamaButonlari();
                }
            }

            function renderSayfalamaButonlari() {
                kimlikSayfalama.innerHTML = '';
                const toplamSayfa = Math.ceil(aktifKimlikler.length / sayfaBasina);
                if(toplamSayfa <= 1) return;

                for(let i = 1; i <= toplamSayfa; i++) {
                    const btn = document.createElement('button');
                    const bgStyle = i === suAnkiSayfa ? 'background-color: var(--primary); color: white;' : 'background-color: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-color);';
                    btn.className = `kimlik-page-btn rounded-pill fw-bold`;
                    btn.style.cssText = bgStyle;
                    btn.innerText = i;
                    btn.onclick = () => { suAnkiSayfa = i; renderKimlikler(); };
                    kimlikSayfalama.appendChild(btn);
                }
            }

            if(kimlikArama) {
                kimlikArama.addEventListener('input', (e) => {
                    const terim = e.target.value.toLowerCase().trim();
                    aktifKimlikler = kimlikWrappers.filter(w => {
                        const baslik = w.getAttribute('data-isim');
                        return baslik.includes(terim);
                    });
                    suAnkiSayfa = 1;
                    renderKimlikler();
                });
            }
            renderKimlikler();


            const gecmisWrappers = Array.from(document.querySelectorAll('.gecmis-item-wrapper'));
            const gecmisArama = document.getElementById('gecmisArama');
            const gecmisSayfalama = document.getElementById('gecmisSayfalama');
            const gecmisBosMesaj = document.getElementById('gecmisBosMesaji');

            let aktifGecmisler = [...gecmisWrappers];
            let gecmisSayfa = 1;
            const gecmisSayfaBasina = 6;

            function renderGecmisler() {
                gecmisWrappers.forEach(w => w.style.display = 'none');
                const baslangic = (gecmisSayfa - 1) * gecmisSayfaBasina;
                const bitis = baslangic + gecmisSayfaBasina;
                const gosterilecekler = aktifGecmisler.slice(baslangic, bitis);

                gosterilecekler.forEach(w => w.style.display = 'block');

                if(aktifGecmisler.length === 0) {
                    if(gecmisBosMesaj) gecmisBosMesaj.classList.remove('d-none');
                    if(gecmisSayfalama) gecmisSayfalama.innerHTML = '';
                } else {
                    if(gecmisBosMesaj) gecmisBosMesaj.classList.add('d-none');
                    renderGecmisSayfalama();
                }
            }

            function renderGecmisSayfalama() {
                if(!gecmisSayfalama) return;
                gecmisSayfalama.innerHTML = '';
                const toplamSayfa = Math.ceil(aktifGecmisler.length / gecmisSayfaBasina);
                if(toplamSayfa <= 1) return;

                for(let i = 1; i <= toplamSayfa; i++) {
                    const btn = document.createElement('button');
                    const bgStyle = i === gecmisSayfa ? 'background-color: var(--primary); color: white;' : 'background-color: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-color);';
                    btn.className = `kimlik-page-btn rounded-pill fw-bold`;
                    btn.style.cssText = bgStyle;
                    btn.innerText = i;
                    btn.onclick = () => { gecmisSayfa = i; renderGecmisler(); };
                    gecmisSayfalama.appendChild(btn);
                }
            }

            if(gecmisArama) {
                gecmisArama.addEventListener('input', (e) => {
                    const terim = e.target.value.toLowerCase().trim();
                    aktifGecmisler = gecmisWrappers.filter(w => {
                        const metin = w.getAttribute('data-arama');
                        return metin.includes(terim);
                    });
                    gecmisSayfa = 1;
                    renderGecmisler();
                });
            }
            if(gecmisWrappers.length > 0) renderGecmisler();


            const rootStyles = getComputedStyle(document.documentElement);
            const primaryRgb = rootStyles.getPropertyValue('--bs-primary-rgb').trim() || '37, 99, 235';
            const textColor = rootStyles.getPropertyValue('--text-muted').trim() || '#64748b';
            const gridColor = rootStyles.getPropertyValue('--border-color').trim() || '#e2e8f0';

            const premiumColors = [
                `rgba(${primaryRgb}, 1)`,
                `rgba(${primaryRgb}, 0.8)`,
                `rgba(${primaryRgb}, 0.6)`,
                `rgba(${primaryRgb}, 0.4)`,
                `rgba(${primaryRgb}, 0.2)`,
                gridColor
            ];

            const grafikTarihler = {{ \Illuminate\Support\Js::from($grafikTarihler) }};
            const grafikVeriler = {{ \Illuminate\Support\Js::from($grafikVeriler) }};

            const ctxBar = document.getElementById('barGrafik').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: grafikTarihler,
                    datasets: [{
                        label: 'Kullanım',
                        data: grafikVeriler,
                        backgroundColor: `rgba(${primaryRgb}, 0.8)`,
                        borderRadius: 6,
                        barPercentage: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, color: textColor },
                            border: { display: false },
                            grid: { color: gridColor }
                        },
                        x: {
                            ticks: { color: textColor, font: { weight: '500' } },
                            grid: { display: false },
                            border: { display: false }
                        }
                    }
                }
            });

            const kategoriVerileri = {{ \Illuminate\Support\Js::from(array_values($kategoriDagilimi)) }};
            const kategoriEtiketleri = {{ \Illuminate\Support\Js::from(array_keys($kategoriDagilimi)) }};

            const ctxPie = document.getElementById('kategoriGrafik').getContext('2d');
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: kategoriEtiketleri,
                    datasets: [{
                        data: kategoriVerileri,
                        backgroundColor: premiumColors.slice(0, kategoriVerileri.length),
                        borderWidth: 2,
                        borderColor: rootStyles.getPropertyValue('--bg-surface').trim() || '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, padding: 15, font: { family: 'Poppins', size: 12, color: textColor } }
                        }
                    }
                }
            });
        });
    </script>
@endpush
