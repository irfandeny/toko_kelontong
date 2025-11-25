@extends('layouts.app')

@section('title', 'Edit Pembelian')

@section('content')
<h2 class="mb-4"><i class="bi bi-pencil"></i> Edit Pembelian Stok</h2>

<div class="card">
    <div class="card-body">
        <form action="{{ route('purchases.update', $purchase) }}" method="POST" id="purchaseEditForm">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">No Invoice *</label>
                    <input type="text" name="invoice_number" class="form-control" value="{{ $purchase->invoice_number }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Supplier *</label>
                    <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ old('supplier_id', $purchase->supplier_id)==$sup->id?'selected':'' }}>
                                {{ $sup->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal *</label>
                    <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror"
                           value="{{ old('purchase_date', $purchase->purchase_date) }}" required>
                    @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                          placeholder="Catatan opsional">{{ old('notes', $purchase->notes) }}</textarea>
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
                        <!-- Rows will be injected by JS -->
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
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update</button>
                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Batal</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const productsData = @json($productsData);
const existingDetails = @json($existingDetails);

function formatNumber(num) { return num.toLocaleString('id-ID'); }

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

function addRow(initial=null) {
    const tbody = document.getElementById('detailBody');
    const tr = document.createElement('tr');

    const productOptions = productsData.map(p =>
        `<option value="${p.id}" data-default="${p.default_price}">${p.name}</option>`
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

    // set initial values if provided
    const selectEl = tr.querySelector('.product-select');
    const priceEl = tr.querySelector('.price-input');
    const qtyEl = tr.querySelector('.qty-input');

    if (initial) {
        selectEl.value = String(initial.product_id);
        priceEl.value = initial.price;
        qtyEl.value = initial.quantity;
        tr.querySelector('.row-subtotal').value = (initial.price * initial.quantity).toFixed(2);
        recalcTotals();
    }

    selectEl.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const defPrice = selected.getAttribute('data-default');
        if (defPrice) {
            priceEl.value = defPrice;
            qtyEl.value = 1;
            tr.querySelector('.row-subtotal').value = parseFloat(defPrice).toFixed(2);
            recalcTotals();
        }
    });

    tr.querySelector('.remove-row').addEventListener('click', () => {
        tr.remove();
        recalcTotals();
    });
}

// Initialize rows from existing details
if (existingDetails && existingDetails.length) {
    existingDetails.forEach(d => addRow(d));
} else {
    addRow();
}

// Event listener for "Tambah Baris" button
document.getElementById('addRowBtn').addEventListener('click', () => {
    addRow();
});
</script>
@endpush
@endsection
