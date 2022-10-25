<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ProductResource::collection(Product::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        $image = $request->file('image');
        $imageName = Str::random(32).'.'.$image->getClientOriginalExtension();

        $create = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imageName
        ]);

        Storage::disk('public')->put($imageName, file_get_contents($request->image));

        return new ProductResource($create);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, Product $product)
    {
        $storage = Storage::disk('public');

        $product->name = $request->name;
        $product->description = $request->description;

        if($image = $request->file('image')){
            if($storage->exists($product->image)){
                $storage->delete($product->image);
            }
            $imageName = Str::random(32).'.'.$image->getClientOriginalExtension();
            Storage::disk('public')->put($imageName, file_get_contents($request->image));
            $product->image = $imageName;
        }
        $product->save();
        return new ProductResource($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $storage = Storage::disk('public');
        if($product->delete()){
            $storage->delete($product->image);
            return response()->json([
                'message' => 'Product deleted successfuly'
            ]);
        }

    }
}
