<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductsController extends Controller
{
    public function index()
    {
        return response()->json(Product::all());
      
    }
    
    public function show(string $id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }
}
