@extends('layouts.app')

@section('content')
<div class="container-fluid">

<h2 class="fw-bold mb-1">Laporan Transaksi Wisata</h2>

{{-- ================= SUMMARY CARD ================= --}}

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm p-3 rounded-4">
            <small>Total Revenue</small>
            <h3 class="text-primary fw-bold">
                Rp {{ number_format($summary['revenue'],0,',','.') }}
            </h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm p-3 rounded-4">
            <small>Total Pengunjung</small>
            <h3 class="text-success fw-bold">
                {{ $summary['pengunjung'] }}
            </h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm p-3 rounded-4">
            <small>Total Transaksi</small>
            <h3 class="fw-bold text-purple">
                {{ $summary['transaksi'] }}
            </h3>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm p-3 rounded-4">
            <small>Pending</small>
            <h3 class="fw-bold text-danger">
                {{ $summary['pending'] }}
            </h3>
        </div>
    </div>
</div>

{{-- ================= GRAFIK ================= --}}
<div class="row g-4 mb-4">

    <div class="col-md-6">
        <div class="card shadow-sm p-4 border-0 rounded-4">
            <h5 class="fw-bold mb-3">Tren Revenue & Pengunjung Harian</h5>
            <div id="revenueChart"></div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm p-4 border-0 rounded-4">
            <h5 class="fw-bold mb-3">Metode Pembayaran</h5>
            <div id="paymentChart"></div>
        </div>
    </div>

</div>

{{-- ================= TABEL ================= --}}
<div class="card shadow-sm p-4 border-0 rounded-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Daftar Transaksi</h5>

        @if(auth()->guard('web')->check())
        <button onclick="downloadPDF()"
            class="btn text-white fw-semibold px-4 py-2 rounded-4 shadow-sm"
            style="background:#2563eb;">
            <i class="bi bi-printer me-2"></i> Cetak Data
        </button>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Lokasi</th>
                    <th>Tiket</th>
                    <th>Jumlah</th>
                    <th>Pembayaran</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
            @forelse($transactions as $t)
            <tr>
                <td>{{ $t->kode }}</td>
                <td>{{ $t->tanggal }}</td>
                <td>{{ $t->customer }}</td>
                <td>{{ $t->lokasi }}</td>
                <td>{{ $t->tiket }}</td>
                <td>{{ $t->jumlah }}</td>
                <td>{{ $t->pembayaran }}</td>
                <td>Rp {{ number_format($t->total,0,',','.') }}</td>
                <td>{{ $t->status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    Belum ada data transaksi
                </td>
            </tr>
            @endforelse
            </tbody>

        </table>
    </div>

</div>

</div>

{{-- ================= SCRIPT GRAFIK ================= --}}

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const trend = @json($trend);
    const metode = @json($metodePembayaran);

    const labels = trend.map(t => t.tanggal);
    const revenue = trend.map(t => t.revenue);
    const visitors = trend.map(t => t.pengunjung);

    new ApexCharts(document.querySelector("#revenueChart"), {
        chart: { type: 'line', height: 300 },
        stroke: { curve: 'smooth' },
        series: [
            { name: 'Revenue', data: revenue },
            { name: 'Visitors', data: visitors }
        ],
        xaxis: { categories: labels }
    }).render();

    new ApexCharts(document.querySelector("#paymentChart"), {
        chart: { type: 'pie', height: 300 },
        labels: metode.map(m => m.metode),
        series: metode.map(m => m.total)
    }).render();

});


// ================= DOWNLOAD PDF TANPA RELOAD / TAB =================
function downloadPDF() {
    fetch("{{ route('admin.transaksi.cetak') }}")
    .then(response => {
        if (!response.ok) {
            throw new Error("Gagal download");
        }
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);

        const a = document.createElement("a");
        a.href = url;
        a.download = "laporan-transaksi.pdf";
        document.body.appendChild(a);
        a.click();

        a.remove();
        window.URL.revokeObjectURL(url);
    })
    .catch(err => {
        console.error(err);
        alert("Download gagal");
    });
}
</script>

@endsection