<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $buyer = Auth::guard('buyer')->user();
        $favorites = Favorite::where('user_id', $buyer->id)
            ->where('user_type', 'buyer')
            ->with(['product.category', 'product.images'])
            ->latest()
            ->get();

        return view('buyer.favorites.index', compact('favorites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $buyer = Auth::guard('buyer')->user();

        // Check if already favorited
        $exists = Favorite::where('user_id', $buyer->id)
            ->where('user_type', 'buyer')
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Design is already in your favorites.']);
        }

        Favorite::create([
            'user_id' => $buyer->id,
            'user_type' => 'buyer',
            'product_id' => $request->product_id,
        ]);

        return response()->json(['success' => true, 'message' => 'Design added to favorites successfully!']);
    }

    public function destroy($id)
    {
        $buyer = Auth::guard('buyer')->user();
        $favorite = Favorite::where('user_id', $buyer->id)
            ->where('user_type', 'buyer')
            ->where('id', $id)
            ->firstOrFail();

        $favorite->delete();

        return back()->with('success', 'Design removed from favorites.');
    }
}
