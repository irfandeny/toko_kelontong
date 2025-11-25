@extends('layouts.app')

@section('title', 'Detail Penjualan')

@section('content')
<h2 class="mb-4"><i class="bi bi-receipt"></i> Detail Penjualan</h2>

<div class="mb-3">
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-4">
                <strong>Invoice:</strong><br>{{ $sale->invoice_number }}
            </div>
            <div class="col-md-4">
                <strong>Tanggal:</strong><br>{{ $sale->sale_date }}
            </div>
            <div class="col-md-4">
                <strong>Pelanggan:</strong><br>{{ $sale->customer_name ?? '-' }}
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-md-8">
                <strong>Catatan:</strong><br>{{ $sale->notes ?? '-' }}
            </div>
            <div class="col-md-4">
                <strong>Total:</strong><br>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}<br>
                <strong>Dibayar:</strong> Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}<br>
                <strong>Kembalian:</strong> Rp {{ number_format($sale->change_amount, 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Barang</th>
                    <th>Harga Jual</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleDetails as $detail)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $detail->product->name ?? '-' }}</td>
                    <td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
