<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
       return ProductResource::collection(Product::all());
    }

    public function store(Request $request)
    {
        $product = $request->validate([
            'name' => 'required',
            'price' => 'required',
        ]);

       return Product::create($product);
    }

    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    public function update(Product $product, Request $request)
    {
        $product->update($request->all());
        return new ProductResource($product);
    }
}
