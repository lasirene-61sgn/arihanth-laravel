<?php

namespace App\Http\Controllers\Craftsman;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Repair;
use Illuminate\Support\Facades\Auth;

class RepairController extends Controller
{
    public function index()
    {
        $craftsmanCode = Auth::guard('craftsman')->user()->craftman_code;
        $repairs = Repair::with('buyer')
            ->where('allocated_craftsman_code', $craftsmanCode)
            ->latest()
            ->paginate(10);
        return view('craftsman.repairs.index', compact('repairs'));
    }

    public function accept($id)
    {
        $craftsmanCode = Auth::guard('craftsman')->user()->craftman_code;
        $repair = Repair::where('allocated_craftsman_code', $craftsmanCode)->findOrFail($id);
        $repair->update([
            'craftsman_status' => 'Accepted',
            'status' => 'In_Process'
        ]);
        return redirect()->route('craftsman.repairs.index')->with('success', 'Repair accepted.');
    }

    public function reject($id)
    {
        $craftsmanCode = Auth::guard('craftsman')->user()->craftman_code;
        $repair = Repair::where('allocated_craftsman_code', $craftsmanCode)->findOrFail($id);
        $repair->update([
            'craftsman_status' => 'Rejected',
            'status' => 'Craftsman_Rejected',
        ]);
        return redirect()->route('craftsman.repairs.index')->with('success', 'Repair rejected.');
    }

    public function complete($id)
    {
        $craftsmanCode = Auth::guard('craftsman')->user()->craftman_code;
        $repair = Repair::where('allocated_craftsman_code', $craftsmanCode)->findOrFail($id);
        $repair->update([
            'craftsman_status' => 'Completed',
            'status' => 'Craftsman_Completed',
        ]);
        return redirect()->route('craftsman.repairs.index')->with('success', 'Repair marked as completed by craftsman.');
    }
}
