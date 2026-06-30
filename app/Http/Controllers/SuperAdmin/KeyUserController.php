<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\KeyUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Exports\KeyUserExport;
use Maatwebsite\Excel\Facades\Excel;

class KeyUserController extends Controller
{
    public function index(Request $request)
    {
        // Check if the user clicked the Export button
        if ($request->get('export') === 'excel') {
            return Excel::download(new KeyUserExport($request), 'key-users-report.xlsx');
        }
        $query = KeyUser::query();


        // 1. Search Logic
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                    ->orWhere('email_id', 'like', "%$search%")
                    ->orWhere('user_code', 'like', "%$search%");
            });
        }

        // 2. Filter Logic (Status)
        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }

        // 3. Sort Logic
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 4. Export Logic (If you use Maatwebsite Excel)
        if ($request->get('export') === 'excel') {
            // Return your export class here
        }

        $keyUsers = $query->paginate(10)->withQueryString();

        return view('super-admin.key-user.index', compact('keyUsers'));
    }

    /**
     * Show the form for creating a new key user.
     */
    public function create()
    {
        $buyers = Buyer::all();
        return view('super-admin.key-user.create', compact('buyers'));
    }

    /**
     * Store a newly created key user in storage.
     */
    public function store(Request $request)
    {
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

        $data = $request->except(['profile_picture', 'aadhar_photo', 'password_confirmation']);
        $data['user_code'] = KeyUser::generateUserCode();
        $data['password'] = Hash::make($request->password);
        $data['password_plain'] = $request->password;
        // Add permissions if provided
        if ($request->has('permissions')) {
            $data['permissions'] = json_encode($request->permissions);
        }

        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $data['profile_picture'] = $profilePicturePath;
        }

        if ($request->hasFile('aadhar_photo')) {
            $aadharPhotoPath = $request->file('aadhar_photo')->store('aadhar_photos', 'public');
            $data['aadhar_photo'] = $aadharPhotoPath;
        }

        $keyUser = KeyUser::create($data);

        return redirect()->route('super-admin.key-user.index')
            ->with('success', 'Key user created successfully with user code: ' . $keyUser->user_code);
    }

    /**
     * Display the specified key user.
     */
    public function show(KeyUser $keyUser)
    {
        return view('super-admin.key-user.show', compact('keyUser'));
    }

    /**
     * Show the form for editing the specified key user.
     */
    public function edit(KeyUser $keyUser)
    {
        $buyers = Buyer::all();
        return view('super-admin.key-user.edit', compact('keyUser', 'buyers'));
    }

    /**
     * Update the specified key user in storage.
     */
    public function update(Request $request, KeyUser $keyUser)
    {
        $request->validate([
            'bp_code' => 'required|string|exists:buyers,bp_code',
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
        ]);

        $data = $request->except(['profile_picture', 'aadhar_photo', 'password', 'password_confirmation']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['password_plain'] = $request->password;
        }

        // Add permissions if provided
        if ($request->has('permissions')) {
            $data['permissions'] = json_encode($request->permissions);
        }

        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $data['profile_picture'] = $profilePicturePath;
        }

        if ($request->hasFile('aadhar_photo')) {
            $aadharPhotoPath = $request->file('aadhar_photo')->store('aadhar_photos', 'public');
            $data['aadhar_photo'] = $aadharPhotoPath;
        }

        $keyUser->update($data);

        return redirect()->route('super-admin.key-user.index')
            ->with('success', 'Key user updated successfully');
    }

    /**
     * Remove the specified key user from storage.
     */
    public function destroy(KeyUser $keyUser)
    {
        $keyUser->delete();

        return redirect()->route('super-admin.key-user.index')
            ->with('success', 'Key user deleted successfully');
    }

    public function printSelected(Request $request)
    {
        $ids = $request->input('selected_key_users', []);
        $keyUsers = KeyUser::whereIn('id', $ids)->get();
        return view('super-admin.key-user.print-selected', compact('keyUsers'));
    }
}
