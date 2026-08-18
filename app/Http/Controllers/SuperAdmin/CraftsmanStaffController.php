<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\CraftsmanStaff;
use App\Models\Craftman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CraftsmanStaffController extends Controller
{
    /**
     * Display a listing of the craftsman staff.
     */
    public function index(Request $request)
    {
        $query = CraftsmanStaff::with('craftsman');

        // Specific Staff Filters
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('staff_code')) {
            $query->where('staff_code', 'like', '%' . $request->staff_code . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('mobile')) {
            $query->where('mobile', 'like', '%' . $request->mobile . '%');
        }
        if ($request->filled('craftsman_id')) {
            $query->where('craftsman_id', $request->craftsman_id);
        }

        // Craftsman Relation Filters (Craftman Code & Business Name)
        if ($request->filled('craftman_code')) {
            $query->whereHas('craftsman', function ($q) use ($request) {
                $q->where('craftman_code', 'like', '%' . $request->craftman_code . '%');
            });
        }
        if ($request->filled('business_name')) {
            $query->whereHas('craftsman', function ($q) use ($request) {
                $q->where('business_name', 'like', '%' . $request->business_name . '%');
            });
        }

        // Status / Frozen filter
        if ($request->filled('status')) {
            $status = $request->status; // 'active' or 'frozen'
            if ($status == 'active') {
                $query->where('is_active', true);
            } elseif ($status == 'frozen') {
                $query->where('is_active', false);
            }
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        $staffs = $query->orderBy($sort, $direction)
                      ->paginate(10) 
                      ->appends($request->all());

        $craftsmen = Craftman::all();

        return view('super-admin.business-partner.craftsman-staff.index', compact('staffs', 'craftsmen'));
    }

    /**
     * Show the form for creating a new craftsman staff.
     */
    public function create()
    {
        $craftsmen = Craftman::all();
        return view('super-admin.business-partner.craftsman-staff.create', compact('craftsmen'));
    }

    /**
     * Store a newly created craftsman staff in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_code' => 'required|string|unique:craftsman_staff',
            'craftsman_id' => 'required|exists:craftmen,id',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15|unique:craftsman_staff',
            'email' => 'required|email|max:255|unique:craftsman_staff',
            'aadhar_number' => 'nullable|string|max:20|unique:craftsman_staff',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $staff = new CraftsmanStaff([
            'staff_code' => $request->staff_code,
            'craftsman_id' => $request->craftsman_id,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'aadhar_number' => $request->aadhar_number,
            'permissions' => $request->permissions ?? [],
            'password' => bcrypt($request->password),
            'password_plain' => $request->password,
            'is_active' => true,
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_image_' . $file->getClientOriginalName();
            $file->storeAs('craftsman-staff', $filename);
            $staff->image = 'craftsman-staff/' . $filename;
        }

        if ($request->hasFile('aadhar_image')) {
            $file = $request->file('aadhar_image');
            $filename = time() . '_aadhar_' . $file->getClientOriginalName();
            $file->storeAs('craftsman-staff', $filename);
            $staff->aadhar_image = 'craftsman-staff/' . $filename;
        }

        $staff->save();

        return redirect()->route('super-admin.business-partner.craftsman-staff')
            ->with('success', 'Craftsman Staff created successfully!');
    }

    /**
     * Display the specified craftsman staff.
     */
    public function show(CraftsmanStaff $staff)
    {
        return view('super-admin.business-partner.craftsman-staff.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified craftsman staff.
     */
    public function edit(CraftsmanStaff $staff)
    {
        $craftsmen = Craftman::all();
        return view('super-admin.business-partner.craftsman-staff.edit', compact('staff', 'craftsmen'));
    }

    /**
     * Update the specified craftsman staff in storage.
     */
    public function update(Request $request, CraftsmanStaff $staff)
    {
        $validator = Validator::make($request->all(), [
            'staff_code' => 'required|string|unique:craftsman_staff,staff_code,' . $staff->id,
            'craftsman_id' => 'required|exists:craftmen,id',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15|unique:craftsman_staff,mobile,' . $staff->id,
            'email' => 'required|email|max:255|unique:craftsman_staff,email,' . $staff->id,
            'aadhar_number' => 'nullable|string|max:20|unique:craftsman_staff,aadhar_number,' . $staff->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $staff->staff_code = $request->staff_code;
        $staff->craftsman_id = $request->craftsman_id;
        $staff->name = $request->name;
        $staff->mobile = $request->mobile;
        $staff->email = $request->email;
        $staff->aadhar_number = $request->aadhar_number;
        $staff->permissions = $request->permissions ?? [];
        
        if ($request->password) {
            $staff->password = bcrypt($request->password);
            $staff->password_plain = $request->password;
        }

        if ($request->hasFile('image')) {
            if ($staff->image && Storage::exists('public/' . $staff->image)) {
                Storage::delete('public/' . $staff->image);
            }
            $file = $request->file('image');
            $filename = time() . '_image_' . $file->getClientOriginalName();
            $file->storeAs('craftsman-staff', $filename);
            $staff->image = 'craftsman-staff/' . $filename;
        }

        if ($request->hasFile('aadhar_image')) {
            if ($staff->aadhar_image && Storage::exists('public/' . $staff->aadhar_image)) {
                Storage::delete('public/' . $staff->aadhar_image);
            }
            $file = $request->file('aadhar_image');
            $filename = time() . '_aadhar_' . $file->getClientOriginalName();
            $file->storeAs('craftsman-staff', $filename);
            $staff->aadhar_image = 'craftsman-staff/' . $filename;
        }

        $staff->save();

        return redirect()->route('super-admin.business-partner.craftsman-staff')
            ->with('success', 'Craftsman Staff updated successfully!');
    }

    /**
     * Remove the specified craftsman staff from storage.
     */
    public function destroy(CraftsmanStaff $staff)
    {
        $staff->delete();

        return redirect()->route('super-admin.business-partner.craftsman-staff')
            ->with('success', 'Craftsman Staff deleted successfully!');
    }

    public function printSelected(Request $request)
    {
        $selectedIds = $request->input('selected_staffs', []);

        if (empty($selectedIds)) {
            return redirect()->back()->with('error', 'No staffs selected for printing.');
        }

        $staffs = CraftsmanStaff::whereIn('id', $selectedIds)->get();

        return view('super-admin.business-partner.craftsman-staff.print-selected', compact('staffs'));
    }
}