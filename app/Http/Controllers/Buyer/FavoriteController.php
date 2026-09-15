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

        // Keep all favorites, including ones that have become locked
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
        $product = Product::findOrFail($request->product_id);

        if ($product->isDesignLocked($buyer)) {
            return response()->json([
                'success' => false,
                'message' => 'This design is currently locked and cannot be added to favorites.'
            ], 403);
        }

        if (!$product->design_code || $product->design_status !== 'Accepted') {
            return response()->json([
                'success' => false,
                'message' => 'Design not found in the approved catalogue.'
            ], 404);
        }

        $designName = filled($request->design_name) ? trim($request->design_name) : null;

        $favorite = Favorite::where('user_id', $buyer->id)
            ->where('user_type', 'buyer')
            ->where('product_id', $request->product_id)
            ->first();

        if ($favorite) {
            $favorite->update(['design_name' => $designName]);

            return response()->json([
                'success' => true,
                'message' => 'Favorite design name updated successfully!',
                'data'    => $favorite
            ]);
        }

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

    /**
     * Update custom design name via AJAX modal
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'design_name' => 'nullable|string|max:255',
        ]);

        $buyer = Auth::guard('buyer')->user();

        $favorite = Favorite::where('user_id', $buyer->id)
            ->where('user_type', 'buyer')
            ->where('id', $id)
            ->firstOrFail();

        $favorite->update([
            'design_name' => filled($request->design_name) ? trim($request->design_name) : null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Custom design name updated successfully!',
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
