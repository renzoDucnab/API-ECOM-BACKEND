<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**  
     * Get user's cart items
     */
    public function index()
    {
        $user = Auth::user();
        $cartItems = Cart::where('user_id', $user->id)->with('product')->get();

        return response()->json($cartItems);
    }

    /**  
     * Add product to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        $product = Product::findOrFail($request->product_id);

        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Not enough stock available'], 400);
        }

        $cartItem = Cart::where('user_id', $user->id)->where('product_id', $request->product_id)->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            $cartItem = Cart::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'price' => $product->price
            ]);
        }

        return response()->json(['messsage' => 'Product added to cart', 'cart_item' => $cartItem->load('product')], 201);
    }

    /**  
     * Update cart item quantity
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $user = Auth::user();

        $cartItem = Cart::where('user_id', $user->id)->where('id', $id)->firstOrFail();
        $product =  Product::findOrFail($cartItem->product_id);

        // check stock
        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Not enough stock available'], 400);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json(['messsage' => 'Cart updated', 'cart_item' => $cartItem->load('product')]);
    }

    /**  
     * Remove item from cart
     */
    public function remove($id)
    {
        $user = Auth::user();
        $cartItem = Cart::where('user_id', $user->id)->where('id', $id)->firstOrFail();

        $cartItem->delete();

        return response()->json(['messsage' => 'Item removed from cart']);
    }

    /**  
     * Clear user's cart
     */
    public function clear()
    {
        $user = Auth::user();

        Cart::where('user_id', $user->id)->delete();

        return response()->json(['messsage' => 'Cart cleared']);
    }
}
