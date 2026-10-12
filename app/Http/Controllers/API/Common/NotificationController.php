<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Store FCM token for the authenticated user.
     * Works for Users, Buyers, KeyUsers, ProcessOwners, and Craftsmen.
     */
    public function storeFcmToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'nullable|string',
            'voip_token' => 'nullable|string',
            'device_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $data = [];
        if ($request->filled('fcm_token')) {
            $data['fcm_token'] = $request->fcm_token;
        }

        $isIos = !$request->filled('device_type') || strtolower($request->device_type) === 'ios';
        if ($request->filled('voip_token') && $isIos) {
            $data['voip_token'] = $request->voip_token;
        }

        if (!empty($data)) {
            $user->update($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Device token(s) updated successfully',
            'fcm_token' => $user->fcm_token,
            'voip_token' => $user->voip_token
        ]);
    }
}
