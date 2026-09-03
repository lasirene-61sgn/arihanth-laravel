<?php

namespace App\Http\Controllers\Admin;

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
                DB::raw('GROUP_CONCAT(DISTINCT products.design_code SEPARATOR ", ") as design_codes'),
                DB::raw('GROUP_CONCAT(DISTINCT IF(favorites.design_name IS NOT NULL AND favorites.design_name != "", CONCAT(favorites.design_name, " (", products.design_code, ")"), products.design_code) SEPARATOR ", ") as design_names')
            )
            ->join('products', 'favorites.product_id', '=', 'products.id')
            // Join user tables based on user_type to enable searching
            ->leftJoin('buyers', function($join) {
                $join->on('favorites.user_id', '=', 'buyers.id')
                     ->where('favorites.user_type', '=', 'buyer');
            })
            ->leftJoin('craftmen', function($join) {
                $join->on('favorites.user_id', '=', 'craftmen.id')
                     ->where('favorites.user_type', '=', 'craftman');
            })
            ->with(['user']);

        // Search Logic
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
            ->withQueryString(); // Maintains search parameter in pagination links

        return view('admin.favorites.index', compact('favorites', 'search'));
    }

    public function create()
    {
        $buyers = \App\Models\Buyer::all();
        $craftsmen = \App\Models\Craftman::all();
        $products = \App\Models\Product::with('images')->whereNotNull('design_code')->get();
        return view('admin.favorites.create', compact('buyers', 'craftsmen', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'buyer_ids' => 'array',
            'craftsman_ids' => 'array',
            'product_ids' => 'required|array',
            'design_names' => 'array',
        ]);

        $productIds = $request->input('product_ids', []);
        $designNames = $request->input('design_names', []);
        $buyerIds = $request->input('buyer_ids', []);
        $craftsmanIds = $request->input('craftsman_ids', []);

        if (empty($buyerIds) && empty($craftsmanIds)) {
            return back()->with('error', 'Please select at least one buyer or craftsman.');
        }

        // Add to Buyers
        foreach ($buyerIds as $buyerId) {
            foreach ($productIds as $productId) {
                $designName = $designNames[$productId] ?? null;
                Favorite::updateOrCreate(
                    ['user_id' => $buyerId, 'user_type' => 'buyer', 'product_id' => $productId],
                    ['design_name' => $designName]
                );
            }
        }

        // Add to Craftsmen
        foreach ($craftsmanIds as $craftsmanId) {
            foreach ($productIds as $productId) {
                $designName = $designNames[$productId] ?? null;
                Favorite::updateOrCreate(
                    ['user_id' => $craftsmanId, 'user_type' => 'craftsman', 'product_id' => $productId],
                    ['design_name' => $designName]
                );
            }
        }

        return redirect()->route('favorites.index')->with('success', 'Favorites successfully assigned.');
    }

    public function show($user_id, $user_type)
    {
        $favorites = Favorite::where('user_id', $user_id)
                    ->where('user_type', $user_type)
                    ->with('product.images')
                    ->latest()
                    ->get();

        $user = $user_type == 'buyer' ? \App\Models\Buyer::find($user_id) : \App\Models\Craftman::find($user_id);

        return view('admin.favorites.show', compact('favorites', 'user', 'user_type'));
    }

    public function edit($user_id, $user_type)
    {
        $favorites = Favorite::where('user_id', $user_id)
            ->where('user_type', $user_type)
            ->with('product.images')
            ->get();
            
        $user = $user_type == 'buyer' ? \App\Models\Buyer::find($user_id) : \App\Models\Craftman::find($user_id);
        $products = \App\Models\Product::with('images')->whereNotNull('design_code')->get();

        return view('admin.favorites.edit', compact('favorites', 'user', 'user_type', 'products'));
    }

    public function update(Request $request, $user_id, $user_type)
    {
        $request->validate([
            'product_ids' => 'array',
            'design_names' => 'array',
        ]);

        $productIds = $request->input('product_ids', []);
        $designNames = $request->input('design_names', []);

        if(empty($productIds)) {
            Favorite::where('user_id', $user_id)
                ->where('user_type', $user_type)
                ->delete();
        } else {
            // Delete favorites that are not in the selected list
            Favorite::where('user_id', $user_id)
                ->where('user_type', $user_type)
                ->whereNotIn('product_id', $productIds)
                ->delete();

            foreach ($productIds as $productId) {
                $designName = $designNames[$productId] ?? null;
                Favorite::updateOrCreate(
                    ['user_id' => $user_id, 'user_type' => $user_type, 'product_id' => $productId],
                    ['design_name' => $designName]
                );
            }
        }

        return redirect()->route('favorites.index')->with('success', 'Favorites successfully updated.');
    }

    public function destroy($id)
    {
        $favorite = Favorite::findOrFail($id);
        $favorite->delete();

        return back()->with('success', 'Favorite entry deleted successfully.');
    }
}