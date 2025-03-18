@php
    $currentDay = null;
@endphp

@foreach ($systemFunds as $systemFund)
    @if ($currentDay !== $systemFund->day)
        @php
            $currentDay = $systemFund->day;
        @endphp
        <div class="col-12">
            <div class="bg-light p-3 rounded">
                <h5 class="mb-0 text-center text-primary">📅 Ngày:
                    {{ date('d/m/Y', strtotime($currentDay)) }}</h5>
            </div>
        </div>
    @endif

    <div class="col-12 col-md-12">
        <div class="card shadow-sm mb-2">
            <div class="card-body">
                <div>
                    <div class="d-flex">
                        <div class="col-11">
                            <h5 class="card-title fs-15 text-secondary mb-3">
                                Biến động số dư</h5>
                        </div>
                        <div class="col-1 text-center">
                            <span class="text-muted">
                                {{ $systemFund->created_at ? \Carbon\Carbon::parse($systemFund->created_at)->format('H:i:s') : '00:00:00' }}
                            </span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-11">
                            <h6 class="mb-1 text-success">
                                {{ $systemFund->type == 'commission_received' ? '+' : '-' }}
                                {{ number_format($systemFund->total_amount ?? 0) }}
                                VND
                            </h6>
                            <p class="text-muted mb-0">
                                {{ $systemFund->description ?? 'Không có mô tả' }}
                            </p>
                        </div>
                        <div class="col-1 text-center">
                            <a href="{{ route('admin.wallets.show', $systemFund->id) }}">
                                <button class="btn btn-sm btn-info edit-item-btn">
                                    <span class="ri-eye-line"></span>
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

<div class="col-12 col-md-12">
    <div class="d-flex mt-4 justify-content-center">
        <button id="load-more" class="btn btn-sm btn-primary px-4 rounded-pill">
            <i class="ri-refresh-line me-1"></i> Xem thêm
        </button>
    </div>
</div>
