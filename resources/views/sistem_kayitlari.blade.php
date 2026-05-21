@extends('layout')

@push('styles')
    <style>
        /* =========================================
           SİSTEM KAYITLARI - TABLO UYUMU
           ========================================= */
        .premium-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 8px 25px -10px rgba(0, 0, 0, 0.02);
            overflow: hidden;
        }

        /* Tablo Karanlık Mod Optimizasyonu */
        .custom-table {
            color: var(--text-main);
            border-color: var(--border-color);
        }

        .custom-table thead th {
            background-color: var(--bg-canvas);
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color);
        }

        .custom-table tbody td {
            background-color: transparent;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
            vertical-align: middle;
        }

        .custom-table tbody tr:hover td {
            background-color: rgba(var(--bs-primary-rgb), 0.02);
        }

        .custom-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Sayfalama (Pagination) Mod Uyumları */
        .pagination {
            --bs-pagination-bg: var(--bg-surface);
            --bs-pagination-border-color: var(--border-color);
            --bs-pagination-color: var(--text-main);
            --bs-pagination-hover-bg: var(--bg-canvas);
            --bs-pagination-hover-color: var(--primary);
            --bs-pagination-hover-border-color: var(--border-color);
            --bs-pagination-active-bg: var(--primary);
            --bs-pagination-active-border-color: var(--primary);
            --bs-pagination-disabled-bg: var(--bg-canvas);
            --bs-pagination-disabled-border-color: var(--border-color);
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 mt-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">Tüm Sistem Kayıtları</h4>
            <p class="text-muted mb-0" style="font-size: 0.95rem;">Cihazınızda gerçekleşen tüm geçmiş işlemleri buradan inceleyebilirsiniz.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn rounded-pill px-4 shadow-sm" style="background-color: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-main); font-weight: 500; font-size: 0.95rem;">
            <i class="bi bi-arrow-left me-1"></i> Geri Dön
        </a>
    </div>

    <div class="premium-card p-0">
        <div class="card-body p-4">
            @if($loglar->count() > 0)
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead>
                        <tr>
                            <th class="border-0 rounded-start-3 py-3 px-4">İşlem Durumu</th>
                            <th class="border-0 py-3">Bölme Bilgisi</th>
                            <th class="border-0 rounded-end-3 py-3 text-end px-4">Tarih & Saat</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($loglar as $log)
                            <tr>
                                <td class="py-3 px-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 36px; height: 36px; background-color: rgba(var(--bs-primary-rgb), 0.1); color: var(--primary);">
                                            <i class="bi bi-info-circle-fill fs-5"></i>
                                        </div>
                                        <span class="fw-bold text-dark" style="font-size: 1.05rem;">{{ $log->durum }}</span>
                                    </div>
                                </td>
                                <td class="py-3">
                                        <span class="badge border px-3 py-2 rounded-pill text-muted" style="background-color: var(--bg-canvas); border-color: var(--border-color) !important; font-size: 0.85rem;">
                                            Bölme {{ $log->bolme->bolme_no ?? 'Bilinmiyor' }}
                                        </span>
                                </td>
                                <td class="py-3 text-end px-4">
                                        <span class="d-block fw-bold text-dark" style="font-size: 0.95rem;">
                                            {{ \Carbon\Carbon::parse($log->islem_zamani)->locale('tr')->translatedFormat('d F Y, H:i') }}
                                        </span>
                                    <span class="text-muted" style="font-size: 0.85rem;">
                                            {{ \Carbon\Carbon::parse($log->islem_zamani)->locale('tr')->diffForHumans() }}
                                        </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $loglar->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5 opacity-50">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; background-color: var(--bg-canvas); border: 1px solid var(--border-color);">
                        <i class="bi bi-journal-x text-muted" style="font-size: 2.5rem;"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Kayıt Bulunamadı</h5>
                    <p class="text-muted mb-0" style="font-size: 0.95rem;">Sistemde henüz kaydedilmiş bir işlem hareketi yok.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
