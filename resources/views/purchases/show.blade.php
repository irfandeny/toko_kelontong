@extends('layouts.app')

@section('title', 'Detail Pembelian')

@section('content')
<h2 class="mb-4"><i class="bi bi-receipt"></i> Detail Pembelian</h2>

<div class="mb-3">
    <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-warning">
        <i class="bi bi-pencil"></i> Edit
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-4">
                <strong>Invoice:</strong><br>{{ $purchase->invoice_number }}
            </div>
            <div class="col-md-4">
                <strong>Supplier:</strong><br>{{ $purchase->supplier->name ?? '-' }}
            </div>
            <div class="col-md-4">
                <strong>Tanggal:</strong><br>{{ $purchase->purchase_date }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <strong>Catatan:</strong><br>{{ $purchase->notes ?? '-' }}
            </div>
            <div class="col-md-4">
                <strong>Total:</strong><br>
                <span class="fs-5">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</span>
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
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->purchaseDetails as $detail)
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
