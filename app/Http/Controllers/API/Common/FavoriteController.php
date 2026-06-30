<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Buyer;
use App\Models\Craftman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    /**
     * List favorites for the current user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userType = $this->getUserType($user);

        if (!$userType) {
            return response()->json(['success' => false, 'message' => 'Only buyers and craftsmen can have favorites'], 403);
        }

        $favorites = Favorite::where('user_id', $user->id)
            ->where('user_type', $userType)
            ->with(['product.category', 'product.images'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);
    }

    /**
     * Add a design to favorites.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = $request->user();
        $userType = $this->getUserType($user);

        if (!$userType) {
            return response()->json(['success' => false, 'message' => 'Only buyers and craftsmen can add favorites'], 403);
        }

        // Check if already favorited
        $favorite = Favorite::where('user_id', $user->id)
            ->where('user_type', $userType)
            ->where('product_id', $request->product_id)
            ->first();

        if ($favorite) {
            return response()->json(['success' => false, 'message' => 'Design is already in your favorites']);
        }

        $favorite = Favorite::create([
            'user_id' => $user->id,
            'user_type' => $userType,
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Design added to favorites',
            'data' => $favorite
        ]);
    }

    /**
     * Toggle a design in/out of favorites.
     * If already favourited → remove. If not → add.
     */
    public function toggle(Request $request, $productId)
    {
        $request->merge(['product_id' => $productId]);
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user     = $request->user();
        $userType = $this->getUserType($user);

        if (!$userType) {
            return response()->json(['success' => false, 'message' => 'Only buyers and craftsmen can favourite designs'], 403);
        }

        $existing = Favorite::where('user_id', $user->id)
            ->where('user_type', $userType)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'success'     => true,
                'is_favorite' => false,
                'message'     => 'Design removed from favourites',
            ]);
        }

        $favorite = Favorite::create([
            'user_id'    => $user->id,
            'user_type'  => $userType,
            'product_id' => $productId,
        ]);

        return response()->json([
            'success'     => true,
            'is_favorite' => true,
            'message'     => 'Design added to favourites',
            'data'        => $favorite,
        ]);
    }

    /**
     * Remove a design from favorites.
     * Accepts either the product_id or the favourite record id.
     */
    public function destroy(Request $request, $productId)
    {
        $user     = $request->user();
        $userType = $this->getUserType($user);

        if (!$userType) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Try to find by product_id first (normal case), then fall back to favourite record id
        $favorite = Favorite::where('user_id', $user->id)
            ->where('user_type', $userType)
            ->where(function ($q) use ($productId) {
                $q->where('product_id', $productId)
                  ->orWhere('id', $productId);
            })
            ->first();

        if (!$favorite) {
            return response()->json(['success' => false, 'message' => 'Favourite not found'], 404);
        }

        $favorite->delete();

        return response()->json([
            'success'     => true,
            'is_favorite' => false,
            'message'     => 'Design removed from favourites',
        ]);
    }

    /**
     * Admin view: List all favorites grouped by user.
     */
    public function adminIndex(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin' && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $search = $request->input('search');

        $query = Favorite::select(
                'favorites.user_id', 
                'favorites.user_type', 
                DB::raw('count(*) as total_favorites'), 
                DB::raw('max(favorites.created_at) as last_added_at'),
                DB::raw('GROUP_CONCAT(products.design_code SEPARATOR ", ") as design_codes')
            )
            ->join('products', 'favorites.product_id', '=', 'products.id')
            ->leftJoin('buyers', function($join) {
                $join->on('favorites.user_id', '=', 'buyers.id')
                     ->where('favorites.user_type', '=', 'buyer');
            })
            ->leftJoin('craftmen', function($join) {
                $join->on('favorites.user_id', '=', 'craftmen.id')
                     ->where('favorites.user_type', '=', 'craftsman');
            })
            ->with(['user']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->orWhere('buyers.bp_code', 'LIKE', "%{$search}%")
                  ->orWhere('craftmen.name', 'LIKE', "%{$search}%")
                  ->orWhere('craftmen.craftman_code', 'LIKE', "%{$search}%")
                  ->orWhere('products.design_code', 'LIKE', "%{$search}%");
            });
        }

        $favorites = $query->groupBy('favorites.user_id', 'favorites.user_type')
            ->orderBy('last_added_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);
    }

    /**
     * Helper to determine user type for morph.
     */
    private function getUserType($user)
    {
        if ($user instanceof Buyer) {
            return 'buyer';
        } elseif ($user instanceof Craftman || ($user->role ?? '') === 'craftsman') {
            return 'craftsman';
        }

        return null;
    }
}
