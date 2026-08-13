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
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('repair_view') && !$staff->hasPermission('repair_accept') && !$staff->hasPermission('repair_reject')) {
                abort(403, 'Unauthorized action.');
            }
        }
        $craftsmanCode = $this->currentCraftsman()->craftman_code;
        $repairs = Repair::with('buyer')
            ->where('allocated_craftsman_code', $craftsmanCode)
            ->latest()
            ->paginate(10);
        return view('craftsman.repairs.index', compact('repairs'));
    }

    public function show($id)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('repair_view') && !$staff->hasPermission('repair_accept') && !$staff->hasPermission('repair_reject')) {
                abort(403, 'Unauthorized action.');
            }
        }
        $craftsmanCode = $this->currentCraftsman()->craftman_code;
        $repair = Repair::with('buyer')->where('allocated_craftsman_code', $craftsmanCode)->findOrFail($id);
        return view('craftsman.repairs.show', compact('repair'));
    }

    public function accept($id)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('repair_accept')) abort(403, 'Unauthorized action.');
        }
        $craftsmanCode = $this->currentCraftsman()->craftman_code;
        $repair = Repair::where('allocated_craftsman_code', $craftsmanCode)->findOrFail($id);
        $updateData = [
            'craftsman_status' => 'Accepted',
            'status' => 'In_Process',
            'craftsman_accepted_at' => now(),
        ];
        if ($staff = $this->currentStaff()) {
            $updateData['accepted_by_staff_id'] = $staff->id;
            $updateData['staff_accepted_at'] = now();
        }
        $repair->update($updateData);
        return redirect()->route('craftsman.repairs.index')->with('success', 'Repair accepted.');
    }

    public function reject($id)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('repair_reject')) abort(403, 'Unauthorized action.');
        }
        $craftsmanCode = $this->currentCraftsman()->craftman_code;
        $repair = Repair::where('allocated_craftsman_code', $craftsmanCode)->findOrFail($id);
        $repair->update([
            'craftsman_status' => 'Rejected',
            'status' => 'Craftsman_Rejected',
        ]);
        return redirect()->route('craftsman.repairs.index')->with('success', 'Repair rejected.');
    }

    public function complete($id)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('repair_accept')) abort(403, 'Unauthorized action.');
        }
        $craftsmanCode = $this->currentCraftsman()->craftman_code;
        $repair = Repair::where('allocated_craftsman_code', $craftsmanCode)->findOrFail($id);
        $updateData = [
            'craftsman_status' => 'Completed',
            'status' => 'Craftsman_Completed',
            'craftsman_completed_at' => now(),
        ];
        if ($staff = $this->currentStaff()) {
            $updateData['staff_completed_at'] = now();
        }
        $repair->update($updateData);
        return redirect()->route('craftsman.repairs.index')->with('success', 'Repair marked as completed by craftsman.');
    }
}
