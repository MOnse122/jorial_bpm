<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;


class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $products = Product::query()
            ->active()
            ->search($search)
            ->paginate(5);

        return response()->json($products);
    }


    public function show(string $id)
    {
        return response()->json([
            'metodo' => 'show',
            'id' => $id
        ]);
    }

}
