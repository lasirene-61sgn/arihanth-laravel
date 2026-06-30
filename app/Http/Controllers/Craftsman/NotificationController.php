<?php

namespace App\Http\Controllers\Craftsman;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Store the FCM token for the authenticated craftsman.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $user = Auth::guard('craftsman')->user();
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $user->fcm_token = $request->token;
            $user->save();

            return response()->json(['success' => true, 'message' => 'Token saved successfully.']);
        } catch (\Exception $e) {
            Log::error('Error saving FCM token: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error saving token'], 500);
        }
    }
}
