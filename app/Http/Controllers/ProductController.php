<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Filter search
        if ($search = $request->input('q')) {
            $query->where('name', 'like', "%$search%");
        }

        // Filter kategori
        if ($cat = $request->input('category')) {
            $query->where('category_id', $cat);
        }

        $products = $query->latest()->paginate(10)->appends($request->query());
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'image' => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambah');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'image' => 'nullable|image|max:2048|mimes:jpg,jpeg,png',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products');
        }

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diubah');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus');
    }
}
