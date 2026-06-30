<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Favorite::select(
                'favorites.user_id', 
                'favorites.user_type', 
                DB::raw('count(*) as total_favorites'), 
                DB::raw('max(favorites.created_at) as last_added_at'),
                DB::raw('GROUP_CONCAT(products.design_code SEPARATOR ", ") as design_codes')
            )
            ->join('products', 'favorites.product_id', '=', 'products.id')
            // Left join both tables to access codes for searching
            ->leftJoin('buyers', function($join) {
                $join->on('favorites.user_id', '=', 'buyers.id')
                     ->where('favorites.user_type', '=', 'buyer');
            })
            ->leftJoin('craftmen', function($join) {
                $join->on('favorites.user_id', '=', 'craftmen.id')
                     ->where('favorites.user_type', '=', 'craftsman');
            })
            ->with(['user']);

        // Search logic
        if ($search) {
            $query->where(function($q) use ($search) {
                $q
                  ->orWhere('buyers.bp_code', 'LIKE', "%{$search}%")
                  ->orWhere('craftmen.name', 'LIKE', "%{$search}%")
                  ->orWhere('craftmen.craftman_code', 'LIKE', "%{$search}%")
                  ->orWhere('products.design_code', 'LIKE', "%{$search}%");
            });
        }

        $favorites = $query->groupBy('favorites.user_id', 'favorites.user_type')
            ->orderBy('last_added_at', 'desc')
            ->paginate(15)
            ->withQueryString(); // Keeps search parameter in pagination links

        return view('super-admin.favorites.index', compact('favorites', 'search'));
    }

    public function show($user_id, $user_type)
    {
        $favorites = Favorite::where('user_id', $user_id)
                    ->where('user_type', $user_type)
                    ->with('product.images')
                    ->latest()
                    ->get();

        $user = $user_type == 'buyer' ? \App\Models\Buyer::find($user_id) : \App\Models\Craftman::find($user_id);

        return view('super-admin.favorites.show', compact('favorites', 'user', 'user_type'));
    }

    public function destroy($id)
    {
        $favorite = Favorite::findOrFail($id);
        $favorite->delete();

        return back()->with('success', 'Favorite entry deleted successfully.');
    }
}
