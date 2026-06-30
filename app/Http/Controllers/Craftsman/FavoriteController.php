<?php

namespace App\Http\Controllers\Craftsman;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $craftsman = Auth::guard('craftsman')->user();
        $favorites = Favorite::where('user_id', $craftsman->id)
            ->where('user_type', 'craftsman')
            ->with(['product.category', 'product.images'])
            ->latest()
            ->get();

        return view('craftsman.favorites.index', compact('favorites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $craftsman = Auth::guard('craftsman')->user();

        // Check if already favorited
        $exists = Favorite::where('user_id', $craftsman->id)
            ->where('user_type', 'craftsman')
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Design is already in your favorites.']);
        }

        Favorite::create([
            'user_id' => $craftsman->id,
            'user_type' => 'craftsman',
            'product_id' => $request->product_id,
        ]);

        return response()->json(['success' => true, 'message' => 'Design added to favorites successfully!']);
    }

    public function destroy($id)
    {
        $craftsman = Auth::guard('craftsman')->user();
        $favorite = Favorite::where('user_id', $craftsman->id)
            ->where('user_type', 'craftsman')
            ->where('id', $id)
            ->firstOrFail();

        $favorite->delete();

        return back()->with('success', 'Design removed from favorites.');
    }
}
