@extends('layouts.app')

@section('title', 'Transaksi Penjualan')

@section('content')
<h2 class="mb-4"><i class="bi bi-cart"></i> Transaksi Penjualan (Kasir)</h2>

<div class="card">
    <div class="card-body">
        <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
            @csrf
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">No Invoice *</label>
                    <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror"
                           value="{{ old('invoice_number', $nextInvoice) }}" readonly>
                    @error('invoice_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal *</label>
                    <input type="date" name="sale_date" class="form-control @error('sale_date') is-invalid @enderror"
                           value="{{ old('sale_date', date('Y-m-d')) }}" required>
                    @error('sale_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nama Pelanggan</label>
                    <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror"
                           value="{{ old('customer_name') }}" placeholder="Umum / Nama Pelanggan">
                    @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Catatan</label>
                    <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror"
                           value="{{ old('notes') }}" placeholder="Opsional">
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr>
            <h5>Detail Barang</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="detailsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:30%">Barang *</th>
                            <th style="width:15%">Harga Jual (Rp)</th>
                            <th style="width:15%">Qty *</th>
                            <th style="width:20%">Subtotal</th>
                            <th style="width:10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="detailBody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total</strong></td>
                            <td><input type="text" id="grandTotal" class="form-control" readonly value="Rp 0"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @error('product_id')<div class="text-danger small">{{ $message }}</div>@enderror
            @error('quantity')<div class="text-danger small">{{ $message }}</div>@enderror
            @error('price')<div class="text-danger small">{{ $message }}</div>@enderror

            <button type="button" class="btn btn-outline-primary mb-3" id="addRowBtn">
                <i class="bi bi-plus-circle"></i> Tambah Item
            </button>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Dibayar (Rp) *</label>
                    <input type="number" name="paid_amount" step="0.01" id="paidAmount"
                           class="form-control @error('paid_amount') is-invalid @enderror"
                           value="{{ old('paid_amount') }}" required>
                    @error('paid_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kembalian (Rp)</label>
                    <input type="text" id="changeAmount" class="form-control" readonly value="Rp 0">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Transaksi</button>
                <a href="{{ route('sales.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Batal</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const productsData = @json($products->map(fn($p) => [
    'id' => $p->id,
    'name' => $p->name . ' (' . ($p->category->name ?? '-') . ')',
    'price' => $p->selling_price,
    'stock' => $p->stock
]));

function formatRupiah(v){
    return 'Rp ' + Number(v).toLocaleString('id-ID');
}

function recalcTotals(){
    let grand = 0;
    document.querySelectorAll('.subtotal-field').forEach(el=>{
        grand += parseFloat(el.value || 0);
    });
    document.getElementById('grandTotal').value = formatRupiah(grand);

    const paid = parseFloat(document.getElementById('paidAmount').value || 0);
    const change = Math.max(paid - grand, 0);
    document.getElementById('changeAmount').value = formatRupiah(change);
}

function bindRow(row){
    const priceInput = row.querySelector('.price-input');
    const qtyInput   = row.querySelector('.qty-input');
    const productSelect = row.querySelector('.product-select');
    const subtotalInput = row.querySelector('.subtotal-field');

    function updateSubtotal(){
        const price = parseFloat(priceInput.value || 0);
        const qty   = parseInt(qtyInput.value || 0);
        subtotalInput.value = (price * qty).toFixed(2);
        recalcTotals();
    }

    productSelect.addEventListener('change', ()=>{
        const selected = productSelect.options[productSelect.selectedIndex];
        const pid = selected.value;
        if(!pid){ return; }
        const prod = productsData.find(p=> p.id == pid);
        if(prod){
            priceInput.value = prod.price;
            qtyInput.value = 1;
            subtotalInput.value = prod.price.toFixed(2);
            recalcTotals();
        }
    });

    priceInput.addEventListener('input', updateSubtotal);
    qtyInput.addEventListener('input', ()=>{
        // optionally check stock
        const pid = productSelect.value;
        const prod = productsData.find(p=> p.id == pid);
        const qty = parseInt(qtyInput.value || 0);
        if(prod && qty > prod.stock){
            alert('Stok tidak cukup. Stok tersedia: ' + prod.stock);
            qtyInput.value = prod.stock;
        }
        updateSubtotal();
    });
}

document.getElementById('addRowBtn').addEventListener('click', ()=>{
    const tbody = document.getElementById('detailBody');
    const tr = document.createElement('tr');
    const productOptions = productsData.map(p =>
        <option value=\"${p.id}\">${p.name} | Stok: ${p.stock}</option>
    ).join('');
    tr.innerHTML = `
        <td>
            <select name=\"product_id[]\" class=\"form-select product-select\" required>
                <option value=\"\">-- Pilih Barang --</option>
                ${productOptions}
            </select>
        </td>
        <td>
            <input type=\"number\" step=\"0.01\" name=\"price[]\" class=\"form-control price-input\" required placeholder=\"0\">
        </td>
        <td>
            <input type=\"number\" name=\"quantity[]\" class=\"form-control qty-input\" required min=\"1\" value=\"1\">
        </td>
        <td>
            <input type=\"text\" class=\"form-control subtotal-field\" readonly value=\"0\">
        </td>
        <td>
            <button type=\"button\" class=\"btn btn-sm btn-danger remove-row\"><i class=\"bi bi-x-circle\"></i></button>
        </td>
    `;
    tbody.appendChild(tr);
    bindRow(tr);
    tr.querySelector('.remove-row').addEventListener('click', ()=>{
        tr.remove();
        recalcTotals();
    });
});

document.getElementById('paidAmount').addEventListener('input', recalcTotals);
</script>
@endpush
@endsection
