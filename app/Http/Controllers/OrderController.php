<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * List all orders
     */
    public function index(Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        $userId = $request->input('user_id');
        $status = $request->input('status');

        $query = DB::table('orders');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Get single order
     */
    public function show(int $id): JsonResponse
    {
        $order = DB::table('orders')->where('id', $id)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => 'Order not found',
            ], 404);
        }

        $orderItems = DB::table('order_items')->where('order_id', $id)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $order,
                'items' => $orderItems,
            ],
        ]);
    }

    /**
     * Create new order
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }

            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $validated['user_id'],
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'shipping_address' => $validated['shipping_address'],
                'payment_method' => $validated['payment_method'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($validated['items'] as $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            $order = DB::table('orders')->where('id', $orderId)->first();
            $orderData = array_merge((array) $order, ['items' => $validated['items']]);

            // Dispatch event to process order
            event(new OrderCreated($orderData));

            return response()->json([
                'success' => true,
                'data' => $order,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update order status
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $exists = DB::table('orders')->where('id', $id)->exists();

        if (!$exists) {
            return response()->json([
                'success' => false,
                'error' => 'Order not found',
            ], 404);
        }

        DB::table('orders')->where('id', $id)->update([
            'status' => $validated['status'],
            'updated_at' => now(),
        ]);

        $order = DB::table('orders')->where('id', $id)->first();

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }
}

