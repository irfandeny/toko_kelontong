<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::query();

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%$search%")
                  ->orWhere('customer_name', 'like', "%$search%");
            });
        }

        // HINDARI withQueryString() agar tidak error di Intelephense
        $sales = $query->latest()->paginate(10);
        $sales->appends($request->all());

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $products = Product::with('category')->orderBy('name')->get();
        $productsData = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name . ' (' . ($p->category->name ?? '-') . ')',
                'price' => (float) $p->selling_price,
                'stock' => (int) $p->stock,
            ];
        });

        $nextInvoice = $this->generateInvoiceNumber();
        return view('sales.create', compact('products', 'productsData', 'nextInvoice'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number'    => 'required|string|unique:sales,invoice_number',
            'sale_date'         => 'required|date',
            'customer_name'     => 'nullable|string|max:255',
            'notes'             => 'nullable|string',
            'product_id'        => 'required|array|min:1',
            'product_id.*'      => 'required|exists:products,id',
            'quantity'          => 'required|array|min:1',
            'quantity.*'        => 'required|integer|min:1',
            'price'             => 'required|array|min:1',
            'price.*'           => 'required|numeric|min:0',
            'paid_amount'       => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {
            $total = 0;

            // Validasi stok
            foreach ($validated['product_id'] as $i => $pid) {
                $qty = (int)$validated['quantity'][$i];
                $product = Product::findOrFail($pid);
                if ($product->stock < $qty) {
                    throw new \Exception("Stok barang '{$product->name}' tidak cukup.");
                }
            }

            $sale = Sale::create([
                'invoice_number' => $validated['invoice_number'],
                'sale_date'      => $validated['sale_date'],
                'total_amount'   => 0,
                'paid_amount'    => 0,
                'change_amount'  => 0,
                'customer_name'  => $validated['customer_name'] ?? null,
                'notes'          => $validated['notes'] ?? null,
            ]);

            // Detail penjualan
            foreach ($validated['product_id'] as $i => $pid) {
                $qty = (int)$validated['quantity'][$i];
                $price = (float)$validated['price'][$i];
                $subtotal = $qty * $price;
                $total += $subtotal;

                SaleDetail::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $pid,
                    'quantity'   => $qty,
                    'price'      => $price,
                    'subtotal'   => $subtotal,
                ]);

                Product::where('id', $pid)->decrement('stock', $qty);
            }

            $paid = (float)$validated['paid_amount'];
            $change = max($paid - $total, 0);

            $sale->update([
                'total_amount'  => $total,
                'paid_amount'   => $paid,
                'change_amount' => $change,
            ]);
            });

            return redirect()->route('sales.index')
                ->with('success', 'Transaksi penjualan berhasil disimpan!');
        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(Sale $sale)
    {
        $sale->load('saleDetails.product.category');
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $products = Product::with('category')->orderBy('name')->get();
        $sale->load('saleDetails.product');
        return view('sales.edit', compact('sale', 'products'));
    }

    public function update(Request $request, Sale $sale)
    {
        return back()->with('error', 'Fitur edit penjualan belum diaktifkan.');
    }

    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            $sale->load('saleDetails');

            foreach ($sale->saleDetails as $detail) {
                Product::where('id', $detail->product_id)
                       ->increment('stock', $detail->quantity);
            }

            SaleDetail::where('sale_id', $sale->id)->delete();
            $sale->delete();
        });

        return redirect()->route('sales.index')
            ->with('success', 'Transaksi penjualan berhasil dihapus!');
    }

    private function generateInvoiceNumber(): string
    {
        $datePart = now()->format('Ymd');
        $countToday = Sale::whereDate('sale_date', now()->toDateString())->count() + 1;

        return 'INV-' . $datePart . '-' . str_pad($countToday, 3, '0', STR_PAD_LEFT);
    }
}
