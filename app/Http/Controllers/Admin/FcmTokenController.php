<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FcmTokenController extends Controller
{
    /**
     * Save FCM token for the currently authenticated Admin (auth:admin guard).
     * Called from the admin panel JS after Firebase Messaging getToken() succeeds.
     */
    public function saveAdminToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $admin->update(['fcm_token' => $request->token]);

        Log::info('Admin FCM token saved', ['admin_id' => $admin->id, 'guard' => 'admin']);

        return response()->json(['success' => true]);
    }

    /**
     * Save FCM token for the currently authenticated Super Admin (auth:super_admin guard).
     */
    public function saveSuperAdminToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $admin = Auth::guard('super_admin')->user();

        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $admin->update(['fcm_token' => $request->token]);

        Log::info('Super Admin FCM token saved', ['admin_id' => $admin->id, 'guard' => 'super_admin']);

        return response()->json(['success' => true]);
    }
}
