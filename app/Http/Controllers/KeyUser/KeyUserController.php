<?php

namespace App\Http\Controllers\KeyUser;

use App\Http\Controllers\Controller;
use App\Models\KeyUser;
use App\Models\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KeyUserController extends Controller
{
    /**
     * Display a listing of the key users.
     */
    public function index()
    {
        // Only buyers with key_user permission can manage other key users
        $user = Auth::guard('key_user')->user();
        
        // Check if it's an actual key user (has user_code)
        $isActualKeyUser = $user && $user->user_code ?? false;
        
        // Check if it's a buyer with key_user permission
        $hasKeyUserPermission = false;
        if ($user && !$isActualKeyUser) {
            // This is a buyer logged in via BP code
            $buyer = $user->buyer;
            if ($buyer) {
                $hasKeyUserPermission = $buyer->hasPermission('key_user');
            }
        }
        
        // Only allow access if it's a buyer with key_user permission
        if (!Auth::guard('key_user')->check() || $isActualKeyUser || !$hasKeyUserPermission) {
            return redirect()->route('key-user.dashboard')->with('error', 'Access denied.');
        }
        
        $keyUsers = KeyUser::with('buyer')->latest()->paginate(10);
        return view('key-user.key-user.index', compact('keyUsers'));
    }

    /**
     * Show the form for creating a new key user.
     */
    public function create()
    {
        // Only buyers with key_user permission can create other key users
        $user = Auth::guard('key_user')->user();
        
        // Check if it's an actual key user (has user_code)
        $isActualKeyUser = $user && $user->user_code ?? false;
        
        // Check if it's a buyer with key_user permission
        $hasKeyUserPermission = false;
        if ($user && !$isActualKeyUser) {
            // This is a buyer logged in via BP code
            $buyer = $user->buyer;
            if ($buyer) {
                $hasKeyUserPermission = $buyer->hasPermission('key_user');
            }
        }
        
        // Only allow access if it's a buyer with key_user permission
        if (!Auth::guard('key_user')->check() || $isActualKeyUser || !$hasKeyUserPermission) {
            return redirect()->route('key-user.dashboard')->with('error', 'Access denied.');
        }
        
        $buyers = Buyer::all();
        return view('key-user.key-user.create', compact('buyers'));
    }

    /**
     * Store a newly created key user in storage.
     */
    public function store(Request $request)
    {
        // Only buyers with key_user permission can create other key users
        $user = Auth::guard('key_user')->user();
        
        // Check if it's an actual key user (has user_code)
        $isActualKeyUser = $user && $user->user_code ?? false;
        
        // Check if it's a buyer with key_user permission
        $hasKeyUserPermission = false;
        if ($user && !$isActualKeyUser) {
            // This is a buyer logged in via BP code
            $buyer = $user->buyer;
            if ($buyer) {
                $hasKeyUserPermission = $buyer->hasPermission('key_user');
            }
        }
        
        // Only allow access if it's a buyer with key_user permission
        if (!Auth::guard('key_user')->check() || $isActualKeyUser || !$hasKeyUserPermission) {
            return redirect()->route('key-user.dashboard')->with('error', 'Access denied.');
        }
        
        $request->validate([
            'bp_code' => 'required|string|exists:buyers,bp_code',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'full_name' => 'required|string|max:255',
            'email_id' => 'required|email|unique:key_users,email_id',
            'mobile_no' => 'required|string|max:15',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|boolean',
            'dob' => 'nullable|date',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'aadhar_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aadhar_number' => 'nullable|string|max:20',
        ]);

        // Handle file uploads
        $profilePicturePath = null;
        $aadharPhotoPath = null;

        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $request->file('profile_picture')->store('key-users/profile-pictures', 'public');
        }

        if ($request->hasFile('aadhar_photo')) {
            $aadharPhotoPath = $request->file('aadhar_photo')->store('key-users/aadhar-photos', 'public');
        }

        // Generate user code
        $lastKeyUser = KeyUser::orderBy('id', 'desc')->first();
        $userCode = $lastKeyUser ? 'KU' . str_pad((intval(substr($lastKeyUser->user_code, 2)) + 1), 4, '0', STR_PAD_LEFT) : 'KU0001';

        $data = [
            'user_code' => $userCode,
            'bp_code' => $request->bp_code,
            'profile_picture' => $profilePicturePath,
            'full_name' => $request->full_name,
            'email_id' => $request->email_id,
            'mobile_no' => $request->mobile_no,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'dob' => $request->dob,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'pincode' => $request->pincode,
            'aadhar_photo' => $aadharPhotoPath,
            'aadhar_number' => $request->aadhar_number,
            'created_by' => Auth::guard('key_user')->id(),
        ];

        $keyUser = KeyUser::create($data);

        return redirect()->route('key-user.key-user.index')
            ->with('success', 'Key user created successfully with user code: ' . $keyUser->user_code);
    }

    /**
     * Display the specified key user.
     */
    public function show(KeyUser $keyUser)
    {
        // Only buyers with key_user permission can view other key users
        $user = Auth::guard('key_user')->user();
        
        // Check if it's an actual key user (has user_code)
        $isActualKeyUser = $user && $user->user_code ?? false;
        
        // Check if it's a buyer with key_user permission
        $hasKeyUserPermission = false;
        if ($user && !$isActualKeyUser) {
            // This is a buyer logged in via BP code
            $buyer = $user->buyer;
            if ($buyer) {
                $hasKeyUserPermission = $buyer->hasPermission('key_user');
            }
        }
        
        // Only allow access if it's a buyer with key_user permission
        if (!Auth::guard('key_user')->check() || $isActualKeyUser || !$hasKeyUserPermission) {
            return redirect()->route('key-user.dashboard')->with('error', 'Access denied.');
        }
        
        return view('key-user.key-user.show', compact('keyUser'));
    }

    /**
     * Show the form for editing the specified key user.
     */
    public function edit(KeyUser $keyUser)
    {
        // Only buyers with key_user permission can edit other key users
        $user = Auth::guard('key_user')->user();
        
        // Check if it's an actual key user (has user_code)
        $isActualKeyUser = $user && $user->user_code ?? false;
        
        // Check if it's a buyer with key_user permission
        $hasKeyUserPermission = false;
        if ($user && !$isActualKeyUser) {
            // This is a buyer logged in via BP code
            $buyer = $user->buyer;
            if ($buyer) {
                $hasKeyUserPermission = $buyer->hasPermission('key_user');
            }
        }
        
        // Only allow access if it's a buyer with key_user permission
        if (!Auth::guard('key_user')->check() || $isActualKeyUser || !$hasKeyUserPermission) {
            return redirect()->route('key-user.dashboard')->with('error', 'Access denied.');
        }
        
        $buyers = Buyer::all();
        return view('key-user.key-user.edit', compact('keyUser', 'buyers'));
    }

    /**
     * Update the specified key user in storage.
     */
    public function update(Request $request, KeyUser $keyUser)
    {
        // Only buyers with key_user permission can update other key users
        $user = Auth::guard('key_user')->user();
        
        // Check if it's an actual key user (has user_code)
        $isActualKeyUser = $user && $user->user_code ?? false;
        
        // Check if it's a buyer with key_user permission
        $hasKeyUserPermission = false;
        if ($user && !$isActualKeyUser) {
            // This is a buyer logged in via BP code
            $buyer = $user->buyer;
            if ($buyer) {
                $hasKeyUserPermission = $buyer->hasPermission('key_user');
            }
        }
        
        // Only allow access if it's a buyer with key_user permission
        if (!Auth::guard('key_user')->check() || $isActualKeyUser || !$hasKeyUserPermission) {
            return redirect()->route('key-user.dashboard')->with('error', 'Access denied.');
        }
        
        $request->validate([
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'full_name' => 'required|string|max:255',
            'email_id' => [
                'required',
                'email',
                Rule::unique('key_users')->ignore($keyUser->id),
            ],
            'mobile_no' => 'required|string|max:15',
            'status' => 'required|boolean',
            'dob' => 'nullable|date',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'aadhar_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aadhar_number' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'full_name' => $request->full_name,
            'email_id' => $request->email_id,
            'mobile_no' => $request->mobile_no,
            'status' => $request->status,
            'dob' => $request->dob,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'pincode' => $request->pincode,
            'aadhar_number' => $request->aadhar_number,
        ];

        // Handle file uploads
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($keyUser->profile_picture) {
                Storage::disk('public')->delete($keyUser->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('key-users/profile-pictures', 'public');
        }

        if ($request->hasFile('aadhar_photo')) {
            // Delete old aadhar photo if exists
            if ($keyUser->aadhar_photo) {
                Storage::disk('public')->delete($keyUser->aadhar_photo);
            }
            $data['aadhar_photo'] = $request->file('aadhar_photo')->store('key-users/aadhar-photos', 'public');
        }

        // Update password if provided
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $keyUser->update($data);

        return redirect()->route('key-user.key-user.index')
            ->with('success', 'Key user updated successfully!');
    }

    /**
     * Remove the specified key user from storage.
     */
    public function destroy(KeyUser $keyUser)
    {
        // Only buyers with key_user permission can delete other key users
        $user = Auth::guard('key_user')->user();
        
        // Check if it's an actual key user (has user_code)
        $isActualKeyUser = $user && $user->user_code ?? false;
        
        // Check if it's a buyer with key_user permission
        $hasKeyUserPermission = false;
        if ($user && !$isActualKeyUser) {
            // This is a buyer logged in via BP code
            $buyer = $user->buyer;
            if ($buyer) {
                $hasKeyUserPermission = $buyer->hasPermission('key_user');
            }
        }
        
        // Only allow access if it's a buyer with key_user permission
        if (!Auth::guard('key_user')->check() || $isActualKeyUser || !$hasKeyUserPermission) {
            return redirect()->route('key-user.dashboard')->with('error', 'Access denied.');
        }
        
        // Delete associated files
        if ($keyUser->profile_picture) {
            Storage::disk('public')->delete($keyUser->profile_picture);
        }
        
        if ($keyUser->aadhar_photo) {
            Storage::disk('public')->delete($keyUser->aadhar_photo);
        }
        
        $keyUser->delete();

        return redirect()->route('key-user.key-user.index')
            ->with('success', 'Key user deleted successfully!');
    }
}