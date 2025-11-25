<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'categories_count' => Category::count(),
            'suppliers_count' => Supplier::count(),
            'products_count' => Product::count(),
            'purchases_count' => Purchase::count(),
            'sales_count' => Sale::count(),
        ]);
    }
}
