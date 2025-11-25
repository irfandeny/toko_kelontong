@extends('layouts.app')

@section('title', 'Tambah Pembelian')

@section('content')
<h2 class="mb-4"><i class="bi bi-plus-circle"></i> Tambah Pembelian Stok</h2>

<div class="card">
    <div class="card-body">
        <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
            @csrf

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">No Invoice *</label>
                    <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror"
                           value="{{ old('invoice_number', $nextInvoice) }}" readonly>
                    @error('invoice_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Supplier *</label>
                    <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ old('supplier_id')==$sup->id?'selected':'' }}>
                                {{ $sup->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal *</label>
                    <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror"
                           value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                    @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                          placeholder="Catatan opsional">{{ old('notes') }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <hr>
            <h5>Detail Barang</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="detailsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:30%">Barang *</th>
                            <th style="width:15%">Harga (Rp) *</th>
                            <th style="width:15%">Qty *</th>
                            <th style="width:20%">Subtotal</th>
                            <th style="width:10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="detailBody">
                        <!-- Rows will be added dynamically -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total</strong></td>
                            <td>
                                <input type="text" id="grandTotal" class="form-control" readonly value="0">
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @error('product_id')<div class="text-danger small">{{ $message }}</div>@enderror
            @error('quantity')<div class="text-danger small">{{ $message }}</div>@enderror
            @error('price')<div class="text-danger small">{{ $message }}</div>@enderror

            <button type="button" class="btn btn-outline-primary mb-3" id="addRowBtn">
                <i class="bi bi-plus-circle"></i> Tambah Baris
            </button>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Batal</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const productsData = @json($products->map(fn($p) => [
    'id' => $p->id,
    'name' => $p->name . ' (' . ($p->category->name ?? '-') . ')',
    'default_price' => $p->purchase_price,
]));

function formatNumber(num) {
    return num.toLocaleString('id-ID');
}

function recalcTotals() {
    let grand = 0;
    document.querySelectorAll('.row-subtotal').forEach(el => {
        grand += parseFloat(el.value || 0);
    });
    document.getElementById('grandTotal').value = 'Rp ' + formatNumber(grand);
}

function bindRowEvents(row) {
    const priceInput = row.querySelector('.price-input');
    const qtyInput = row.querySelector('.qty-input');
    const subtotalInput = row.querySelector('.row-subtotal');

    function updateSubtotal() {
        const price = parseFloat(priceInput.value || 0);
        const qty = parseInt(qtyInput.value || 0);
        subtotalInput.value = (price * qty).toFixed(2);
        recalcTotals();
    }

    priceInput.addEventListener('input', updateSubtotal);
    qtyInput.addEventListener('input', updateSubtotal);
}

document.getElementById('addRowBtn').addEventListener('click', () => {
    const tbody = document.getElementById('detailBody');
    const tr = document.createElement('tr');

    const productOptions = productsData.map(p =>
        <option value="${p.id}" data-default="${p.default_price}">${p.name}</option>
    ).join('');

    tr.innerHTML = `
        <td>
            <select name="product_id[]" class="form-select product-select" required>
                <option value="">-- Pilih Barang --</option>
                ${productOptions}
            </select>
        </td>
        <td>
            <input type="number" step="0.01" name="price[]" class="form-control price-input" required placeholder="0">
        </td>
        <td>
            <input type="number" name="quantity[]" class="form-control qty-input" required min="1" value="1">
        </td>
        <td>
            <input type="text" class="form-control row-subtotal" readonly value="0">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger remove-row">
                <i class="bi bi-x-circle"></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    bindRowEvents(tr);

    tr.querySelector('.product-select').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const defPrice = selected.getAttribute('data-default');
        if (defPrice) {
            tr.querySelector('.price-input').value = defPrice;
            tr.querySelector('.qty-input').value = 1;
            const subtotal = parseFloat(defPrice) * 1;
            tr.querySelector('.row-subtotal').value = subtotal.toFixed(2);
            recalcTotals();
        }
    });

    tr.querySelector('.remove-row').addEventListener('click', () => {
        tr.remove();
        recalcTotals();
    });
});

</script>
@endpush
@endsection
