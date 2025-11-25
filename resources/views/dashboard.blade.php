@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="mb-4">Dashboard Toko Kelontong</h1>
    <div class="row g-4">
        @php
            $cards = [
                ['count' => $categories_count, 'label' => 'Kategori', 'icon' => 'bi-tags', 'bg' => 'bg-gradient-primary', 'route' => 'categories.index'],
                ['count' => $suppliers_count, 'label' => 'Supplier', 'icon' => 'bi-people', 'bg' => 'bg-gradient-success', 'route' => 'suppliers.index'],
                ['count' => $products_count, 'label' => 'Produk', 'icon' => 'bi-box-seam', 'bg' => 'bg-gradient-warning', 'route' => 'products.index'],
                ['count' => $purchases_count, 'label' => 'Pembelian', 'icon' => 'bi-cart-plus', 'bg' => 'bg-gradient-danger', 'route' => 'purchases.index'],
                ['count' => $sales_count, 'label' => 'Penjualan', 'icon' => 'bi-cash-stack', 'bg' => 'bg-gradient-info', 'route' => 'sales.index'],
            ];
        @endphp

        @foreach($cards as $card)
            <div class="col-md-4 col-lg-3">
                <a href="{{ route($card['route']) }}" class="text-decoration-none">
                    <div class="card text-white {{ $card['bg'] }} shadow-sm p-4 rounded dashboard-card">
                        <div class="d-flex align-items-center">
                            <i class="bi {{ $card['icon'] }}" style="font-size: 3rem; opacity: 0.9;"></i>
                            <div class="ms-3">
                                <h2 class="mb-0 fw-bold">{{ $card['count'] }}</h2>
                                <p class="mb-0 opacity-90">{{ $card['label'] }}</p>
                            </div>
                        </div>
                        <div class="mt-3 text-end">
                            <small class="opacity-75">Lihat Detail <i class="bi bi-arrow-right"></i></small>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <!-- Tambahan: Tombol Quick Actions -->
    <div class="row mt-5">
        <div class="col-12">
            <h4 class="mb-3">Quick Actions</h4>
        </div>
        <div class="col-md-3">
            <a href="{{ route('products.create') }}" class="btn btn-primary w-100 py-3">
                <i class="bi bi-plus-circle"></i> Tambah Produk
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('purchases.create') }}" class="btn btn-success w-100 py-3">
                <i class="bi bi-cart-plus"></i> Buat Pembelian
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('sales.create') }}" class="btn btn-warning w-100 py-3">
                <i class="bi bi-cash-stack"></i> Transaksi Penjualan
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('categories.create') }}" class="btn btn-info w-100 py-3">
                <i class="bi bi-tags"></i> Tambah Kategori
            </a>
        </div>
    </div>

    <style>
        .bg-gradient-primary { background: linear-gradient(135deg, #667eea, #764ba2); }
        .bg-gradient-success { background: linear-gradient(135deg, #34d399, #10b981); }
        .bg-gradient-warning { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
        .bg-gradient-danger { background: linear-gradient(135deg, #f87171, #ef4444); }
        .bg-gradient-info { background: linear-gradient(135deg, #60a5fa, #3b82f6); }

        .dashboard-card {
            transition: all 0.3s ease;
            border: none;
        }

        .dashboard-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.25);
        }

        a.text-decoration-none:hover .dashboard-card {
            opacity: 0.95;
        }
    </style>
@endsection
