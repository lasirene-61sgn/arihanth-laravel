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
        $craftsman = $this->currentCraftsman();
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
            'product_id'  => 'required|exists:products,id',
            'design_name' => 'nullable|string|max:255',
        ]);

        $craftsman = $this->currentCraftsman();
        $designName = filled($request->design_name) ? trim($request->design_name) : null;

        // Check if already favorited
        $favorite = Favorite::where('user_id', $craftsman->id)
            ->where('user_type', 'craftsman')
            ->where('product_id', $request->product_id)
            ->first();

        if ($favorite) {
            // Update existing custom design name if passed
            $favorite->update([
                'design_name' => $designName
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Favorite design name updated successfully!',
                'data'    => $favorite
            ]);
        }

        // Create new favorite with custom design name
        $favorite = Favorite::create([
            'user_id'     => $craftsman->id,
            'user_type'   => 'craftsman',
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
        $craftsman = $this->currentCraftsman();
        $favorite = Favorite::where('user_id', $craftsman->id)
            ->where('user_type', 'craftsman')
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