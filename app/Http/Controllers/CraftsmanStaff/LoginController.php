<?php

namespace App\Http\Controllers\CraftsmanStaff;

use App\Http\Controllers\Controller;
use App\Models\CraftsmanStaff;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('craftsman_staff.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'staff_code' => 'required|string',
            'password' => 'required|string',
        ]);

        $staff = CraftsmanStaff::where('staff_code', $request->staff_code)->first();

        if ($staff && Hash::check($request->password, $staff->password)) {
            if (!$staff->is_active) {
                return back()->withErrors([
                    'staff_code' => 'Your account is inactive. Please contact your Craftsman.',
                ])->withInput($request->only('staff_code'));
            }
            
            Auth::guard('craftsman_staff')->login($staff);
            return redirect()->route('craftsman_staff.dashboard');
        }

        return back()->withErrors([
            'staff_code' => 'Invalid credentials.',
        ])->withInput($request->only('staff_code'));
    }

    public function dashboard(Request $request)
    {
        $staff = Auth::guard('craftsman_staff')->user();
        $craftsman = $staff->craftsman;
        if (!$craftsman) {
            return redirect()->route('craftsman_staff.login')->withErrors(['staff_code' => 'No associated craftsman found.']);
        }

        $craftsmanCode = $craftsman->craftman_code;
        $now = Carbon::now();
        $isLate = $now->hour >= 12;

        // Fetch Work Orders
        $allWorkOrders = WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)->get();
        
        // Fetch Purchase Orders
        $allPurchaseOrders = PurchaseOrder::where('allocated_craftsman_code', $craftsmanCode)->get();

        // Work Order Statistics
        $woAllocatedCount = 0;
        $woInProcessCount = 0;
        $woCompletedCount = 0;
        $woOverdueCount = 0;
        $woAllocatedWeight = 0;
        $woInProcessWeight = 0;
        $woOverdueWeight = 0;

        foreach ($allWorkOrders as $wo) {
            $weight = (float)($wo->weight_to ?? $wo->weight_from ?? 0);
            $dueDate = $wo->craftsman_due_date ?? $wo->due_date;
            $parsedDueDate = $dueDate ? Carbon::parse($dueDate)->startOfDay() : null;
            
            $isOverdue = $wo->status !== 'completed' && $wo->craftsman_status !== 'rejected' && $parsedDueDate &&
                         ($parsedDueDate->lt($now->copy()->startOfDay()) || ($parsedDueDate->eq($now->copy()->startOfDay()) && $isLate));
            
            $wo->is_delayed = $isOverdue;
            $wo->days_delayed = ($isOverdue && $parsedDueDate) ? (int)$now->diffInDays($parsedDueDate) : 0;
            if ($wo->is_delayed && $wo->days_delayed === 0) {
                $wo->days_delayed = 1;
            }

            if ($wo->craftsman_status === 'allocated') {
                $woAllocatedCount++;
                $woAllocatedWeight += $weight;
            } elseif ($wo->craftsman_status === 'in_process') {
                $woInProcessCount++;
                $woInProcessWeight += $weight;
            } elseif ($wo->craftsman_status === 'completed' || $wo->status === 'completed') {
                $woCompletedCount++;
            }

            if ($isOverdue) {
                $woOverdueCount++;
                $woOverdueWeight += $weight;
            }
        }

        $woStats = [
            'total' => $allWorkOrders->count(),
            'allocated' => $woAllocatedCount,
            'in_process' => $woInProcessCount,
            'completed' => $woCompletedCount,
            'overdue' => $woOverdueCount,
            'allocated_weight' => $woAllocatedWeight,
            'in_process_weight' => $woInProcessWeight,
            'overdue_weight' => $woOverdueWeight,
        ];

        // Purchase Order Statistics & Weight Calculations
        $poAllocatedCount = 0;
        $poInProcessCount = 0;
        $poCompletedCount = 0;
        $poOverdueCount = 0;
        $poAllocatedWeight = 0;
        $poInProcessWeight = 0;
        $poOverdueWeight = 0;

        foreach ($allPurchaseOrders as $po) {
            $totalWeight = 0;
            $totalQty = 0;
            $items = $po->items ?? [];
            foreach ($items as $item) {
                $totalWeight += (float)($item['weight'] ?? 0);
                $totalQty += (int)($item['quantity'] ?? 1);
            }
            $po->calculated_weight = $totalWeight;
            $po->calculated_qty = $totalQty;

            $parsedPoDueDate = $po->due_date ? Carbon::parse($po->due_date)->startOfDay() : null;
            $isOverdue = !in_array($po->status, ['completed', 'approved']) && $po->craftsman_status !== 'rejected' && $parsedPoDueDate &&
                         ($parsedPoDueDate->lt($now->copy()->startOfDay()) || ($parsedPoDueDate->eq($now->copy()->startOfDay()) && $isLate));

            $po->is_delayed = $isOverdue;
            $po->days_delayed = ($isOverdue && $parsedPoDueDate) ? (int)$now->diffInDays($parsedPoDueDate) : 0;
            if ($po->is_delayed && $po->days_delayed === 0) {
                $po->days_delayed = 1;
            }

            if ($po->craftsman_status === 'allocated') {
                $poAllocatedCount++;
                $poAllocatedWeight += $totalWeight;
            } elseif ($po->craftsman_status === 'in_process') {
                $poInProcessCount++;
                $poInProcessWeight += $totalWeight;
            } elseif (in_array($po->status, ['completed', 'approved'])) {
                $poCompletedCount++;
            }

            if ($isOverdue) {
                $poOverdueCount++;
                $poOverdueWeight += $totalWeight;
            }
        }

        $poStats = [
            'total' => $allPurchaseOrders->count(),
            'allocated' => $poAllocatedCount,
            'in_process' => $poInProcessCount,
            'completed' => $poCompletedCount,
            'overdue' => $poOverdueCount,
            'allocated_weight' => $poAllocatedWeight,
            'in_process_weight' => $poInProcessWeight,
            'overdue_weight' => $poOverdueWeight,
        ];

        $totalProducts = Product::where('bp_code', $craftsmanCode)->count();
        $totalDesigns = Product::where('bp_code', $craftsmanCode)->count();

        // Progress Analytics
        $craftsmanStats = [
            $craftsmanCode => [
                'name' => $craftsman->business_name ?: $craftsman->name,
                'wa' => [
                    'process' => ['count' => $woStats['in_process'], 'weight' => $woStats['in_process_weight']],
                    'overdue' => ['count' => $woStats['overdue'], 'weight' => $woStats['overdue_weight']],
                ],
                'po' => [
                    'process' => ['count' => $poStats['in_process'], 'weight' => $poStats['in_process_weight']],
                    'overdue' => ['count' => $poStats['overdue'], 'weight' => $poStats['overdue_weight']],
                ]
            ]
        ];

        return view('craftsman_staff.dashboard', compact(
            'staff',
            'craftsman',
            'allWorkOrders',
            'allPurchaseOrders',
            'woStats',
            'poStats',
            'totalProducts',
            'totalDesigns',
            'craftsmanStats'
        ));
    }

    public function logout(Request $request)
    {
        Auth::guard('craftsman_staff')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('craftsman_staff.login');
    }
}
