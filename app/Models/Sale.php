<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'sale_date',
        'total_amount',
        'paid_amount',
        'change_amount',
        'customer_name',
        'notes'
    ];

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }
}
