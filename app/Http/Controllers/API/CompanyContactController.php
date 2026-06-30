<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyContact;
use Illuminate\Http\Request;

class CompanyContactController extends Controller
{
    /**
     * Get all active company contacts.
     */
    public function index()
    {
        $contacts = CompanyContact::where('is_active', true)
            ->get()
            ->groupBy('type')
            ->map(function ($items, $type) {
                return $items->map(function ($item) use ($type) {
                    $data = $item->data;
                    if ($type === 'bank') {
                        return array_filter($data, function ($value) {
                            return !is_null($value) && $value !== '';
                        });
                    }
                    return $data['value'] ?? $data;
                });
            });

        return response()->json([
            'success' => true,
            'data' => $contacts
        ]);
    }
}
