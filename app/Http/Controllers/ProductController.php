<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessProductImageJob;
use App\Services\AwsStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected AwsStorageService $storageService;

    public function __construct(AwsStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * List all products (Web View)
     */
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->expectsJson()) {
            return $this->indexApi($request);
        }

        $page = $request->input('page', 1);
        $perPage = 20;
        $category = $request->input('category');

        $query = DB::table('products')->where('status', 'active');

        if ($category) {
            $query->where('category', $category);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return view('products.index', compact('products'));
    }

    /**
     * List all products (API)
     */
    public function indexApi(Request $request): JsonResponse
    {
        $cacheKey = 'products_' . md5(json_encode($request->all()));
        
        return Cache::remember($cacheKey, 3600, function () use ($request) {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 20);
            $category = $request->input('category');

            $query = DB::table('products')->where('status', 'active');

            if ($category) {
                $query->where('category', $category);
            }

            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        });
    }

    /**
     * Get single product
     */
    public function show(int $id): JsonResponse
    {
        $product = Cache::remember("product_{$id}", 3600, function () use ($id) {
            return DB::table('products')->where('id', $id)->first();
        });

        if (!$product) {
            return response()->json([
                'success' => false,
                'error' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Create new product
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string',
            'sku' => 'required|string|unique:products,sku',
            'stock_quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|max:5120',
        ]);

        $productId = DB::table('products')->insertGetId([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'category' => $validated['category'],
            'sku' => $validated['sku'],
            'stock_quantity' => $validated['stock_quantity'],
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = "products/{$productId}/" . $request->file('image')->getClientOriginalName();
            $imageContent = file_get_contents($request->file('image')->getRealPath());
            
            $uploadResult = $this->storageService->uploadFile($imagePath, $imageContent, 'public');
            
            if ($uploadResult['success']) {
                DB::table('products')->where('id', $productId)->update([
                    'image_url' => $uploadResult['url'],
                    'updated_at' => now(),
                ]);

                // Process image in background
                ProcessProductImageJob::dispatch($imagePath, $productId);
            }
        }

        Cache::forget('products_*');

        $product = DB::table('products')->where('id', $productId)->first();

        return response()->json([
            'success' => true,
            'data' => $product,
        ], 201);
    }

    /**
     * Update product
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'category' => 'sometimes|string',
            'stock_quantity' => 'sometimes|integer|min:0',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $exists = DB::table('products')->where('id', $id)->exists();

        if (!$exists) {
            return response()->json([
                'success' => false,
                'error' => 'Product not found',
            ], 404);
        }

        $validated['updated_at'] = now();
        DB::table('products')->where('id', $id)->update($validated);

        Cache::forget("product_{$id}");
        Cache::forget('products_*');

        $product = DB::table('products')->where('id', $id)->first();

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Delete product
     */
    public function destroy(int $id): JsonResponse
    {
        $product = DB::table('products')->where('id', $id)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'error' => 'Product not found',
            ], 404);
        }

        // Delete associated images from S3
        if ($product->image_url) {
            $this->storageService->delete("products/{$id}/");
        }

        DB::table('products')->where('id', $id)->delete();

        Cache::forget("product_{$id}");
        Cache::forget('products_*');

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}

