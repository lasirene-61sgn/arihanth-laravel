<?php

namespace App\Http\Controllers\KeyUser;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
{
    $keyUserBpCode = auth()->guard('key_user')->user()->bp_code;
    $query = User::with(['createdBy', 'buyer'])
                 ->where('bp_code', $keyUserBpCode);

    // 1. Quick Search (Global)
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('user_code', 'like', "%{$search}%");
        });
    }

    // 2. Advanced Filters
    if ($request->filled('filter_user_code')) {
        $query->where('user_code', 'like', '%' . $request->filter_user_code . '%');
    }
    if ($request->filled('filter_bp_code')) {
        $query->where('bp_code', 'like', '%' . $request->filter_bp_code . '%');
    }
    if ($request->filled('filter_mobile')) {
        $query->where('mobile_no', 'like', '%' . $request->filter_mobile . '%');
    }
    if ($request->filled('filter_status')) {
        $query->where('status', $request->filter_status);
    }

    // 3. Sorting
    $sort = $request->get('sort', 'created_at');
    $direction = $request->get('direction', 'desc');
    $query->orderBy($sort, $direction);

    $users = $query->paginate(10)->appends($request->query());

    return view('key-user.user.index', compact('users'));
}



    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $keyUser = auth()->guard('key_user')->user();
        $buyer = $keyUser->buyer;
        return view('key-user.user.create', compact('buyer'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'email_id' => 'required|email|unique:users,email_id',
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
        $data['user_code'] = User::generateUserCode();
        $data['password'] = Hash::make($request->password);
        $data['created_by'] = auth()->guard('key_user')->id(); // Set the creator
        $data['bp_code'] = auth()->guard('key_user')->user()->bp_code; // Automatically link BP code

        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $data['profile_picture'] = $profilePicturePath;
        }

        if ($request->hasFile('aadhar_photo')) {
            $aadharPhotoPath = $request->file('aadhar_photo')->store('aadhar_photos', 'public');
            $data['aadhar_photo'] = $aadharPhotoPath;
        }

        $user = User::create($data);

        return redirect()->route('key-user.user.index')
            ->with('success', 'User created successfully with user code: ' . $user->user_code);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return view('key-user.user.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $keyUser = auth()->guard('key_user')->user();
        if ($user->bp_code !== $keyUser->bp_code) {
            abort(403, 'Unauthorized action.');
        }
        $buyer = $keyUser->buyer;
        return view('key-user.user.edit', compact('user', 'buyer'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $keyUser = auth()->guard('key_user')->user();
        if ($user->bp_code !== $keyUser->bp_code) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'email_id' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
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

        $data = $request->except(['profile_picture', 'aadhar_photo', 'password', 'bp_code']);

        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $data['profile_picture'] = $profilePicturePath;
        }

        if ($request->hasFile('aadhar_photo')) {
            $aadharPhotoPath = $request->file('aadhar_photo')->store('aadhar_photos', 'public');
            $data['aadhar_photo'] = $aadharPhotoPath;
        }

        $user->update($data);

        return redirect()->route('key-user.user.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('key-user.user.index')
            ->with('success', 'User deleted successfully');
    }
    public function export(Request $request) 
{
    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\KeyUserUserExport($request), 'User-List-Report.xlsx');
}
}