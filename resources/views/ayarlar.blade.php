@extends('layout')

@push('styles')
    <style>
        .premium-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 8px 25px -10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: background 0.3s, border-color 0.3s;
        }

        .modern-input {
            background-color: var(--bg-canvas);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.6rem 1rem;
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-main);
            transition: all 0.3s ease;
        }

        .modern-input:focus {
            background-color: var(--bg-surface);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.15);
            outline: none;
        }

        .btn-submit-premium {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.2rem;
            font-size: 1rem;
            font-weight: 600;
            box-shadow: 0 6px 15px rgba(var(--bs-primary-rgb), 0.25);
            transition: all 0.3s ease;
            color: white;
        }

        .btn-submit-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(var(--bs-primary-rgb), 0.35);
            color: white;
        }

        .list-group-item-custom {
            background-color: transparent;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .list-group-item-custom:last-child { border-bottom: none; }
        .list-group-item-custom:hover { background-color: var(--bg-canvas); }

        .modal-content.premium-modal {
            background-color: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        /* --- TEMA SEÇİCİLERİ --- */
        .theme-btn {
            width: 38px; height: 38px; border-radius: 50%;
            border: 3px solid transparent; cursor: pointer;
            transition: all 0.2s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            position: relative;
        }

        .theme-btn:hover { transform: scale(1.1); }
        .theme-btn.active {
            border-color: var(--text-main); transform: scale(1.15);
            box-shadow: 0 0 0 3px var(--bg-surface) inset, 0 4px 15px rgba(0,0,0,0.15);
        }

        .theme-btn.active::after {
            content: '\F26A'; font-family: "bootstrap-icons"; color: white;
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            font-size: 0.85rem; text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        .mode-card {
            flex: 1; border: 2px solid var(--border-color); background: var(--bg-canvas);
            border-radius: 12px; padding: 1rem; text-align: center; cursor: pointer;
            font-weight: 600; color: var(--text-muted); transition: all 0.2s;
        }

        .mode-card:hover { border-color: var(--primary-light); color: var(--primary); }
        .mode-card.active {
            border-color: var(--primary); background: rgba(var(--bs-primary-rgb), 0.05);
            color: var(--primary);
        }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center mb-5">
        <div class="col-xl-8 col-lg-10">

            <div class="d-flex align-items-center mb-4 mt-2">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-gear-fill fs-5"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-dark mb-0">Sistem Ayarları</h4>
                    <p class="text-muted mb-0" style="font-size: 0.95rem;">İlaç kütüphanenizi ve sistem görünümünü yönetin.</p>
                </div>
            </div>

            <!-- Görünüm Ayarları -->
            <div class="premium-card mb-4 p-4">
                <h6 class="fw-bold mb-3 text-dark">Arka Plan</h6>
                <div class="d-flex gap-3 mb-4">
                    <div class="mode-card" data-mode-value="light">
                        <i class="bi bi-sun-fill fs-4 d-block mb-2"></i> Aydınlık
                    </div>
                    <div class="mode-card" data-mode-value="dark">
                        <i class="bi bi-moon-fill fs-4 d-block mb-2"></i> Loş
                    </div>
                </div>

                <h6 class="fw-bold mb-3 text-dark">Vurgu Rengi</h6>
                <div class="d-flex flex-wrap gap-3" id="themeSelectorContainer">
                    <button type="button" class="theme-btn" data-theme-value="default" style="background-color: #2563eb;" title="Klasik Mavi"></button>
                    <button type="button" class="theme-btn" data-theme-value="emerald" style="background-color: #059669;" title="Zümrüt Yeşili"></button>
                    <button type="button" class="theme-btn" data-theme-value="ruby" style="background-color: #e11d48;" title="Yakut Kırmızısı"></button>
                    <button type="button" class="theme-btn" data-theme-value="amethyst" style="background-color: #7c3aed;" title="Ametist Moru"></button>
                </div>
            </div>

            <!-- İlaç Ekleme Formu -->
            <div class="premium-card mb-4 p-4">
                <h6 class="fw-bold mb-3 text-dark">Yeni İlaç Tanımla</h6>
                <form action="{{ route('ayarlar.ilacEkle') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-muted" style="font-size: 0.85rem; letter-spacing: 0.5px;">İLAÇ ADI</label>
                            <input type="text" name="ilac_adi" class="form-control modern-input" placeholder="Örn: Parol" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted" style="font-size: 0.85rem; letter-spacing: 0.5px;">MİKTAR</label>
                            <input type="number" name="dozaj_miktar" class="form-control modern-input" placeholder="1" step="any" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted" style="font-size: 0.85rem; letter-spacing: 0.5px;">BİRİM</label>
                            <select name="dozaj_birim" class="form-select modern-input" required>
                                <option value="Tablet">Tablet</option>
                                <option value="Kapsül">Kapsül</option>
                                <option value="mg">mg</option>
                                <option value="ml">ml</option>
                                <option value="Damla">Damla</option>
                                <option value="Ölçek">Ölçek</option>
                            </select>
                        </div>
                        <div class="col-12 mt-3 text-end">
                            <button type="submit" class="btn btn-submit-premium">Kütüphaneye Ekle</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Kayıtlı İlaç Kütüphanesi -->
            <div class="premium-card">
                <div class="p-3 border-bottom border-light bg-light">
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Kayıtlı İlaç Kütüphanesi</h6>
                </div>
                <div class="p-0">
                    @if($ilaclar->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($ilaclar as $ilac)
                                @php
                                    preg_match('/^([\d.,]+)\s*(.*)$/', trim($ilac->dozaj), $matches);
                                    $miktar = $matches[1] ?? '';
                                    $birim = $matches[2] ?? 'Tablet';
                                @endphp

                                <div class="list-group-item list-group-item-custom d-flex justify-content-between align-items-center py-3 px-4 border-0">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                                            <i class="bi bi-capsule" style="font-size: 1.1rem;"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 1rem;">{{ $ilac->ilac_adi }}</h6>
                                            <span class="text-muted" style="font-size: 0.85rem;">Doz: <span class="fw-medium text-dark">{{ $ilac->dozaj }}</span></span>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm bg-canvas border rounded-pill px-3 text-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $ilac->id }}" style="font-size: 0.85rem; font-weight: 600;">
                                            Düzenle
                                        </button>

                                        <form action="{{ route('ayarlar.ilacSil', $ilac->id) }}" method="POST" onsubmit="return confirm('Bu ilacı kütüphaneden silmek istediğinize emin misiniz?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm bg-canvas text-danger border rounded-pill px-3" style="font-size: 0.85rem; font-weight: 600;">Sil</button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Düzenleme Modalı -->
                                <div class="modal fade" id="editModal{{ $ilac->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content premium-modal">
                                            <div class="modal-header border-0 pb-0 pt-4 px-4">
                                                <h6 class="modal-title fw-bold text-dark">İlacı Düzenle</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <form action="{{ route('ayarlar.ilacGuncelle', $ilac->id) }}" method="POST">
                                                    @csrf
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold text-muted" style="font-size: 0.85rem; letter-spacing: 0.5px;">İLAÇ ADI</label>
                                                        <input type="text" name="ilac_adi" class="form-control modern-input" value="{{ $ilac->ilac_adi }}" required>
                                                    </div>
                                                    <div class="row g-3 mb-4">
                                                        <div class="col-6">
                                                            <label class="form-label fw-bold text-muted" style="font-size: 0.85rem; letter-spacing: 0.5px;">MİKTAR</label>
                                                            <input type="number" name="dozaj_miktar" class="form-control modern-input" value="{{ $miktar }}" step="any" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-bold text-muted" style="font-size: 0.85rem; letter-spacing: 0.5px;">BİRİM</label>
                                                            <select name="dozaj_birim" class="form-select modern-input" required>
                                                                @foreach(['Tablet', 'Kapsül', 'mg', 'ml', 'Damla', 'Ölçek'] as $b)
                                                                    <option value="{{ $b }}" {{ $birim == $b ? 'selected' : '' }}>{{ $b }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-submit-premium w-100">Değişiklikleri Kaydet</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 opacity-50">
                            <h6 class="fw-bold text-dark mb-1">Kütüphane Boş</h6>
                            <p class="text-muted mb-0" style="font-size: 0.95rem;">Sistemde kayıtlı ilaç bulunmuyor.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeBtns = document.querySelectorAll('.theme-btn');
            const modeBtns = document.querySelectorAll('.mode-card');

            // Tema Yükleme ve Değiştirme
            const currentTheme = localStorage.getItem('appTheme') || 'default';
            themeBtns.forEach(btn => {
                if(btn.getAttribute('data-theme-value') === currentTheme) btn.classList.add('active');

                btn.addEventListener('click', function() {
                    const selectedTheme = this.getAttribute('data-theme-value');
                    document.documentElement.setAttribute('data-theme', selectedTheme);
                    localStorage.setItem('appTheme', selectedTheme);

                    themeBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Mod (Light/Dark) Yükleme ve Değiştirme
            const currentMode = localStorage.getItem('bgMode') || 'light';
            modeBtns.forEach(btn => {
                if(btn.getAttribute('data-mode-value') === currentMode) btn.classList.add('active');

                btn.addEventListener('click', function() {
                    const selectedMode = this.getAttribute('data-mode-value');
                    document.documentElement.setAttribute('data-bg-mode', selectedMode);
                    localStorage.setItem('bgMode', selectedMode);

                    modeBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
@endpush
