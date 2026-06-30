<?php

namespace App\Http\Controllers\Craftsman;

use App\Http\Controllers\Controller;
use App\Models\Craftman;
use App\Models\WorkOrder;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Show the craftsman login form.
     */
    public function showLoginForm()
    {
        return view('craftsman.login');
    }

    /**
     * Handle craftsman login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'craftman_code' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find craftsman by craftman_code
        $craftsman = Craftman::where('craftman_code', $request->craftman_code)->first();

        // Check if craftsman exists and password is correct
        if ($craftsman && Hash::check($request->password, $craftsman->password)) {
            // Check if account is frozen
            if ($craftsman->is_frozen) {
                return back()->withErrors([
                    'craftman_code' => 'Your account has been frozen. Please contact the Super Admin.',
                ])->withInput($request->only('craftman_code'));
            }
            
            Auth::guard('craftsman')->login($craftsman);
            return redirect()->route('craftsman.dashboard');
        }

        return back()->withErrors([
            'craftman_code' => 'Invalid credentials.',
        ])->withInput($request->only('craftman_code'));
    }

    /**
     * Show the craftsman dashboard.
     */
    public function dashboard()
    {
        $craftsman = Auth::guard('craftsman')->user();
        $craftsmanCode = $craftsman->craftman_code;
        $now = now()->toDateString();
        $isLate = now()->hour >= 12;

        // Get allocated work orders for the list (the "active" ones)
        $workOrders = WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)
            ->where('craftsman_status', 'allocated')
            ->get();

        // Work Order Statistics
        $woQuery = WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode);
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
        $poQuery = PurchaseOrder::where('allocated_craftsman_code', $craftsmanCode);
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

        // PO Weights calculation (manual since it's JSON items)
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

        // Craftsman-wise Progress Analytics for Modal (Self only)
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
        
        return view('craftsman.dashboard', compact(
            'craftsman', 
            'workOrders', 
            'woStats',
            'poStats',
            'totalProducts', 
            'totalDesigns',
            'craftsmanStats'
        ));
    }

    /**
     * Handle craftsman logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('craftsman')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('craftsman.login');
    }
    
    public function finance()
    {
        return view('craftsman.finance.index');
    }
}