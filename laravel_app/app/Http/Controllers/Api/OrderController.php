<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController
{
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                // Create order
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'customer_name' => $request->customer_name,
                    'customer_email' => $request->customer_email,
                    'customer_phone' => $request->customer_phone,
                    'delivery_address' => $request->delivery_address,
                    'notes' => $request->notes,
                    'total_price' => 0, // Will be calculated
                    'status' => 'pending',
                ]);

                $totalPrice = 0;

                // Create order items
                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $quantity = $item['quantity'];
                    $subtotal = $product->price * $quantity;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price' => $product->price,
                        'quantity' => $quantity,
                        'subtotal' => $subtotal,
                    ]);

                    $totalPrice += $subtotal;
                }

                // Update order total
                $order->update(['total_price' => $totalPrice]);

                // Load relationships
                $order->load('items.product');

                return response()->json([
                    'message' => 'Order berhasil dibuat',
                    'data' => $order,
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Order $order): JsonResponse
    {
        $order->load('items.product');

        return response()->json([
            'data' => $order,
        ]);
    }

    public function byEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'order_number' => 'required|string',
        ]);

        $order = Order::where('customer_email', $request->email)
            ->where('order_number', $request->order_number)
            ->with('items.product')
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'data' => $order,
        ]);
    }
}
