<?php

namespace App\Http\Controllers\CraftsmanStaff;

use App\Http\Controllers\Controller;
use App\Models\CraftsmanStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

    public function dashboard()
    {
        $staff = Auth::guard('craftsman_staff')->user();
        $craftsman = $staff->craftsman;
        if (!$craftsman) {
            return redirect()->route('craftsman_staff.login')->withErrors(['staff_code' => 'No associated craftsman found.']);
        }
        
        $craftsmanCode = $craftsman->craftman_code;
        $now = now()->toDateString();
        $isLate = now()->hour >= 12;

        // Get allocated work orders for the list (the "active" ones)
        $workOrders = \App\Models\WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)
            ->where('craftsman_status', 'allocated')
            ->get();

        // Work Order Statistics
        $woQuery = \App\Models\WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode);
        $woStats = [
            'total' => (clone $woQuery)->count(),
            'allocated' => (clone $woQuery)->where('craftsman_status', 'allocated')->count(),
            'in_process' => (clone $woQuery)->where('craftsman_status', 'in_process')->count(),
            'completed' => (clone $woQuery)->where('craftsman_status', 'completed')->count(),
            'for_approval' => (clone $woQuery)->where('status', 'for_approval')->count(),
            'rejected' => (clone $woQuery)->where('craftsman_status', 'rejected')->count(),
            'overdue' => (clone $woQuery)->where('status', '!=', 'completed')
                ->where('craftsman_status', '!=', 'rejected')
                ->where(function($q) use ($now, $isLate) {
                    $q->whereDate('due_date', '<', $now)
                      ->orWhere(function($sq) use ($now, $isLate) {
                          if ($isLate) {
                              $sq->whereDate('due_date', $now);
                          } else {
                              $sq->whereRaw('1=0');
                          }
                      });
                })->count(),
            'allocated_weight' => (clone $woQuery)->where('craftsman_status', 'allocated')->sum('weight_to'),
            'in_process_weight' => (clone $woQuery)->where('craftsman_status', 'in_process')->sum('weight_to'),
            'overdue_weight' => (clone $woQuery)->where('status', '!=', 'completed')
                ->where('craftsman_status', '!=', 'rejected')
                ->where(function($q) use ($now, $isLate) {
                    $q->whereDate('due_date', '<', $now)
                      ->orWhere(function($sq) use ($now, $isLate) {
                          if ($isLate) {
                              $sq->whereDate('due_date', $now);
                          } else {
                              $sq->whereRaw('1=0');
                          }
                      });
                })->sum('weight_to'),
        ];

        // Purchase Order Statistics
        $poQuery = \App\Models\PurchaseOrder::where('allocated_craftsman_code', $craftsmanCode);
        $poStats = [
            'total' => (clone $poQuery)->count(),
            'allocated' => (clone $poQuery)->where('craftsman_status', 'allocated')->count(),
            'in_process' => (clone $poQuery)->where('craftsman_status', 'in_process')->count(),
            'completed' => (clone $poQuery)->where('craftsman_status', 'completed')->count(),
            'for_approval' => (clone $poQuery)->where('status', 'for_approval')->count(),
            'rejected' => (clone $poQuery)->where(function($q) {
                $q->where('craftsman_status', 'rejected')
                  ->orWhereRaw('JSON_LENGTH(rejected_items) > 0');
            })->count(),
            'overdue' => (clone $poQuery)->where('status', '!=', 'completed')
                ->where('status', '!=', 'approved')
                ->where('craftsman_status', '!=', 'rejected')
                ->where(function($q) use ($now, $isLate) {
                    $q->whereDate('due_date', '<', $now)
                      ->orWhere(function($sq) use ($now, $isLate) {
                          if ($isLate) {
                              $sq->whereDate('due_date', $now);
                          } else {
                              $sq->whereRaw('1=0');
                          }
                      });
                })->count(),
        ];

        // PO Weights calculation
        $poAllocatedWeight = 0;
        $poInProcessWeight = 0;
        $poOverdueWeight = 0;

        foreach ((clone $poQuery)->get() as $po) {
            $poWeight = collect($po->items)->sum(function ($item) {
                return (float) ($item['weight'] ?? 0);
            });

            if ($po->craftsman_status == 'allocated') $poAllocatedWeight += $poWeight;
            if ($po->craftsman_status == 'in_process') $poInProcessWeight += $poWeight;
            
            $isOverdue = $po->status != 'completed' && $po->status != 'approved' && $po->craftsman_status != 'rejected' && 
                         (($po->due_date && $po->due_date < $now) || ($po->due_date == $now && $isLate));
            if ($isOverdue) $poOverdueWeight += $poWeight;
        }

        $poStats['allocated_weight'] = $poAllocatedWeight;
        $poStats['in_process_weight'] = $poInProcessWeight;
        $poStats['overdue_weight'] = $poOverdueWeight;
        
        $totalProducts = \App\Models\Product::where('bp_code', $craftsmanCode)->count();
        $totalDesigns = \App\Models\Product::where('bp_code', $craftsmanCode)->count();

        // Craftsman-wise Progress Analytics for Modal
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
            'workOrders', 
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
