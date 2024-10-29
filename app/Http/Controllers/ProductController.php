<?php

namespace App\Http\Controllers;

use App\Jobs\NewProductNotifyJob;
use App\Models\Product;
use app\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::paginate(10);
        //$products = Product::published()->paginate(10);
        return view('product', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('product-create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, YouTubeService $youTubeService)
    {
        $product = $request->validate([
            'name' => 'required',
            'price' => 'required',
            'youtube_id' => 'nullable',
        ]);

        if ($request->youtube_id) {
            $product['youtube_thumbnail'] = $youTubeService->getThumbnailByID($request->youtube_id);
        }

       $product = Product::create($product);

        if (request()->hasFile('photo')) {
            $fileName = request()->file('photo')->getClientOriginalName();
            request()->file('photo')->storeAs('product/photos', $fileName);
            $product->update(['photo' => $fileName]);
        }

        NewProductNotifyJob::dispatch($product);

        return redirect()->route('products.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        return view('product-edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $productData = $request->validate([
            'name' => 'required',
            'price' => 'required',
        ]);

        $product->update($productData);
        return redirect()->route('products.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index');
    }
}
