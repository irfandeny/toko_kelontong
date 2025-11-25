@extends('layouts.app')

@section('title', 'Data Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cart-plus"></i> Data Pembelian Stok</h2>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Tambah Pembelian
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control" placeholder="Cari invoice..."
                       value="{{ request('q') }}">
            </div>
            <div class="col-md-4">
                <select name="supplier" class="form-select">
                    <option value="">-- Filter Supplier --</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-secondary" type="submit">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>ID</th>
                    <th>Invoice</th>
                    <th>Supplier</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th width="20%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                <tr>
                    <td>{{ $loop->iteration + ($purchases->currentPage()-1) * $purchases->perPage() }}</td>
                    <td>{{ $purchase->id }}</td>
                    <td><strong>{{ $purchase->invoice_number }}</strong></td>
                    <td>{{ $purchase->supplier->name ?? '-' }}</td>
                    <td>{{ $purchase->purchase_date }}</td>
                    <td>Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" class="d-inline" data-confirm="Yakin ingin menghapus pembelian invoice '{{ $purchase->invoice_number }}'?">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-inbox" style="font-size:3rem;"></i>
                        <p class="mt-2">Belum ada data pembelian</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            {{ $purchases->links() }}
        </div>
    </div>
</div>
@endsection
