<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index');
    }

    public function getProducts()
    {
        $products = Product::orderBy('created_at', 'desc')->get();

        // Prepare products data
        $productsData = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'quantity_in_stock' => (int) $product->quantity_in_stock,
                'price_per_item' => (float) $product->price_per_item,
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                'total_value' => (float) ($product->quantity_in_stock * $product->price_per_item),
            ];
        });

        // Calculate grand total
        $grandTotal = $products->sum(function ($product) {
            return $product->quantity_in_stock * $product->price_per_item;
        });

        // Save products to JSON file
        $this->saveToJson($products);

        return response()->json([
            'products' => $productsData,
            'grand_total' => (float) $grandTotal,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'quantity_in_stock' => 'required|integer|min:0',
            'price_per_item' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = Product::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Product added successfully!',
            'product' => [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'quantity_in_stock' => (int) $product->quantity_in_stock,
                'price_per_item' => (float) $product->price_per_item,
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                'total_value' => (float) ($product->quantity_in_stock * $product->price_per_item),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'quantity_in_stock' => 'required|integer|min:0',
            'price_per_item' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = Product::findOrFail($id);
        $product->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully!',
            'product' => [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'quantity_in_stock' => (int) $product->quantity_in_stock,
                'price_per_item' => (float) $product->price_per_item,
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                'total_value' => (float) ($product->quantity_in_stock * $product->price_per_item),
            ],
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully!',
        ]);
    }

    private function saveToJson($products)
    {
        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'quantity_in_stock' => (int) $product->quantity_in_stock,
                'price_per_item' => (float) $product->price_per_item,
                'datetime_submitted' => $product->created_at->toIso8601String(),
                'total_value' => (float) ($product->quantity_in_stock * $product->price_per_item),
            ];
        })->toArray();

        Storage::disk('public')->put('products.json', json_encode($data, JSON_PRETTY_PRINT));
    }
}
