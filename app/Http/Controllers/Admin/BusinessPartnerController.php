<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Craftman;
use Illuminate\Http\Request;

class BusinessPartnerController extends Controller
{
    /**
     * Display business partner overview page for admins
     */
    public function index(Request $request)
    {
        // Get buyers data
        $buyersQuery = Buyer::query();
        
        // Apply search filter for buyers
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $buyersQuery->where(function($q) use ($searchTerm) {
                $q->where('bp_code', 'like', "%{$searchTerm}%")
                  ->orWhere('business_name', 'like', "%{$searchTerm}%")
                  ->orWhere('name', 'like', "%{$searchTerm}%");
            });
        }
        
        $buyers = $buyersQuery->get();

        // Get craftmen data
        $craftmenQuery = Craftman::query();
        
        // Apply search filter for craftmen
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $craftmenQuery->where(function($q) use ($searchTerm) {
                $q->where('craftman_code', 'like', "%{$searchTerm}%")
                  ->orWhere('business_name', 'like', "%{$searchTerm}%")
                  ->orWhere('name', 'like', "%{$searchTerm}%");
            });
        }
        
        $craftmen = $craftmenQuery->get();

        // Return to the business partner index view
        return view('admin.business-partner.index', compact('buyers', 'craftmen'));
    }
}