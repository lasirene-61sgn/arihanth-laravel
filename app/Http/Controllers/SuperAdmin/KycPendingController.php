<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Craftman;
use Illuminate\Http\Request;

class KycPendingController extends Controller
{
    public function index(Request $request)
    {
        // Get buyers with incomplete KYC
        $pendingBuyers = $this->getPendingBuyers($request);
        
        // Get craftsmen with incomplete KYC
        $pendingCraftsmen = $this->getPendingCraftsmen($request);
        
        return view('super-admin.kyc-pending.index', compact('pendingBuyers', 'pendingCraftsmen'));
    }
    
    private function getPendingBuyers(Request $request)
    {
        $search = $request->input('searchText');
        
        return Buyer::all()->filter(function ($buyer) use ($search) {
            // Check if search term matches
            if ($search) {
                $searchLower = strtolower($search);
                $matchesSearch = str_contains(strtolower($buyer->business_name), $searchLower) ||
                                str_contains(strtolower($buyer->name), $searchLower) ||
                                str_contains(strtolower($buyer->email), $searchLower) ||
                                str_contains(strtolower("BP-" . str_pad($buyer->id, 4, '0', STR_PAD_LEFT)), $searchLower);
                
                if (!$matchesSearch) return false;
            }

            // Check if required KYC fields are missing
            $hasBasicInfo = !empty($buyer->business_name) && !empty($buyer->name);
            
            // Check if at least one Aadhar detail exists
            $hasAadhar = $buyer->aadharDetails && $buyer->aadharDetails->count() > 0;
            
            // Check if at least one PAN detail exists
            $hasPan = $buyer->panDetails && $buyer->panDetails->count() > 0;
            
            // Check if at least one Bank detail exists
            $hasBank = $buyer->bankDetails && $buyer->bankDetails->count() > 0;
            
            // Return true if any required KYC is missing
            return !($hasBasicInfo && $hasAadhar && $hasPan && $hasBank);
        })->values();
    }
    
    private function getPendingCraftsmen(Request $request)
    {
        $search = $request->input('searchText');

        return Craftman::all()->filter(function ($craftsman) use ($search) {
            // Check if search term matches
            if ($search) {
                $searchLower = strtolower($search);
                $matchesSearch = str_contains(strtolower($craftsman->business_name), $searchLower) ||
                                str_contains(strtolower($craftsman->name), $searchLower) ||
                                str_contains(strtolower($craftsman->email), $searchLower) ||
                                str_contains(strtolower("CP-" . str_pad($craftsman->id, 4, '0', STR_PAD_LEFT)), $searchLower);
                
                if (!$matchesSearch) return false;
            }

            // Check if required basic fields are missing
            $hasBasicInfo = !empty($craftsman->business_name) && !empty($craftsman->name) && !empty($craftsman->mobile);
            
            // Check if at least one Aadhar detail exists
            $hasAadhar = $craftsman->aadharDetails && $craftsman->aadharDetails->count() > 0;
            
            // Check if at least one PAN detail exists
            $hasPan = $craftsman->panDetails && $craftsman->panDetails->count() > 0;
            
            // Check if at least one Bank detail exists
            $hasBank = $craftsman->bankDetails && $craftsman->bankDetails->count() > 0;
            
            // Return true if any required KYC is missing
            return !($hasBasicInfo && $hasAadhar && $hasPan && $hasBank);
        })->values();
    }
}
