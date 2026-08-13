<?php

namespace App\Http\Controllers\Craftsman;

use App\Http\Controllers\Controller;
use App\Models\CraftsmanStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    public function index()
    {
        $craftsman = $this->currentCraftsman();
        $staffs = CraftsmanStaff::where('craftsman_id', $craftsman->id)->get();
        return view('craftsman.staff.index', compact('staffs'));
    }

    public function create()
    {
        return view('craftsman.staff.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'aadhar_number' => 'nullable|string|max:20',
            'image' => 'nullable|image|max:2048',
            'aadhar_image' => 'nullable|image|max:2048',
            'permissions' => 'array',
        ]);

        $craftsman = $this->currentCraftsman();

        $staff = new CraftsmanStaff();
        $staff->craftsman_id = $craftsman->id;
        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->mobile = $request->mobile;
        $staff->password = Hash::make($request->password);
        $staff->password_plain = $request->password;
        $staff->aadhar_number = $request->aadhar_number;
        $staff->is_active = $request->has('is_active');
        $staff->permissions = $request->permissions ?? [];
        
        // Generate temporary code to save first
        $staff->staff_code = 'TEMP';
        $staff->save();

        // Update code with ID
        $staff->staff_code = 'CS' . str_pad($staff->id, 4, '0', STR_PAD_LEFT);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('staff_images', 'public');
            $staff->image = $path;
        }

        if ($request->hasFile('aadhar_image')) {
            $path = $request->file('aadhar_image')->store('staff_aadhar', 'public');
            $staff->aadhar_image = $path;
        }

        $staff->save();

        return redirect()->route('craftsman.staff.index')->with('success', 'Staff created successfully. Code: ' . $staff->staff_code);
    }

    public function edit($id)
    {
        $craftsman = $this->currentCraftsman();
        $staff = CraftsmanStaff::where('craftsman_id', $craftsman->id)->findOrFail($id);
        return view('craftsman.staff.edit', compact('staff'));
    }

    public function update(Request $request, $id)
    {
        $craftsman = $this->currentCraftsman();
        $staff = CraftsmanStaff::where('craftsman_id', $craftsman->id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'aadhar_number' => 'nullable|string|max:20',
            'image' => 'nullable|image|max:2048',
            'aadhar_image' => 'nullable|image|max:2048',
            'permissions' => 'array',
        ]);

        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->mobile = $request->mobile;
        if ($request->filled('password')) {
            $staff->password = Hash::make($request->password);
            $staff->password_plain = $request->password;
        }
        $staff->aadhar_number = $request->aadhar_number;
        
        // Restriction: Craftsman cannot deactivate an already active staff.
        // They can activate an inactive staff.
        if ($staff->is_active) {
            // Force it to remain active
            $staff->is_active = true;
        } else {
            $staff->is_active = $request->has('is_active');
        }

        $staff->permissions = $request->permissions ?? [];

        if ($request->hasFile('image')) {
            if ($staff->image) Storage::disk('public')->delete($staff->image);
            $path = $request->file('image')->store('staff_images', 'public');
            $staff->image = $path;
        }

        if ($request->hasFile('aadhar_image')) {
            if ($staff->aadhar_image) Storage::disk('public')->delete($staff->aadhar_image);
            $path = $request->file('aadhar_image')->store('staff_aadhar', 'public');
            $staff->aadhar_image = $path;
        }

        $staff->save();

        return redirect()->route('craftsman.staff.index')->with('success', 'Staff updated successfully.');
    }
}
