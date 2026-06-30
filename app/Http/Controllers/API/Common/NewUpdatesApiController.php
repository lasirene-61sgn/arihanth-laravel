<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\NewUpdates;
use Illuminate\Http\Request;

class NewUpdatesApiController extends Controller
{
    /**
     * Get all new updates (filtered by target_audience and check if seen).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $userTypeClass = get_class($user);
        // Determine role based on class name (e.g. App\Models\Buyer -> buyer)
        $role = 'all';
        if (str_contains(strtolower($userTypeClass), 'buyer')) {
            $role = 'buyer';
        } elseif (str_contains(strtolower($userTypeClass), 'craftsman')) {
            $role = 'craftsman';
        }

        $updates = NewUpdates::where(function($query) use ($role, $user) {
            $query->where('target_audience', 'all');
            if ($role === 'buyer') {
                $query->orWhere(function($q) use ($user) {
                    $q->where('target_audience', 'buyer')
                      ->where(function($subQ) use ($user) {
                          $subQ->whereJsonContains('target_buyers', 'all')
                               ->orWhereJsonContains('target_buyers', (string)$user->id);
                      });
                });
            } elseif ($role === 'craftsman') {
                $query->orWhere(function($q) use ($user) {
                    $q->where('target_audience', 'craftsman')
                      ->where(function($subQ) use ($user) {
                          $subQ->whereJsonContains('target_craftsmen', 'all')
                               ->orWhereJsonContains('target_craftsmen', (string)$user->id);
                      });
                });
            }
        })
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($update) use ($user, $userTypeClass) {
                if ($update->media_path) {
                    $update->media_url = asset('storage/' . $update->media_path);
                } else {
                    $update->media_url = null;
                }

                // Check if user has seen this update
                $isSeen = \Illuminate\Support\Facades\DB::table('new_update_views')
                    ->where('new_update_id', $update->id)
                    ->where('user_id', $user->id)
                    ->where('user_type', $userTypeClass)
                    ->exists();
                
                $update->is_seen = $isSeen;

                // If not seen, mark it as seen now for future requests
                if (!$isSeen) {
                    \Illuminate\Support\Facades\DB::table('new_update_views')->insert([
                        'new_update_id' => $update->id,
                        'user_id' => $user->id,
                        'user_type' => $userTypeClass,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                return $update;
            });

        return response()->json([
            'success' => true,
            'data' => $updates
        ]);
    }

    /**
     * Mark an update as seen by the current user.
     */
    public function markAsSeen(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $update = NewUpdates::find($id);
        if (!$update) {
            return response()->json(['success' => false, 'message' => 'Update not found'], 404);
        }

        $userTypeClass = get_class($user);

        \Illuminate\Support\Facades\DB::table('new_update_views')->updateOrInsert(
            [
                'new_update_id' => $update->id,
                'user_id' => $user->id,
                'user_type' => $userTypeClass,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Update marked as seen'
        ]);
    }
}
