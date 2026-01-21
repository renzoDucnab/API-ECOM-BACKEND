<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**  
     * Get user's orders
     */
    public function index()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->with('orderItems.product')->orderBy('created_at', 'desc')->get();

        return response()->json($orders);
    }

    /**  
     * Create new order from cart
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $user = Auth::user();
            $cartItems = Cart::where('user_id', $user->id)->with('product')->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['message' => 'Cart is empty'], 400);
            }

            $totalAmount = 0;
            $orderItems = [];

            // validate stock
            foreach ($cartItems as $cartItem) {
                if ($cartItem->product->stock < $cartItem->quantity) {
                    throw new \Exception("Not enougn stock for product: {$cartItem->product->name}");
                }

                $totalAmount += $cartItem->price * $cartItem->quantity;

                $orderItems[] = [
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                ];
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => 'pending'
            ]);

            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // update stock
                $product = Product::find($item['product_id']);
                $product->stock -= $item['quantity'];
                $product->save();
            }

            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            return response()->json(['messsage' => 'Order place succesfully', 'order' => $order->load('orderItems.product')], 201);
        } catch (\Exception $e) {

            DB::rollback();

            return response()->json(['messsage' => 'Failed to place order', 'error' => $e->getMessage()], 500);
        }
    }

    /**  
     * Get order details
     */
    public function show($id)
    {
        $user = Auth::user();

        $order = Order::where('user_id', $user->id)->where('id', $id)->with('orderItems.product')->firstOrFail();

        return response()->json($order);
    }
}
