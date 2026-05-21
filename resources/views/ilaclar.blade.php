@extends('layout')

@push('styles')
    <style>
        .premium-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 8px 25px -10px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        /* Donanım Bölme Widget'ları */
        .compartment-widget {
            border-radius: 16px;
            padding: 1.2rem;
            position: relative;
            z-index: 1;
            border: 1px solid var(--border-color);
        }

        .compartment-widget:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -8px rgba(0, 0, 0, 0.05);
        }

        .compartment-widget.dolu { background: rgba(225, 29, 72, 0.05); border-color: rgba(225, 29, 72, 0.2); }
        [data-bg-mode="dark"] .compartment-widget.dolu { background: rgba(225, 29, 72, 0.15); border-color: rgba(225, 29, 72, 0.4); }

        .compartment-widget.bos { background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.2); }
        [data-bg-mode="dark"] .compartment-widget.bos { background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.4); }

        .schedule-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px -10px rgba(var(--bs-primary-rgb), 0.15);
            border-color: var(--primary-light);
        }

        /* Haftalık Gün Rozetleri */
        .day-pill-container { display: flex; gap: 4px; flex-wrap: wrap; }
        .day-pill {
            font-size: 0.8rem; font-weight: 600;
            padding: 5px 12px; border-radius: 8px;
            transition: all 0.2s ease;
        }

        .day-pill.active {
            background: var(--primary); color: white;
            box-shadow: 0 4px 8px rgba(var(--bs-primary-rgb), 0.25);
        }

        .day-pill.inactive {
            background: var(--bg-canvas); color: var(--text-muted);
            border: 1px solid var(--border-color);
        }

        /* BUTONLAR */
        .btn-soft-primary, .btn-soft-warning, .btn-soft-danger {
            font-size: 0.9rem; font-weight: 600;
            padding: 0.5rem 0.6rem; border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        /* Düzenle Butonu */
        .btn-soft-primary { background: rgba(var(--bs-primary-rgb), 0.08); color: var(--primary); }
        [data-bg-mode="dark"] .btn-soft-primary { background: rgba(var(--bs-primary-rgb), 0.2); border-color: rgba(var(--bs-primary-rgb), 0.4); color: var(--primary-light); }
        .btn-soft-primary:hover { background: var(--primary); color: white !important; border-color: var(--primary); }

        /* Sanal Buton */
        .btn-soft-warning { background: rgba(245, 158, 11, 0.1); color: #d97706; border-color: rgba(245, 158, 11, 0.2); }
        [data-bg-mode="dark"] .btn-soft-warning { background: rgba(245, 158, 11, 0.15); border-color: rgba(245, 158, 11, 0.4); color: #fbbf24; }
        .btn-soft-warning:hover { background: #d97706; color: white !important; border-color: #d97706; }

        /* Kaldır Butonu */
        .btn-soft-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }
        [data-bg-mode="dark"] .btn-soft-danger { background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.4); color: #f87171; }
        .btn-soft-danger:hover { background: #ef4444; color: white !important; border-color: #ef4444; }

        /* Vakti Gelen Animasyonu */
        .time-alert {
            background: rgba(239, 68, 68, 0.05);
            border: 1px dashed rgba(239, 68, 68, 0.4);
            border-radius: 12px; padding: 0.6rem;
            animation: gentlePulse 2s infinite;
        }
        [data-bg-mode="dark"] .time-alert { background: rgba(239, 68, 68, 0.15); border: 1px dashed rgba(239, 68, 68, 0.6); }

        @keyframes gentlePulse {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.2); }
            70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 mt-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">İlaç Programım</h4>
            <p class="text-muted mb-0" style="font-size: 0.95rem;">8 Donanım bölmesini ve cihazınızın haftalık planını yönetin.</p>
        </div>
        <div>
            <div class="d-inline-flex align-items-center rounded-pill px-3 py-2 border" style="background-color: var(--bg-surface); border-color: var(--border-color);">
                <span id="live-clock" class="fw-bold text-dark" style="font-size: 1.05rem;">{{ now()->format('H:i') }}</span>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <h6 class="fw-bold text-dark mb-0 text-uppercase" style="letter-spacing: 0.5px; font-size: 0.85rem;">Fiziksel Cihaz Durumu (8 Bölme)</h6>
    </div>

    <!-- Cihaz Bölmeleri -->
    <div class="row g-3 mb-5">
        @foreach($bolmeler as $bolme)
            @php $z = $bolme->zamanlamalar->first(); @endphp
            <div class="col-6 col-md-3">
                <div class="premium-card compartment-widget {{ $z ? 'dolu' : 'bos' }} text-center h-100 d-flex flex-column justify-content-center">
                    <h6 class="text-muted fw-bold mb-1 opacity-75" style="font-size: 0.75rem;">BÖLME</h6>
                    <h4 class="fw-bold mb-2 text-dark">{{ $bolme->bolme_no }}</h4>

                    <div class="mb-2">
                        <span class="badge bg-{{ $z ? 'danger' : 'success' }} text-white rounded-pill px-2 py-1 shadow-sm" style="font-size: 0.8rem;">
                            <i class="bi {{ $z ? 'bi-lock-fill' : 'bi-unlock-fill' }} me-1"></i> {{ $z ? 'Dolu' : 'Müsait' }}
                        </span>
                    </div>

                    <div class="fw-semibold {{ $z ? 'text-dark' : 'text-muted opacity-50' }} text-truncate px-1" style="font-size: 0.9rem;" title="{{ $z->ilac->ilac_adi ?? 'Kullanıma Hazır' }}">
                        {{ $z->ilac->ilac_adi ?? 'Kullanıma Hazır' }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold text-dark mb-0 text-uppercase" style="letter-spacing: 0.5px; font-size: 0.85rem;">Aktif İlaç Programı</h6>
        <a href="{{ route('ilac.create') }}" class="btn btn-sm rounded-pill px-3 fw-medium shadow-sm d-md-none text-white" style="font-size: 0.9rem; background-color: var(--primary); border: 1px solid var(--primary);">
            Yeni Ekle
        </a>
    </div>

    <!-- Program Kartları -->
    <div class="row g-3">
        @php
            $hafta = ['Pazartesi'=>'Pzt', 'Salı'=>'Sal', 'Çarşamba'=>'Çar', 'Perşembe'=>'Per', 'Cuma'=>'Cum', 'Cumartesi'=>'Cmt', 'Pazar'=>'Paz'];
            $ilacVarMi = $bolmeler->sum(fn($b) => $b->zamanlamalar->count()) > 0;
        @endphp

        @if($ilacVarMi)
            @foreach($bolmeler as $bolme)
                @foreach($bolme->zamanlamalar as $z)
                    <div class="col-12 col-xl-6">
                        <div class="premium-card schedule-card h-100 d-flex flex-column">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="border-color: var(--border-color) !important; background-color: rgba(0,0,0,0.02);">
                                <span class="badge border rounded-pill px-2 py-1 text-dark" style="font-size: 0.85rem; background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                    Bölme {{ $bolme->bolme_no }}
                                </span>
                                <span class="fw-bold text-dark" style="font-size: 1.2rem; letter-spacing: -0.5px;">
                                    {{ \Carbon\Carbon::parse($z->alinacak_saat)->format('H:i') }}
                                </span>
                            </div>

                            <div class="card-body p-3 flex-grow-1">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 44px; height: 44px; background-color: rgba(var(--bs-primary-rgb), 0.1); color: var(--primary);">
                                        <i class="bi bi-capsule fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 1.05rem;" title="{{ $z->ilac->ilac_adi }}">{{ $z->ilac->ilac_adi }}</h6>
                                        <span class="text-muted d-block" style="font-size: 0.9rem;">
                                            Doz: <span class="text-dark fw-medium">{{ $z->ilac->dozaj }}</span>
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <span class="text-muted d-block mb-1 fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.75rem;">Haftalık Rutin</span>
                                    <div class="day-pill-container">
                                        @foreach($hafta as $tam => $kisa)
                                            @php $kayitli = in_array($tam, (array)($z->gunler ?? [])); @endphp
                                            <span class="day-pill {{ $kayitli ? 'active' : 'inactive' }}" title="{{ $tam }}">
                                                {{ $kisa }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 pt-0 mt-auto">
                                @if($z->aktif_mi)
                                    <div class="time-alert mb-2 text-center">
                                        <span class="text-danger fw-bold d-flex align-items-center justify-content-center gap-1" style="font-size: 0.9rem;">
                                            <i class="bi bi-exclamation-circle-fill"></i> ŞU AN İLAÇ VAKTİ!
                                        </span>
                                    </div>
                                @else
                                    <div class="rounded-3 p-2 mb-2 text-center border" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important;">
                                        <span class="text-success fw-bold d-block" style="font-size: 0.85rem;">
                                            Saat Bekleniyor
                                        </span>
                                    </div>
                                @endif

                                <div class="d-flex gap-2">
                                    <a href="{{ route('ilac.edit', $z->id) }}" class="btn btn-soft-primary rounded-pill flex-fill text-center">
                                        <i class="bi bi-pencil me-1"></i> Düzenle
                                    </a>

                                    <button type="button" onclick="window.sanalButon(this, {{ $bolme->bolme_no }})" class="btn btn-soft-warning rounded-pill flex-fill">
                                        <i class="bi bi-robot me-1"></i> Onayla
                                    </button>

                                    <form action="{{ route('bolme.temizle', $bolme->id) }}" method="POST" class="flex-fill d-flex" onsubmit="return confirm('Bu ilacı ve programı silmek istediğinize emin misiniz?');">
                                        @csrf
                                        <button type="submit" class="btn btn-soft-danger rounded-pill w-100">
                                            <i class="bi bi-trash3 me-1"></i> Kaldır
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        @else
            <div class="col-12 text-center py-4">
                <div class="premium-card p-4 d-inline-block border shadow-sm" style="max-width: 450px; border-color: var(--border-color) !important;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px; background-color: rgba(var(--bs-primary-rgb), 0.1); color: var(--primary);">
                        <i class="bi bi-clipboard-x fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Henüz Bir Plan Yok</h5>
                    <p class="text-muted mb-4" style="font-size: 0.95rem;">Donanım bölmelerine ilaç atamak ve kütüphaneden ilaç seçin.</p>
                    <a href="{{ route('ilac.create') }}" class="btn rounded-pill px-4 py-2 fw-bold shadow-sm text-white" style="font-size: 1rem; background-color: var(--primary); border: 1px solid var(--primary);">
                        İlk İlacını Planla
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Canlı saat güncellemesi
            const updateClock = () => {
                const d = new Date();
                const c = document.getElementById('live-clock');
                if(c) {
                    c.innerText = `${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
                }
            };
            setInterval(updateClock, 1000);
            updateClock();
        });

        // Sanal Buton API İsteği
        window.sanalButon = async function(btnElement, bolmeNo) {
            const originalText = btnElement.innerHTML;
            btnElement.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>...';
            btnElement.disabled = true;

            try {
                const response = await fetch(`/api/buton-basildi/${bolmeNo}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (data.status === 'success') {
                    window.location.reload();
                } else {
                    alert('Bir hata oluştu veya bu bölmede aktif ilaç bulunmuyor.');
                    btnElement.innerHTML = originalText;
                    btnElement.disabled = false;
                }
            } catch (error) {
                alert('Bağlantı hatası! Lütfen internet bağlantınızı kontrol edin.');
                btnElement.innerHTML = originalText;
                btnElement.disabled = false;
            }
        };
    </script>
@endpush
