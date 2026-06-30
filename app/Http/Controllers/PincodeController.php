<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Log;

class PincodeController extends Controller
{
    public function fetch($pincode)
    {
        // Validate pincode format (6 digits)
        if (!preg_match('/^\d{6}$/', $pincode)) {
            return response()->json(['Status' => 'Error', 'Message' => 'Invalid Pincode Format'], 400);
        }

        try {
            // Fetch data from http://www.postalpincode.in (HTTP)
            // Adding User-Agent to mimic browser/circumvent possible blocking
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ])->get("http://www.postalpincode.in/api/pincode/{$pincode}");

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['Status']) && $data['Status'] === 'Success') {
                    // The API returns the exact structure ensuring Taluk is present
                    return $response->json();
                } else {
                     return response()->json(['Status' => 'Error', 'Message' => 'No records found'], 404);
                }
            } else {
                 Log::error("Pincode API Error (postalpincode.in): " . $response->status());
                 // Fallback to Zippopotam if primary fails? 
                 // Zippopotam doesn't have Taluk, so maybe better to just fail or try Zippopotam as backup for City/State at least.
                 // Let's keep it simple and try the primary first.
                 return response()->json(['Status' => 'Error', 'Message' => 'Unable to fetch data'], $response->status());
            }
        } catch (\Exception $e) {
            Log::error("Pincode Exception: " . $e->getMessage());
            return response()->json(['Status' => 'Error', 'Message' => 'Server Error'], 500);
        }
    }
}
