@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<h5 class="fw-bold mb-4">Dashboard</h5>

{{-- ── BARIS 1: Summary Cards ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Cash & Bank Balance</div>
                <div class="fw-bold fs-6 text-primary">
                    Rp {{ number_format($cashBalance,0,',','.') }}
                </div>
                <div class="text-muted" style="font-size:11px">Total of all accounts</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Accounts Receivable</div>
                <div class="fw-bold fs-6 text-warning">
                    Rp {{ number_format($totalAR,0,',','.') }}
                </div>
                <div class="text-muted" style="font-size:11px">Unpaid + Installments</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Accounts Payable</div>
                <div class="fw-bold fs-6 text-danger">
                    Rp {{ number_format($totalAP,0,',','.') }}
                </div>
                <div class="text-muted" style="font-size:11px">Unpaid + Installments</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Low Stock Items</div>
                <div class="fw-bold fs-6 {{ $lowStock > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $lowStock }} item
                </div>
                <div class="text-muted" style="font-size:11px">Qty &lt; 10</div>
            </div>
        </div>
    </div>
</div>

{{-- ── BARIS 2: Bulan Ini + Pending ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Sales This Month</div>
                <div class="fw-bold text-success">
                    Rp {{ number_format($salesThisMonth,0,',','.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Purchase This Month</div>
                <div class="fw-bold text-danger">
                    Rp {{ number_format($purchaseThisMonth,0,',','.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 {{ $pendingSales > 0 ? 'border-warning' : '' }}">
            <div class="card-body">
                <div class="text-muted small mb-1">Sales Approval</div>
                <div class="fw-bold {{ $pendingSales > 0 ? 'text-warning' : 'text-muted' }}">
                    {{ $pendingSales }} waiting
                </div>
                @if($pendingSales > 0)
                <a href="{{ route('sales.index', ['status'=>'pending_approval']) }}"
                   class="btn btn-xs btn-warning btn-sm mt-1" style="font-size:11px">Review</a>
                @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 {{ $pendingPurchases > 0 ? 'border-warning' : '' }}">
            <div class="card-body">
                <div class="text-muted small mb-1">Purchase Approval</div>
                <div class="fw-bold {{ $pendingPurchases > 0 ? 'text-warning' : 'text-muted' }}">
                    {{ $pendingPurchases }} waiting
                </div>
                @if($pendingPurchases > 0)
                <a href="{{ route('purchases.index', ['status'=>'pending_approval']) }}"
                   class="btn btn-xs btn-warning btn-sm mt-1" style="font-size:11px">Review</a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── BARIS 3: Chart Penjualan vs Pembelian + Laba ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold small">
                Sales vs Purchases (Last 6 Months)
            </div>
            <div class="card-body">
                <canvas id="chartSalesPurchase" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold small">
                Gross Profit (Last 6 Months)
            </div>
            <div class="card-body">
                <canvas id="chartProfit" height="120"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── BARIS 4: Chart Arus Kas Harian ── --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold small">
                Daily Cash Flow — Last 30 Days
            </div>
            <div class="card-body">
                <canvas id="chartCashFlow" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── BARIS 5: Transaksi Terbaru ── --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white fw-bold small">Recent Transactions</div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Type</th>
                    <th>No. Document</th>
                    <th>Partner</th>
                    <th>Date</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
            @forelse($recentTransactions as $trx)
                <tr>
                    <td>
                        <span class="badge bg-{{ $trx['type']==='Sales' ? 'success' : 'primary' }}">
                            {{ $trx['type'] }}
                        </span>
                    </td>
                    <td><code>{{ $trx['no'] }}</code></td>
                    <td>{{ $trx['partner'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($trx['date'])->format('d/m/Y') }}</td>
                    <td class="text-end">Rp {{ number_format($trx['amount'],0,',','.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Belum ada transaksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chartSalesPurchase'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($months) !!},
        datasets: [
            {
                label: 'Sales',
                data: {!! json_encode($salesData) !!},
                backgroundColor: 'rgba(25, 135, 84, 0.7)',
                borderColor: 'rgba(25, 135, 84, 1)',
                borderWidth: 1,
                borderRadius: 3,
            },
            {
                label: 'Purchases',
                data: {!! json_encode($purchData) !!},
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1,
                borderRadius: 3,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'jt'
                }
            }
        }
    }
});

// ── Laba Kotor (Line Chart) ──
new Chart(document.getElementById('chartProfit'), {
    type: 'line',
    data: {
        labels: {!! json_encode($months) !!},
        datasets: [{
            label: 'Gross Profit',
            data: {!! json_encode($profitData) !!},
            borderColor: 'rgba(255, 193, 7, 1)',
            backgroundColor: 'rgba(255, 193, 7, 0.15)',
            borderWidth: 2,
            pointRadius: 4,
            fill: true,
            tension: 0.3,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                ticks: {
                    callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'jt'
                }
            }
        }
    }
});

new Chart(document.getElementById('chartCashFlow'), {
    type: 'line',
    data: {
        labels: {!! json_encode($cashDates) !!},
        datasets: [
            {
                label: 'Cash In',
                data: {!! json_encode($cashIn) !!},
                borderColor: 'rgba(25, 135, 84, 0.9)',
                backgroundColor: 'rgba(25, 135, 84, 0.08)',
                borderWidth: 1.5,
                pointRadius: 2,
                fill: true,
                tension: 0.2,
            },
            {
                label: 'Cash Out',
                data: {!! json_encode($cashOut) !!},
                borderColor: 'rgba(220, 53, 69, 0.9)',
                backgroundColor: 'rgba(220, 53, 69, 0.08)',
                borderWidth: 1.5,
                pointRadius: 2,
                fill: true,
                tension: 0.2,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'jt'
                }
            }
        }
    }
});
</script>
@endpush
@endsection