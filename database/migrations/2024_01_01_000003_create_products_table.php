<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->decimal('purchase_price', 15, 2); // Harga beli
            $table->decimal('selling_price', 15, 2);  // Harga jual
            $table->integer('stock')->default(0);
            $table->string('unit'); // Satuan: pcs, bungkus, botol, dll
            $table->string('image')->nullable(); // Foto barang
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
