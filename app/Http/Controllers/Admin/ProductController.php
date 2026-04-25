<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Requests\StoreStockRequest;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index()
    {
        return $this->list();
    }
    
    /**
     * Display a listing of products.
     */
    public function list()
    {
        try {
            $products = Product::where('deleted', false)->get();
            return view('admin.pages.products.list', compact('products'));
        } catch (\Throwable $th) {
            Log::error('Failed to list products: ' . $th->getMessage());
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to list products',
            ]);
        }
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('admin.pages.products.create');
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreRequest $request)
{
    try {
        DB::beginTransaction();

        // Handle Image Upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('products', $imageName, 'public');
        }

        // Create Product Record with default values
        $product = Product::create([
            'uuid' => Str::uuid(),
            'name' => $request->name,
            'purchase_price' => 0, // Default value
            'sale_price' => 0,      // Default value
            'stock' => 0,
            'image' => $imagePath,
            'description' => $request->description,
            'net_weight' => 0,      // Default value
            'price_40kg' => 0,      // Default value
        ]);

        // Create Initial Stock History
        StockHistory::create([
            'uuid' => Str::uuid(),
            'date' => now(),
            'product_id' => $product->id,
            'quantity' => 0,
            'type' => 'in',
            'description' => 'Product Created (Initial Stock 0)',
        ]);

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Product created successfully',
            'redirect' => route('products.list'),
        ]);
    } catch (\Throwable $th) {
        DB::rollback();
        Log::error('Failed to create product: ' . $th->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'Failed to create product: ' . $th->getMessage(),
        ]);
    }
}
    /**
     * Display the specified product.
     */
    public function view($uuid)
    {
        try {
            $product = Product::where('uuid', $uuid)->firstOrFail();
            return view('admin.pages.products.view', compact('product'));
        } catch (\Throwable $th) {
            return redirect()->back()->with(['status' => false, 'message' => 'Product not found']);
        }
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($uuid)
    {
        try {
            $product = Product::where('uuid', $uuid)->firstOrFail();
            return view('admin.pages.products.edit', compact('product'));
        } catch (\Throwable $th) {
            return redirect()->back()->with(['status' => false, 'message' => 'Product not found']);
        }
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateRequest $request, $uuid)
    {
        try {
            DB::beginTransaction();
            $product = Product::where('uuid', $uuid)->firstOrFail();

            $imagePath = $product->image;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('products', $imageName, 'public');
            }

            $product->update([
                'name' => $request->name,
                'purchase_price' => $request->purchase_price,
                'sale_price' => $request->sale_price,
                'image' => $imagePath,
                'description' => $request->description,
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully',
                'redirect' => route('products.list'),
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error('Update Error: ' . $th->getMessage());
            return response()->json(['status' => false, 'message' => 'Update failed']);
        }
    }

    /**
     * Add manual stock.
     */
    public function addStock(StoreStockRequest $request)
    {
        try {
            $product = Product::where('uuid', $request->uuid)->firstOrFail();

            // Update the stock in products table
            $product->stock += $request->stock;
            $product->save();

            // Record history without current_stock column
            StockHistory::create([
                'uuid' => Str::uuid(),
                'date' => now(),
                'product_id' => $product->id,
                'quantity' => $request->stock,
                'type' => 'in',
                'description' => 'Manual Stock Update',
            ]);

            return response()->json(['status' => true, 'message' => 'Stock updated successfully']);
        } catch (\Throwable $th) {
            Log::error('Stock Add Error: ' . $th->getMessage());
            return response()->json(['status' => false, 'message' => 'Stock update failed']);
        }
    }

    public function destroy($uuid)
    {
        // Find the product by its UUID
        $product = Product::where('uuid', $uuid)->firstOrFail();

        // Delete the product (and potentially its image from storage)
        if ($product->image) {
            \Storage::disk('public')->delete($product->image);
        }
        
        $product->delete();

        // Check if the request is AJAX (from our new script)
        if (request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Product deleted successfully!'
            ]);
        }

        // Fallback for standard form submissions
        return redirect()->route('products.list')->with('success', 'Product deleted successfully!');
    }
}