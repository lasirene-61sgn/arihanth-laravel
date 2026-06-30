<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\CompanyContact;
use Illuminate\Http\Request;

class CompanyContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contacts = CompanyContact::orderBy('type')->get();
        return view('super-admin.company_contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = CompanyContact::getTypes();
        return view('super-admin.company_contacts.create', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'is_active' => 'boolean',
        ]);

        if ($request->type === 'bank') {
            $data = $request->only(['bank_name', 'account_holder_name', 'account_number', 'ifsc_code', 'branch', 'bank_city', 'bank_state']);
        } else {
            $data = $request->only(['value']);
        }
        
        CompanyContact::create([
            'type' => $request->type,
            'data' => $data,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return redirect()->route('super-admin.company-contacts.index')->with('success', 'Contact Detail added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CompanyContact $companyContact)
    {
        return view('super-admin.company_contacts.show', compact('companyContact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompanyContact $companyContact)
    {
        $types = CompanyContact::getTypes();
        return view('super-admin.company_contacts.edit', compact('companyContact', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompanyContact $companyContact)
    {
        $request->validate([
            'type' => 'required',
            'is_active' => 'boolean',
        ]);

        if ($request->type === 'bank') {
            $data = $request->only(['bank_name', 'account_holder_name', 'account_number', 'ifsc_code', 'branch', 'bank_city', 'bank_state']);
        } else {
            $data = $request->only(['value']);
        }

        $companyContact->update([
            'type' => $request->type,
            'data' => $data,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return redirect()->route('super-admin.company-contacts.index')->with('success', 'Contact Detail updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompanyContact $companyContact)
    {
        $companyContact->delete();
        return redirect()->route('super-admin.company-contacts.index')->with('success', 'Contact Detail deleted successfully.');
    }
}
