<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\CreateReapir;
use App\Http\Requests\Superadmin\UpdateReapir;
use App\Models\Buyer;
use App\Models\Repair;
use Illuminate\Http\Request;

class RepairController extends Controller
{
    public function index()
    {
        $repairs = Repair::with('buyer')->latest()->paginate(15);
        return view('super-admin.repairs.index', compact('repairs'));
    }

    public function create()
    {
        $buyers = Buyer::all();
        
        // Fetch unique previously used names directly from the repairs table for dropdowns
        $deliveredNames = Repair::whereNotNull('item_delivered_by')->distinct()->pluck('item_delivered_by');
        $receivedNames = Repair::whereNotNull('item_received_by')->distinct()->pluck('item_received_by');

        return view('super-admin.repairs.create', compact('buyers', 'deliveredNames', 'receivedNames'));
    }

    public function store(CreateReapir $request)
    {
        Repair::create($request->validated());

        return redirect()->route('super-admin.repairs.index')
            ->with('success', 'Repair/Sample record created successfully.');
    }

    public function show(Repair $repair)
    {
        return view('super-admin.repairs.show', compact('repair'));
    }

    public function edit(Repair $repair)
    {
        $buyers = Buyer::all();
        
        $deliveredNames = Repair::whereNotNull('item_delivered_by')->distinct()->pluck('item_delivered_by');
        $receivedNames = Repair::whereNotNull('item_received_by')->distinct()->pluck('item_received_by');

        return view('super-admin.repairs.edit', compact('repair', 'buyers', 'deliveredNames', 'receivedNames'));
    }

    public function update(UpdateReapir $request, Repair $repair)
    {
        $repair->update($request->validated());

        return redirect()->route('super-admin.repairs.index')
            ->with('success', 'Repair/Sample record updated successfully.');
    }

    public function destroy(Repair $repair)
    {
        $repair->delete();

        return redirect()->route('super-admin.repairs.index')
            ->with('success', 'Repair/Sample record deleted successfully.');
    }
}