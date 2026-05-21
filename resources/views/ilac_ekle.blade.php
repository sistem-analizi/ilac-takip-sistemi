@extends('layout')

@push('styles')
    <style>
        .premium-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 8px 25px -10px rgba(0, 0, 0, 0.02);
        }

        .step-badge {
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--primary);
            font-weight: 700;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 12px;
            font-size: 0.95rem;
        }

        .box-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
        }

        .compartment-box {
            background-color: var(--bg-canvas);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 1.2rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .compartment-box.empty:hover {
            border-color: var(--primary-light);
            background-color: var(--bg-surface);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px -8px rgba(var(--bs-primary-rgb), 0.15);
        }

        .compartment-box.selected {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(var(--bs-primary-rgb),0.05) 0%, var(--bg-surface) 100%);
            box-shadow: 0 8px 15px rgba(var(--bs-primary-rgb), 0.15);
            transform: translateY(-2px);
        }

        .compartment-box.selected::after {
            content: '\F26A';
            font-family: "bootstrap-icons";
            position: absolute;
            top: 10px; right: 12px;
            color: var(--primary); font-size: 1.1rem;
            animation: fadeIn 0.3s ease;
        }

        .compartment-box.full {
            opacity: 0.6;
            cursor: not-allowed;
            background-color: var(--bg-canvas);
            border-color: var(--border-color);
            filter: grayscale(100%);
        }

        .box-icon { font-size: 1.8rem; margin-bottom: 8px; color: var(--text-muted); transition: color 0.3s ease; }
        .compartment-box.selected .box-icon { color: var(--primary); }

        .modern-input {
            background-color: var(--bg-canvas);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.8rem 1rem;
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

        .form-label { font-weight: 600; color: var(--text-muted); font-size: 0.95rem; margin-bottom: 8px; }

        .day-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }
        @media (max-width: 992px) { .day-grid { grid-template-columns: repeat(4, 1fr); } }
        @media (max-width: 576px) { .day-grid { grid-template-columns: repeat(2, 1fr); } }

        .day-checkbox { display: none; }
        .day-label {
            display: flex;
            align-items: center; justify-content: center;
            width: 100%; padding: 12px 6px; margin: 0;
            border: 1px solid var(--border-color); border-radius: 12px; cursor: pointer;
            font-weight: 600; font-size: 0.95rem;
            transition: all 0.2s ease; background: var(--bg-canvas); color: var(--text-muted);
        }

        .day-label:hover {
            background-color: var(--bg-surface);
            border-color: var(--text-muted); transform: translateY(-2px);
        }

        .day-checkbox:checked + .day-label {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-color: var(--primary); color: white;
            box-shadow: 0 6px 12px rgba(var(--bs-primary-rgb), 0.25);
            transform: translateY(-2px);
        }

        .btn-submit-premium {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 14px;
            padding: 0.9rem;
            font-size: 1.05rem;
            font-weight: 600;
            box-shadow: 0 8px 20px rgba(var(--bs-primary-rgb), 0.25);
            transition: all 0.3s ease;
            color: white;
        }

        .btn-submit-premium:disabled {
            background: var(--bg-canvas);
            box-shadow: none;
            color: var(--text-muted);
            border: 1px solid var(--border-color);
            transform: none;
            cursor: not-allowed;
        }

        .btn-submit-premium:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(var(--bs-primary-rgb), 0.35);
        }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center mb-4">
        <div class="col-xl-8 col-lg-10">

            <div class="d-flex align-items-center mb-4 mt-2">
                <div>
                    <h4 class="fw-bold text-dark mb-0">Bölmeye Program Ekle</h4>
                    <p class="text-muted mb-0" style="font-size: 0.95rem;">Cihazınızdaki fiziksel bölmeleri ilaçlarınızla eşleştirin.</p>
                </div>
            </div>

            <div class="premium-card p-4">
                <form action="{{ route('ilac.store') }}" method="POST" id="ilacForm">
                    @csrf

                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <span class="step-badge">1</span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">Bölmeyi Seçin</h6>
                        </div>

                        <div class="box-grid">
                            @foreach($bolmeler as $bolme)
                                @php $doluMu = $bolme->zamanlamalar->isNotEmpty(); @endphp
                                <div class="compartment-box {{ $doluMu ? 'full' : 'empty' }}" data-id="{{ $bolme->id }}">
                                    <i class="bi bi-box-seam box-icon"></i>
                                    <h6 class="fw-bold text-dark mb-2" style="font-size: 1.05rem;">Bölme {{ $bolme->bolme_no }}</h6>
                                    @if($doluMu)
                                        <span class="badge border px-2 py-1 text-muted" style="font-size: 0.85rem; background-color: var(--bg-surface);">Dolu</span>
                                    @else
                                        <span class="badge border px-2 py-1" style="font-size: 0.85rem; color: var(--primary); background-color: rgba(var(--bs-primary-rgb), 0.1);">Müsait</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="bolme_id" id="selected_bolme_id" required>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <span class="step-badge">2</span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">İlaç ve Zaman Planı</h6>
                        </div>

                        <div class="row g-3 p-3 rounded-3 border" style="background-color: var(--bg-canvas); border-color: var(--border-color);">
                            <div class="col-md-8">
                                <label class="form-label">Kütüphaneden Seç</label>
                                <select name="ilac_id" class="form-select modern-input" required>
                                    <option value="" disabled selected>Kayıtlı ilaçlardan birini seçin...</option>
                                    @foreach($ilaclar as $ilac)
                                        <option value="{{ $ilac->id }}">{{ $ilac->ilac_adi }} ({{ $ilac->dozaj }})</option>
                                    @endforeach
                                </select>
                                <div class="mt-2 text-end">
                                    <a href="{{ route('ayarlar.index') }}" class="text-decoration-none fw-medium" style="font-size: 0.85rem; color: var(--primary);">Listede yoksa ekle</a>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Alınacak Saat</label>
                                <input type="time" name="alinacak_saat" class="form-control modern-input" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <span class="step-badge">3</span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">Hangi Günler Alınacak?</h6>
                        </div>

                        <div class="day-grid">
                            @foreach(['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'] as $gun)
                                <div>
                                    <input type="checkbox" class="day-checkbox" id="gun_{{ $gun }}" name="gunler[]" value="{{ $gun }}">
                                    <label class="day-label" for="gun_{{ $gun }}">{{ mb_substr($gun, 0, 3, 'UTF-8') }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="btn btn-submit-premium w-100 mt-2" id="submitBtn" disabled>
                        Cihaz Programını Kaydet
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('submitBtn');
            const bolmeInput = document.getElementById('selected_bolme_id');
            const checkboxes = document.querySelectorAll('.day-checkbox');
            const emptyBoxes = document.querySelectorAll('.compartment-box.empty');

            const validateForm = () => {
                const isReady = bolmeInput.value && Array.from(checkboxes).some(cb => cb.checked);
                btn.disabled = !isReady;
            };

            emptyBoxes.forEach(box => {
                box.addEventListener('click', function() {
                    const selected = document.querySelector('.compartment-box.selected');
                    if(selected) selected.classList.remove('selected');

                    this.classList.add('selected');
                    bolmeInput.value = this.dataset.id;
                    validateForm();
                });
            });

            checkboxes.forEach(cb => cb.addEventListener('change', validateForm));

            // Sayfa yüklendiğinde mevcut durumu kontrol et
            validateForm();
        });
    </script>
@endpush
