<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with('supplier');

        if ($search = $request->input('q')) {
            $query->where('invoice_number', 'like', '%' . $search . '%');
        }

        if ($supplierId = $request->input('supplier')) {
            $query->where('supplier_id', $supplierId);
        }

        $purchases  = $query->latest()->paginate(10);
        $purchases->appends($request->all());
        $suppliers  = Supplier::orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::with('category')->orderBy('name')->get();
        $productsData = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name . ' (' . ($p->category->name ?? '-') . ')',
                'default_price' => (float) $p->purchase_price,
            ];
        });
        $nextInvoice = $this->generateInvoiceNumber();

        return view('purchases.create', compact('suppliers', 'products', 'productsData', 'nextInvoice'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number'     => 'required|string|unique:purchases,invoice_number',
            'supplier_id'        => 'required|exists:suppliers,id',
            'purchase_date'      => 'required|date',
            'notes'              => 'nullable|string',
            'product_id'         => 'required|array|min:1',
            'product_id.*'       => 'required|exists:products,id',
            'quantity'           => 'required|array|min:1',
            'quantity.*'         => 'required|integer|min:1',
            'price'              => 'required|array|min:1',
            'price.*'            => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $total = 0;

            $purchase = Purchase::create([
                'invoice_number' => $validated['invoice_number'],
                'supplier_id'    => $validated['supplier_id'],
                'purchase_date'  => $validated['purchase_date'],
                'total_amount'   => 0,
                'notes'          => $validated['notes'] ?? null,
            ]);

            foreach ($validated['product_id'] as $index => $pid) {
                $qty   = (int)$validated['quantity'][$index];
                $price = (float)$validated['price'][$index];
                $subtotal = $qty * $price;
                $total += $subtotal;

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $pid,
                    'quantity'    => $qty,
                    'price'       => $price,
                    'subtotal'    => $subtotal,
                ]);

                // Tambah stok
                $product = Product::find($pid);
                $product->increment('stock', $qty);
            }

            $purchase->update(['total_amount' => $total]);
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Pembelian berhasil ditambahkan!');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('supplier', 'purchaseDetails.product.category');
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        // OPSIONAL: mengizinkan edit detail
        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::with('category')->orderBy('name')->get();
        $purchase->load('purchaseDetails.product');
        return view('purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        // Edit pembelian (lebih kompleks): harus kembalikan stok lama lalu tambah stok baru
        $validated = $request->validate([
            'supplier_id'        => 'required|exists:suppliers,id',
            'purchase_date'      => 'required|date',
            'notes'              => 'nullable|string',
            'product_id'         => 'required|array|min:1',
            'product_id.*'       => 'required|exists:products,id',
            'quantity'           => 'required|array|min:1',
            'quantity.*'         => 'required|integer|min:1',
            'price'              => 'required|array|min:1',
            'price.*'            => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $purchase) {
            // Revert stok lama
            $oldDetails = $purchase->purchaseDetails;
            foreach ($oldDetails as $detail) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $product->decrement('stock', $detail->quantity);
                }
            }

            // Hapus detail lama
            PurchaseDetail::where('purchase_id', $purchase->id)->delete();

            // Tambah detail baru
            $total = 0;
            foreach ($validated['product_id'] as $index => $pid) {
                $qty   = (int)$validated['quantity'][$index];
                $price = (float)$validated['price'][$index];
                $subtotal = $qty * $price;
                $total += $subtotal;

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $pid,
                    'quantity'    => $qty,
                    'price'       => $price,
                    'subtotal'    => $subtotal,
                ]);

                $product = Product::find($pid);
                $product->increment('stock', $qty);
            }

            $purchase->update([
                'supplier_id'   => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                'notes'         => $validated['notes'] ?? null,
                'total_amount'  => $total,
            ]);
        });

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Pembelian berhasil diupdate!');
    }

    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            $purchase->load('purchaseDetails');
            // Revert stok
            foreach ($purchase->purchaseDetails as $detail) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $product->decrement('stock', $detail->quantity);
                }
            }
            PurchaseDetail::where('purchase_id', $purchase->id)->delete();
            $purchase->delete();
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Pembelian berhasil dihapus!');
    }

    private function generateInvoiceNumber(): string
    {
        $datePart = now()->format('Ymd');
        $countToday = Purchase::whereDate('purchase_date', now()->toDateString())->count() + 1;
        return 'PO-' . $datePart . '-' . str_pad($countToday, 3, '0', STR_PAD_LEFT);
    }
}
