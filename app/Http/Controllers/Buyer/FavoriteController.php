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
            'product_id'  => 'required|exists:products,id',
            'design_name' => 'nullable|string|max:255',
        ]);

        $buyer = Auth::guard('buyer')->user();
        
        $designName = filled($request->design_name) 
            ? trim($request->design_name) 
            : null;

        // Check if already in favorites
        $favorite = Favorite::where('user_id', $buyer->id)
            ->where('user_type', 'buyer')
            ->where('product_id', $request->product_id)
            ->first();

        if ($favorite) {
            // Update existing custom name
            $favorite->update([
                'design_name' => $designName
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Favorite design name updated successfully!',
                'data'    => $favorite
            ]);
        }

        // Add new favorite
        $favorite = Favorite::create([
            'user_id'     => $buyer->id,
            'user_type'   => 'buyer',
            'product_id'  => $request->product_id,
            'design_name' => $designName,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Design added to favorites successfully!',
            'data'    => $favorite
        ]);
    }

    public function destroy($id)
    {
        $buyer = Auth::guard('buyer')->user();
        
        $favorite = Favorite::where('user_id', $buyer->id)
            ->where('user_type', 'buyer')
            ->where('id', $id)
            ->firstOrFail();

        $favorite->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Design removed from favorites.'
            ]);
        }

        return back()->with('success', 'Design removed from favorites.');
    }
}