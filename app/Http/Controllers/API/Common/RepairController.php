<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Repair;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\ProcessOwner;
use App\Models\CraftsmanStaff;
use App\Models\KeyUser;
use App\Models\User;
use App\Notifications\RepairAllocated;
use App\Notifications\RepairCompleted;
use Dompdf\Dompdf;
use Dompdf\Options;

class RepairController extends Controller
{
    // =========================================================================
    // HELPERS — Role Detection & Permissions
    // =========================================================================

    private function isAdmin($user): bool
    {
        if (!$user) return false;
        return in_array($user->role ?? '', ['super_admin', 'admin', 'process_owner'])
            || $user instanceof ProcessOwner;
    }

    private function isCraftsman($user): bool
    {
        if (!$user) return false;
        return ($user->role ?? '') === 'craftsman'
            || $user instanceof Craftman
            || $user instanceof CraftsmanStaff;
    }

    private function getCraftsmanCode($user): ?string
    {
        if (!$user) return null;
        if ($user instanceof CraftsmanStaff) {
            return $user->craftsman->craftman_code ?? ($user->craftman_code ?? null);
        }
        if ($user instanceof Craftman) {
            return $user->craftman_code;
        }
        return $user->craftman_code ?? null;
    }

    private function isBuyerSide($user): bool
    {
        if (!$user) return false;
        return $user instanceof Buyer
            || $user instanceof KeyUser
            || $user instanceof User
            || ($user->role ?? '') === 'buyer';
    }

    private function getBuyerId($user): ?int
    {
        if (!$user) return null;
        if ($user instanceof Buyer) {
            return $user->id;
        }
        if (!empty($user->bp_code)) {
            $buyer = Buyer::where('bp_code', $user->bp_code)->first();
            if ($buyer) return $buyer->id;
        }
        if (!empty($user->buyer_id)) {
            return (int)$user->buyer_id;
        }
        return null;
    }

    private function getUserRole($user): string
    {
        if ($this->isAdmin($user)) return 'admin';
        if ($this->isCraftsman($user)) return 'craftsman';
        if ($this->isBuyerSide($user)) return 'buyer';
        return 'guest';
    }

    /**
     * Define proper tabs for SuperAdmin, Craftsman, and Buyer matching web panels
     */
    private function getTabsForRole(string $role, array $counts = []): array
    {
        if ($role === 'admin') {
            return [
                ['key' => 'new',                'label' => 'New Orders (Pending)',     'count' => $counts['new'] ?? 0],
                ['key' => 'accepted',           'label' => 'Accepted',                 'count' => $counts['accepted'] ?? 0],
                ['key' => 'allocated',          'label' => 'Allocated to Craftman',    'count' => $counts['allocated'] ?? 0],
                ['key' => 'craftsman_approval', 'label' => 'Craftsman Approval',       'count' => $counts['craftsman_approval'] ?? 0],
                ['key' => 'approval',           'label' => 'Approval',                 'count' => $counts['approval'] ?? 0],
                ['key' => 'buyer_approval',     'label' => 'Buyer Approval',           'count' => $counts['buyer_approval'] ?? 0],
                ['key' => 'completed',          'label' => 'Completed',                'count' => $counts['completed'] ?? 0],
                ['key' => 'rejected',           'label' => 'Rejected',                 'count' => $counts['rejected'] ?? 0],
                ['key' => 'all',                'label' => 'All Records',              'count' => $counts['all'] ?? 0],
            ];
        }

        if ($role === 'craftsman') {
            return [
                ['key' => 'assigned',         'label' => 'New Assigned',                  'count' => $counts['assigned'] ?? 0],
                ['key' => 'in_process',        'label' => 'In Process',                    'count' => $counts['in_process'] ?? 0],
                ['key' => 'pending_approval',  'label' => 'Pending Superadmin Approval',   'count' => $counts['pending_approval'] ?? 0],
                ['key' => 'completed',         'label' => 'Completed',                    'count' => $counts['completed'] ?? 0],
                ['key' => 'all',               'label' => 'All Orders',                   'count' => $counts['all'] ?? 0],
            ];
        }

        // Buyer role (includes both high-level active/approval and granular status tabs)
        return [
            ['key' => 'active',    'label' => 'In Progress / Active',  'count' => $counts['active'] ?? 0],
            ['key' => 'approval',  'label' => 'Pending Acceptance',    'count' => $counts['approval'] ?? 0],
            ['key' => 'completed', 'label' => 'Completed',             'count' => $counts['completed'] ?? 0],
            ['key' => 'new',       'label' => 'New Orders (Pending)',  'count' => $counts['new'] ?? 0],
            ['key' => 'accepted',  'label' => 'Accepted',              'count' => $counts['accepted'] ?? 0],
            ['key' => 'allocated', 'label' => 'Allocated',             'count' => $counts['allocated'] ?? 0],
            ['key' => 'rejected',  'label' => 'Rejected',              'count' => $counts['rejected'] ?? 0],
            ['key' => 'all',       'label' => 'All Orders',            'count' => $counts['all'] ?? 0],
        ];
    }

    private function checkPermission($user, $specificPermission = 'repair_view'): bool
    {
        if ($this->isAdmin($user)) return true;
        if ($this->isBuyerSide($user)) return true;

        if ($user instanceof CraftsmanStaff) {
            return method_exists($user, 'hasPermission') ? $user->hasPermission($specificPermission) : true;
        }

        if ($user instanceof Craftman || ($user->role ?? '') === 'craftsman') {
            return true;
        }

        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission('repairs');
        }

        return false;
    }

    private function formatImageUrl(?string $path): ?string
    {
        if (!$path) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }
        if (file_exists(public_path($path))) {
            return asset($path);
        }
        return asset('storage/' . ltrim($path, '/'));
    }

    private function transformRepair(Repair $repair, $user): Repair
    {
        $repair->repair_image_url = $this->formatImageUrl($repair->repair_image);
        $repair->craftsman_voucher_image_url = $this->formatImageUrl($repair->craftsman_voucher_image);
        $repair->craftsman_completion_proof_image_url = $this->formatImageUrl($repair->craftsman_completion_proof_image);
        $repair->buyer_voucher_image_url = $this->formatImageUrl($repair->buyer_voucher_image);
        $repair->buyer_completion_proof_image_url = $this->formatImageUrl($repair->buyer_completion_proof_image);

        $admin = $this->isAdmin($user);
        $isBuyer = $this->isBuyerSide($user);
        $isCraftsman = $this->isCraftsman($user);
        $buyerId = $this->getBuyerId($user);
        $craftsmanCode = $this->getCraftsmanCode($user);

        $repair->permissions = [
            'can_edit' => $admin || ($isBuyer && $repair->buyer_id == $buyerId && $repair->status === 'pending'),
            'can_delete' => $admin || ($isBuyer && $repair->buyer_id == $buyerId && $repair->status === 'pending'),
            'can_allocate' => $admin && in_array($repair->status, ['pending', 'received']),
            'can_admin_accept' => $admin && $repair->status === 'pending',
            'can_admin_reject' => $admin && in_array($repair->status, ['pending', 'allocated']),
            'can_admin_approve_completion' => $admin && $repair->craftsman_status === 'pending_approval',
            'can_admin_complete' => $admin && $repair->status !== 'completed',
            'can_craftsman_accept' => $isCraftsman && $repair->craftman_code === $craftsmanCode && ($repair->craftsman_status === 'pending_acceptance' || is_null($repair->craftsman_status)),
            'can_craftsman_complete' => $isCraftsman && $repair->craftman_code === $craftsmanCode && in_array($repair->craftsman_status, ['in_progress', 'pending_acceptance', 'accepted', null]),
            'can_buyer_accept' => $isBuyer && $repair->buyer_id == $buyerId && $repair->craftsman_status === 'approved_by_superadmin' && $repair->status !== 'completed',
        ];

        return $repair;
    }

    // =========================================================================
    // QUERY SCOPING & FILTERING
    // =========================================================================

    private function scopeQueryByRole($query, $user)
    {
        if ($this->isAdmin($user)) {
            return $query;
        }

        if ($this->isCraftsman($user)) {
            $craftsmanCode = $this->getCraftsmanCode($user);
            return $query->where('craftman_code', $craftsmanCode);
        }

        if ($this->isBuyerSide($user)) {
            $buyerId = $this->getBuyerId($user);
            return $query->where('buyer_id', $buyerId);
        }

        return $query->whereRaw('1=0');
    }

    private function applyFilters($query, Request $request, $user)
    {
        $admin = $this->isAdmin($user);

        // Repair or Sample Filter
        if ($request->filled('repair_or_sample')) {
            $query->where('repair_or_sample', $request->repair_or_sample);
        }

        // Date-wise Filters (from_date / to_date / date)
        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        } elseif ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        } elseif ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('date') && !$request->filled('from_date') && !$request->filled('date_from')) {
            $query->whereDate('date', $request->date);
        }

        // Craftsman Filter (Admin / SuperAdmin)
        if ($admin) {
            if ($request->filled('craftsman_filter')) {
                $query->where('craftman_code', $request->craftsman_filter);
            } elseif ($request->filled('craftsman_code')) {
                $query->where('craftman_code', $request->craftsman_code);
            }
        }

        // Buyer Filter (Admin / SuperAdmin)
        if ($admin) {
            if ($request->filled('buyer_filter')) {
                $query->where('buyer_id', $request->buyer_filter);
            } elseif ($request->filled('buyer_id')) {
                $query->where('buyer_id', $request->buyer_id);
            } elseif ($request->filled('bp_code')) {
                $query->whereHas('buyer', function ($bq) use ($request) {
                    $bq->where('bp_code', $request->bp_code);
                });
            }
        }

        // Explicit status filter (only when tab is not provided)
        if ($request->filled('status') && !$request->filled('tab')) {
            $query->where('status', $request->status);
        }

        // Keyword Search
        $search = $request->get('search') ?: $request->get('q');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('product_name', 'LIKE', "%{$search}%")
                  ->orWhere('reference_number', 'LIKE', "%{$search}%")
                  ->orWhere('repair_details', 'LIKE', "%{$search}%")
                  ->orWhere('craftman_code', 'LIKE', "%{$search}%")
                  ->orWhereHas('buyer', function ($bq) use ($search) {
                      $bq->where('business_name', 'LIKE', "%{$search}%")
                         ->orWhere('name', 'LIKE', "%{$search}%")
                         ->orWhere('bp_code', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('craftsman', function ($cq) use ($search) {
                      $cq->where('business_name', 'LIKE', "%{$search}%")
                         ->orWhere('name', 'LIKE', "%{$search}%")
                         ->orWhere('craftman_code', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Selection by specific IDs
        if ($request->filled('repair_ids') || $request->filled('ids')) {
            $ids = $request->get('repair_ids') ?: $request->get('ids');
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            if (is_array($ids)) {
                $query->whereIn('id', $ids);
            }
        }

        return $query;
    }

    // =========================================================================
    // INDEX — Tabs-wise Listing & Counts for All 4 Panels
    // =========================================================================

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$this->checkPermission($user, 'repair_view')) {
            return response()->json(['success' => false, 'message' => 'Forbidden – no repair permission'], 403);
        }

        $role = $this->getUserRole($user);

        // 1. Base scoped query with filters applied
        $baseQuery = Repair::query();
        $this->scopeQueryByRole($baseQuery, $user);
        $this->applyFilters($baseQuery, $request, $user);

        $counts = [];
        $activeTab = $request->get('tab');

        // 2. Tab definitions & counts based on Role (matching website panels)
        if ($role === 'admin') {
            $counts = [
                'all'                => (clone $baseQuery)->count(),
                'new'                => (clone $baseQuery)->whereIn('status', ['pending', 'Pending'])->count(),
                'accepted'           => (clone $baseQuery)->whereIn('status', ['accepted', 'Accepted'])->count(),
                'allocated'          => (clone $baseQuery)->whereIn('status', ['allocated', 'Allocated'])->where(function ($q) {
                                          $q->whereNull('craftsman_status')->orWhere('craftsman_status', '!=', 'pending_approval');
                                      })->count(),
                'craftsman_approval' => (clone $baseQuery)->where('craftsman_status', 'pending_approval')->count(),
                'approval'           => (clone $baseQuery)->where('craftsman_status', 'approved_by_superadmin')->whereNull('item_sent_by')->count(),
                'buyer_approval'     => (clone $baseQuery)->where('craftsman_status', 'approved_by_superadmin')->whereNotNull('item_sent_by')->whereNotIn('status', ['completed', 'Completed'])->count(),
                'completed'          => (clone $baseQuery)->whereIn('status', ['completed', 'Completed'])->count(),
                'rejected'           => (clone $baseQuery)->whereIn('status', ['rejected', 'Rejected'])->count(),
            ];

            // Backward compatibility alias
            $counts['received'] = $counts['new'];

            // Normalize activeTab
            $activeTab = match ($activeTab) {
                'pending', 'received'                     => 'new',
                'craftsman-approval'                      => 'craftsman_approval',
                'buyer-approval'                          => 'buyer_approval',
                default                                   => ($activeTab ?: 'new')
            };

            // Apply selected tab condition to query
            if ($activeTab === 'new') {
                $baseQuery->whereIn('status', ['pending', 'Pending']);
            } elseif ($activeTab === 'accepted') {
                $baseQuery->whereIn('status', ['accepted', 'Accepted']);
            } elseif ($activeTab === 'allocated') {
                $baseQuery->whereIn('status', ['allocated', 'Allocated'])->where(function ($q) {
                    $q->whereNull('craftsman_status')->orWhere('craftsman_status', '!=', 'pending_approval');
                });
            } elseif ($activeTab === 'craftsman_approval') {
                $baseQuery->where('craftsman_status', 'pending_approval');
            } elseif ($activeTab === 'approval') {
                $baseQuery->where('craftsman_status', 'approved_by_superadmin')->whereNull('item_sent_by');
            } elseif ($activeTab === 'buyer_approval') {
                $baseQuery->where('craftsman_status', 'approved_by_superadmin')->whereNotNull('item_sent_by')->whereNotIn('status', ['completed', 'Completed']);
            } elseif ($activeTab === 'completed') {
                $baseQuery->whereIn('status', ['completed', 'Completed']);
            } elseif ($activeTab === 'rejected') {
                $baseQuery->whereIn('status', ['rejected', 'Rejected']);
            } // if 'all', return all records

        } elseif ($role === 'craftsman') {
            $counts = [
                'all'              => (clone $baseQuery)->count(),
                'assigned'         => (clone $baseQuery)->whereIn('status', ['allocated', 'Allocated'])->where(function ($q) {
                                        $q->whereNull('craftsman_status')->orWhereIn('craftsman_status', ['pending_acceptance', 'allocated', 'Pending', 'pending']);
                                    })->count(),
                'in_process'       => (clone $baseQuery)->whereIn('status', ['allocated', 'Allocated'])->where('craftsman_status', 'in_process')->count(),
                'pending_approval' => (clone $baseQuery)->where('craftsman_status', 'pending_approval')->count(),
                'completed'        => (clone $baseQuery)->where(function ($q) {
                                        $q->whereIn('status', ['completed', 'Completed'])
                                          ->orWhereIn('craftsman_status', ['completed', 'Completed', 'approved_by_superadmin']);
                                    })->count(),
            ];

            // Normalize activeTab
            $activeTab = match ($activeTab) {
                'new', 'new_assigned', 'new-assigned'     => 'assigned',
                'in-process'                              => 'in_process',
                'pending-approval'                        => 'pending_approval',
                default                                   => ($activeTab ?: 'assigned')
            };

            // Apply selected tab condition to query
            if ($activeTab === 'assigned') {
                $baseQuery->whereIn('status', ['allocated', 'Allocated'])->where(function ($q) {
                    $q->whereNull('craftsman_status')->orWhereIn('craftsman_status', ['pending_acceptance', 'allocated', 'Pending', 'pending']);
                });
            } elseif ($activeTab === 'in_process') {
                $baseQuery->whereIn('status', ['allocated', 'Allocated'])->where('craftsman_status', 'in_process');
            } elseif ($activeTab === 'pending_approval') {
                $baseQuery->where('craftsman_status', 'pending_approval');
            } elseif ($activeTab === 'completed') {
                $baseQuery->where(function ($q) {
                    $q->whereIn('status', ['completed', 'Completed'])
                      ->orWhereIn('craftsman_status', ['completed', 'Completed', 'approved_by_superadmin']);
                });
            } // if 'all', return all records

        } elseif ($role === 'buyer') {
            $counts = [
                'all'       => (clone $baseQuery)->count(),
                'active'    => (clone $baseQuery)->whereNotIn('status', ['completed', 'Completed'])->where(function ($q) {
                                $q->whereNull('craftsman_status')->orWhere('craftsman_status', '!=', 'approved_by_superadmin');
                            })->count(),
                'approval'  => (clone $baseQuery)->where('craftsman_status', 'approved_by_superadmin')
                                ->whereNotNull('item_sent_by')
                                ->whereNotIn('status', ['completed', 'Completed'])->count(),
                'completed' => (clone $baseQuery)->whereIn('status', ['completed', 'Completed'])->count(),
                'new'       => (clone $baseQuery)->whereIn('status', ['pending', 'Pending'])->count(),
                'accepted'  => (clone $baseQuery)->whereIn('status', ['accepted', 'Accepted'])->count(),
                'allocated' => (clone $baseQuery)->whereIn('status', ['allocated', 'Allocated'])->count(),
                'rejected'  => (clone $baseQuery)->whereIn('status', ['rejected', 'Rejected'])->count(),
            ];

            // Normalize activeTab
            $activeTab = match ($activeTab) {
                'in_progress', 'in-progress'              => 'active',
                'pending_acceptance', 'pending-acceptance'=> 'approval',
                'pending'                                 => 'new',
                default                                   => ($activeTab ?: 'active')
            };

            // Apply selected tab condition to query
            if ($activeTab === 'active') {
                $baseQuery->whereNotIn('status', ['completed', 'Completed'])->where(function ($q) {
                    $q->whereNull('craftsman_status')->orWhere('craftsman_status', '!=', 'approved_by_superadmin');
                });
            } elseif ($activeTab === 'approval') {
                $baseQuery->where('craftsman_status', 'approved_by_superadmin')
                    ->whereNotNull('item_sent_by')
                    ->whereNotIn('status', ['completed', 'Completed']);
            } elseif ($activeTab === 'completed') {
                $baseQuery->whereIn('status', ['completed', 'Completed']);
            } elseif ($activeTab === 'new') {
                $baseQuery->whereIn('status', ['pending', 'Pending']);
            } elseif ($activeTab === 'accepted') {
                $baseQuery->whereIn('status', ['accepted', 'Accepted']);
            } elseif ($activeTab === 'allocated') {
                $baseQuery->whereIn('status', ['allocated', 'Allocated']);
            } elseif ($activeTab === 'rejected') {
                $baseQuery->whereIn('status', ['rejected', 'Rejected']);
            } // if 'all', return all records
        }

        // 3. Sorting
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = strtolower($request->get('sort_order', 'desc'));
        if (!in_array($sortBy, ['id', 'date', 'order_number', 'product_name', 'weight', 'status', 'created_at'])) {
            $sortBy = 'date';
        }
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $baseQuery->with(['buyer', 'craftsman'])->orderBy($sortBy, $sortOrder);

        $tabs = $this->getTabsForRole($role, $counts);

        // 4. Print export (Full unpaginated data)
        if ($request->has('print')) {
            $repairs = $baseQuery->get();
            $repairs->transform(fn($repair) => $this->transformRepair($repair, $user));
            return response()->json([
                'success'    => true,
                'role'       => $role,
                'active_tab' => $activeTab,
                'counts'     => $counts,
                'tabs'       => $tabs,
                'data'       => $repairs,
            ]);
        }

        // 5. Paginated Response
        $perPage = (int)$request->get('per_page', 15);
        $repairs = $baseQuery->paginate($perPage);
        $repairs->getCollection()->transform(fn($repair) => $this->transformRepair($repair, $user));

        return response()->json([
            'success'    => true,
            'role'       => $role,
            'active_tab' => $activeTab,
            'counts'     => $counts,
            'tabs'       => $tabs,
            'data'       => $repairs,
        ]);
    }

    // =========================================================================
    // SHOW — Single Repair Details
    // =========================================================================

    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->checkPermission($user, 'repair_view')) {
            return response()->json(['success' => false, 'message' => 'Forbidden – no repair permission'], 403);
        }

        $repair = Repair::with(['buyer', 'craftsman'])->find($id);
        if (!$repair) {
            return response()->json(['success' => false, 'message' => 'Repair not found'], 404);
        }

        $admin = $this->isAdmin($user);
        if (!$admin) {
            if ($this->isCraftsman($user) && $repair->craftman_code !== $this->getCraftsmanCode($user)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized – not assigned to you'], 403);
            }
            if ($this->isBuyerSide($user) && (int)$repair->buyer_id !== (int)$this->getBuyerId($user)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized – not your order'], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $this->transformRepair($repair, $user)
        ]);
    }

    // =========================================================================
    // STORE — Create Repair Order (Buyer & Admin)
    // =========================================================================

    public function store(Request $request)
    {
        $user = $request->user();

        // Craftsmen cannot create repairs
        if ($this->isCraftsman($user) && !$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Craftsmen cannot create repair requests.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'date'             => ['required', 'date'],
            'product_name'     => ['nullable', 'string', 'max:255'],
            'order_number'     => ['nullable', 'string', 'max:255', 'unique:repairs,order_number'],
            'repair_or_sample' => ['required', 'string'],
            'weight'           => ['nullable', 'numeric'],
            'repair_details'   => ['nullable', 'string'],
            'repair_image'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'buyer_id'         => ['nullable', 'exists:buyers,id'],
            'bp_code'          => ['nullable', 'exists:buyers,bp_code'],
            'notes'            => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $imagePath = null;
        if ($request->hasFile('repair_image')) {
            $imagePath = $request->file('repair_image')->store('repairs', 'public');
        }

        // Determine Buyer ID
        $buyerId = null;
        if ($request->filled('buyer_id')) {
            $buyerId = (int)$request->buyer_id;
        } elseif ($request->filled('bp_code')) {
            $buyer = Buyer::where('bp_code', $request->bp_code)->first();
            $buyerId = $buyer ? $buyer->id : null;
        } elseif ($request->filled('buyer')) {
            $buyerId = is_array($request->buyer) ? ($request->buyer['id'] ?? null) : $request->buyer;
        }

        // Fallback to logged-in user's buyer account if not provided in the request
        if (!$buyerId) {
            $buyerId = $this->getBuyerId($user);
        }

        if (!$buyerId) {
            return response()->json(['success' => false, 'message' => 'Unable to determine buyer account.'], 422);
        }

        $buyer = Buyer::find($buyerId);
        $deliveredBy = $buyer ? ($buyer->business_name ?? $buyer->name) : 'Buyer';

        $repair = Repair::create([
            'date'             => $request->date,
            'buyer_id'         => $buyerId,
            'product_name'     => $request->product_name,
            'order_number'     => $request->order_number,
            'repair_or_sample' => $request->repair_or_sample,
            'weight'           => $request->weight,
            'repair_details'   => $request->repair_details,
            'repair_image'     => $imagePath,
            'reference_number' => $request->reference_number,
            'notes'            => $request->notes,
            'item_delivered_by'=> $deliveredBy,
            'item_received_by' => 'Pending',
            'status'           => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Repair order created successfully.',
            'data'    => $this->transformRepair($repair->load(['buyer', 'craftsman']), $user),
        ], 201);
    }

    // =========================================================================
    // UPDATE — Edit Repair Order (Buyer: Pending only, Admin: Always, Craftsman: No edit)
    // =========================================================================

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $admin = $this->isAdmin($user);
        $repair = Repair::find($id);

        if (!$repair) {
            return response()->json(['success' => false, 'message' => 'Repair not found'], 404);
        }

        // Rule: Craftsman does NOT have edit option
        if ($this->isCraftsman($user) && !$admin) {
            return response()->json(['success' => false, 'message' => 'Craftsmen are not permitted to edit repairs.'], 403);
        }

        // Rule: Buyer can only edit if status is still pending and it belongs to them
        if (!$admin) {
            if ($this->isBuyerSide($user)) {
                if ((int)$repair->buyer_id !== (int)$this->getBuyerId($user)) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized – not your order.'], 403);
                }
                if ($repair->status !== 'pending') {
                    return response()->json(['success' => false, 'message' => 'You can only edit orders that are still pending.'], 403);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'date'             => ['required', 'date'],
            'product_name'     => ['required', 'string', 'max:255'],
            'order_number'     => ['required', 'string', 'max:255', 'unique:repairs,order_number,' . $repair->id],
            'repair_or_sample' => ['required', 'in:repair,sample'],
            'weight'           => ['nullable', 'numeric'],
            'repair_details'   => ['nullable', 'string'],
            'repair_image'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'reference_number' => ['nullable', 'string'],
            'notes'            => ['nullable', 'string'],
            'craftman_code'    => ['nullable', 'exists:craftmen,craftman_code'],
            'status'           => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $imagePath = $repair->repair_image;
        if ($request->hasFile('repair_image')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('repair_image')->store('repairs', 'public');
        }

        $updateData = [
            'date'             => $request->date,
            'product_name'     => $request->product_name,
            'order_number'     => $request->order_number,
            'repair_or_sample' => $request->repair_or_sample,
            'weight'           => $request->weight,
            'repair_details'   => $request->repair_details,
            'repair_image'     => $imagePath,
            'reference_number' => $request->reference_number ?? $repair->reference_number,
            'notes'            => $request->notes ?? $repair->notes,
        ];

        if ($admin) {
            if ($request->filled('craftman_code')) {
                $updateData['craftman_code'] = $request->craftman_code;
            }
            if ($request->filled('status')) {
                $updateData['status'] = $request->status;
            }
            if ($request->filled('buyer_id')) {
                $updateData['buyer_id'] = $request->buyer_id;
            }
        }

        $repair->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Repair order updated successfully.',
            'data'    => $this->transformRepair($repair->fresh(['buyer', 'craftsman']), $user),
        ]);
    }

    // =========================================================================
    // DESTROY — Delete Repair Order (Buyer: Pending only, Admin: Always)
    // =========================================================================

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $admin = $this->isAdmin($user);
        $repair = Repair::find($id);

        if (!$repair) {
            return response()->json(['success' => false, 'message' => 'Repair not found'], 404);
        }

        if ($this->isCraftsman($user) && !$admin) {
            return response()->json(['success' => false, 'message' => 'Craftsmen cannot delete repair requests.'], 403);
        }

        if (!$admin) {
            if ($this->isBuyerSide($user)) {
                if ((int)$repair->buyer_id !== (int)$this->getBuyerId($user)) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized – not your order.'], 403);
                }
                if ($repair->status !== 'pending') {
                    return response()->json(['success' => false, 'message' => 'You can only delete orders that are still pending.'], 403);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
        }

        if ($repair->repair_image && Storage::disk('public')->exists($repair->repair_image)) {
            Storage::disk('public')->delete($repair->repair_image);
        }

        $repair->delete();

        return response()->json([
            'success' => true,
            'message' => 'Repair order deleted successfully.'
        ]);
    }

    // =========================================================================
    // WORKFLOW ACTIONS
    // =========================================================================

    /**
     * Accept Repair
     * - Admin accepts pending repair -> status = 'received'
     * - Craftsman accepts assigned order -> craftsman_status = 'in_progress'
     */
    public function accept(Request $request, $id)
    {
        $user = $request->user();
        $repair = Repair::findOrFail($id);

        if ($this->isAdmin($user)) {
            $repair->update([
                'status'           => 'received',
                'item_received_by' => $user->name ?? 'Admin',
            ]);
            return response()->json(['success' => true, 'message' => 'Repair order accepted by admin.']);
        }

        if ($this->isCraftsman($user)) {
            if ($repair->craftman_code !== $this->getCraftsmanCode($user)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized – not assigned to you.'], 403);
            }
            $repair->update(['craftsman_status' => 'in_progress']);
            return response()->json(['success' => true, 'message' => 'Repair order accepted and in progress.']);
        }

        return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
    }

    /**
     * Reject Repair
     * - Admin rejects repair or craftsman's completion
     * - Craftsman rejects assigned order
     */
    public function reject(Request $request, $id)
    {
        $user = $request->user();
        $repair = Repair::findOrFail($id);
        $reason = $request->input('reject_reason') ?: $request->input('reason', 'Rejected');

        if ($this->isAdmin($user)) {
            if ($repair->craftsman_status === 'pending_approval') {
                $repair->update([
                    'craftsman_status' => 'in_progress',
                    'notes'            => ($repair->notes ? $repair->notes . "\n" : '') . "Completion rejected by admin: " . $reason,
                ]);
                return response()->json(['success' => true, 'message' => 'Craftsman completion rejected and returned to craftsman.']);
            }

            $repair->update([
                'status' => 'rejected',
                'notes'  => ($repair->notes ? $repair->notes . "\n" : '') . "Rejected: " . $reason,
            ]);
            return response()->json(['success' => true, 'message' => 'Repair order rejected by admin.']);
        }

        if ($this->isCraftsman($user)) {
            if ($repair->craftman_code !== $this->getCraftsmanCode($user)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized – not assigned to you.'], 403);
            }
            $repair->update([
                'craftsman_status' => 'rejected',
                'notes'            => ($repair->notes ? $repair->notes . "\n" : '') . "Craftsman rejected: " . $reason,
            ]);
            return response()->json(['success' => true, 'message' => 'Repair order rejected by craftsman.']);
        }

        return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
    }

    /**
     * Allocate Repair to Craftsman (Admin / SuperAdmin)
     */
    public function allocate(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden – admin only.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'craftman_code'    => ['required', 'string', 'exists:craftmen,craftman_code'],
            'item_sent_by'     => ['nullable', 'string', 'max:255'],
            'allocation_notes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $repair = Repair::findOrFail($id);
        $repair->update([
            'craftman_code'    => $request->craftman_code,
            'status'           => 'allocated',
            'craftsman_status' => 'pending_acceptance',
            'item_sent_by'     => $request->item_sent_by ?? $repair->item_sent_by,
            'notes'            => $request->allocation_notes ?? $repair->notes,
        ]);

        // Notify craftsman if notification class exists
        $craftsman = Craftman::where('craftman_code', $request->craftman_code)->first();
        if ($craftsman && method_exists($craftsman, 'notify')) {
            try {
                $craftsman->notify(new RepairAllocated($repair));
            } catch (\Exception $e) {
                Log::warning('Could not notify craftsman: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Repair order allocated to craftsman successfully.',
            'data'    => $this->transformRepair($repair->fresh(['buyer', 'craftsman']), $user),
        ]);
    }

    /**
     * Complete Repair
     * - Craftsman marks complete with proof images -> craftsman_status = 'pending_approval'
     * - Admin marks fully completed -> status = 'completed'
     */
    public function complete(Request $request, $id)
    {
        $user = $request->user();
        $repair = Repair::findOrFail($id);

        // Craftsman completion submission
        if ($this->isCraftsman($user)) {
            if ($repair->craftman_code !== $this->getCraftsmanCode($user)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized – not assigned to you.'], 403);
            }

            $validator = Validator::make($request->all(), [
                'item_collected_by'                => ['required', 'string', 'max:255'],
                'item_returned_by'                 => ['required', 'string', 'max:255'],
                'craftsman_voucher_image'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
                'craftsman_completion_proof_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $voucherPath = $repair->craftsman_voucher_image;
            if ($request->hasFile('craftsman_voucher_image')) {
                if ($voucherPath && Storage::disk('public')->exists($voucherPath)) {
                    Storage::disk('public')->delete($voucherPath);
                }
                $voucherPath = $request->file('craftsman_voucher_image')->store('repairs/vouchers', 'public');
            }

            $proofPath = $repair->craftsman_completion_proof_image;
            if ($request->hasFile('craftsman_completion_proof_image')) {
                if ($proofPath && Storage::disk('public')->exists($proofPath)) {
                    Storage::disk('public')->delete($proofPath);
                }
                $proofPath = $request->file('craftsman_completion_proof_image')->store('repairs/proofs', 'public');
            }

            $repair->update([
                'craftsman_status'                 => 'pending_approval',
                'item_collected_by'                => $request->item_collected_by,
                'item_returned_by'                 => $request->item_returned_by,
                'craftsman_voucher_image'          => $voucherPath,
                'craftsman_completion_proof_image' => $proofPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Repair completion submitted for admin approval.',
                'data'    => $this->transformRepair($repair->fresh(['buyer', 'craftsman']), $user),
            ]);
        }

        // Admin complete
        if ($this->isAdmin($user)) {
            $repair->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

            if ($repair->buyer && method_exists($repair->buyer, 'notify')) {
                try {
                    $repair->buyer->notify(new RepairCompleted($repair));
                } catch (\Exception $e) {
                    Log::warning('Could not notify buyer: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Repair order fully completed by admin.',
                'data'    => $this->transformRepair($repair->fresh(['buyer', 'craftsman']), $user),
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
    }

    /**
     * Approve Completion (Admin / SuperAdmin approves craftsman completion)
     */
    public function approveCompletion(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden – admin only.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'item_sent_by' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $repair = Repair::findOrFail($id);
        $repair->update([
            'craftsman_status' => 'approved_by_superadmin',
            'item_sent_by'     => $request->item_sent_by,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Craftsman completion approved. Ready for buyer collection/delivery.',
            'data'    => $this->transformRepair($repair->fresh(['buyer', 'craftsman']), $user),
        ]);
    }

    /**
     * Store Item Sent By (Admin / SuperAdmin)
     */
    public function storeItemSentBy(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden – admin only.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'item_sent_by' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $repair = Repair::findOrFail($id);
        $repair->update(['item_sent_by' => $request->item_sent_by]);

        return response()->json([
            'success' => true,
            'message' => 'Item sent by recorded successfully.',
            'data'    => $this->transformRepair($repair->fresh(['buyer', 'craftsman']), $user),
        ]);
    }

    /**
     * Mark Buyer Completed (Admin / SuperAdmin records collection & delivery)
     */
    public function markBuyerCompleted(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden – admin only.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'item_collected_by' => ['required', 'string', 'max:255'],
            'item_returned_by'  => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $repair = Repair::findOrFail($id);
        $repair->update([
            'status'            => 'completed',
            'item_collected_by' => $request->item_collected_by,
            'item_returned_by'  => $request->item_returned_by,
            'completed_at'      => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Repair marked as completed for buyer.',
            'data'    => $this->transformRepair($repair->fresh(['buyer', 'craftsman']), $user),
        ]);
    }

    /**
     * Buyer Accept Completion (Buyer confirms delivery & uploads proofs)
     */
    public function buyerAccept(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isBuyerSide($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized – buyer only.'], 403);
        }

        $repair = Repair::findOrFail($id);
        if ((int)$repair->buyer_id !== (int)$this->getBuyerId($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized – not your order.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'item_accepted_by'              => ['required', 'string', 'max:255'],
            'buyer_voucher_image'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'buyer_completion_proof_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $voucherPath = $repair->buyer_voucher_image;
        if ($request->hasFile('buyer_voucher_image')) {
            if ($voucherPath && Storage::disk('public')->exists($voucherPath)) {
                Storage::disk('public')->delete($voucherPath);
            }
            $voucherPath = $request->file('buyer_voucher_image')->store('repairs/buyer_vouchers', 'public');
        }

        $proofPath = $repair->buyer_completion_proof_image;
        if ($request->hasFile('buyer_completion_proof_image')) {
            if ($proofPath && Storage::disk('public')->exists($proofPath)) {
                Storage::disk('public')->delete($proofPath);
            }
            $proofPath = $request->file('buyer_completion_proof_image')->store('repairs/buyer_proofs', 'public');
        }

        $repair->update([
            'status'                       => 'completed',
            'item_accepted_by'             => $request->item_accepted_by,
            'buyer_voucher_image'          => $voucherPath,
            'buyer_completion_proof_image' => $proofPath,
            'completed_at'                 => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Repair order accepted and marked as completed successfully.',
            'data'    => $this->transformRepair($repair->fresh(['buyer', 'craftsman']), $user),
        ]);
    }

    /**
     * Buyer Reject Completion (Buyer rejects repair completion)
     */
    public function buyerReject(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isBuyerSide($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized – buyer only.'], 403);
        }

        $repair = Repair::findOrFail($id);
        if ((int)$repair->buyer_id !== (int)$this->getBuyerId($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized – not your order.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'reject_reason' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $repair->update([
            'craftsman_status' => 'rejected_by_buyer',
            'notes'            => ($repair->notes ? $repair->notes . "\n" : '') . "Buyer rejected completion: " . $request->reject_reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Repair completion rejected by buyer.',
            'data'    => $this->transformRepair($repair->fresh(['buyer', 'craftsman']), $user),
        ]);
    }

    /**
     * Bulk Complete (Admin / SuperAdmin)
     */
    public function bulkComplete(Request $request)
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden – admin only.'], 403);
        }

        $ids = $request->get('repair_ids') ?: $request->get('ids');
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'No repair IDs provided.'], 422);
        }

        $count = Repair::whereIn('id', $ids)->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} repairs successfully marked as completed."
        ]);
    }

    // =========================================================================
    // HELPERS — Dropdowns for Craftsmen & Buyers
    // =========================================================================

    public function helpers(Request $request)
    {
        $user = $request->user();
        $admin = $this->isAdmin($user);
        $role = $this->getUserRole($user);

        $craftsmen = Craftman::select('id', 'craftman_code', 'business_name', 'name')
            ->orderBy('craftman_code')
            ->get();

        $buyers = [];
        if ($admin) {
            $buyers = Buyer::select('id', 'bp_code', 'business_name', 'name')
                ->orderBy('bp_code')
                ->get();
        } elseif ($this->isBuyerSide($user)) {
            $buyerId = $this->getBuyerId($user);
            $buyers = Buyer::where('id', $buyerId)
                ->select('id', 'bp_code', 'business_name', 'name')
                ->get();
        }

        return response()->json([
            'success'   => true,
            'role'      => $role,
            'tabs'      => $this->getTabsForRole($role),
            'craftsmen' => $craftsmen,
            'buyers'    => $buyers,
            'types'     => [
                ['value' => 'repair', 'label' => 'Repair'],
                ['value' => 'sample', 'label' => 'Sample'],
            ],
        ]);
    }

    public function getBuyers(Request $request)
    {
        $user = $request->user();
        if ($this->isAdmin($user)) {
            $buyers = Buyer::select('id', 'bp_code', 'business_name', 'name')
                ->orderBy('bp_code')
                ->get();
            return response()->json(['success' => true, 'data' => $buyers]);
        }

        if ($this->isBuyerSide($user)) {
            $buyerId = $this->getBuyerId($user);
            $buyer = Buyer::where('id', $buyerId)->select('id', 'bp_code', 'business_name', 'name')->first();
            return response()->json(['success' => true, 'data' => $buyer ? [$buyer] : []]);
        }

        return response()->json(['success' => true, 'data' => []]);
    }

    public function getCraftsmen(Request $request)
    {
        $craftsmen = Craftman::select('id', 'craftman_code', 'business_name', 'name')
            ->orderBy('craftman_code')
            ->get();

        return response()->json(['success' => true, 'data' => $craftsmen]);
    }

    // =========================================================================
    // GENERATE PDF — PDF Export for Repairs
    // =========================================================================

    public function generatePdf(Request $request)
    {
        $user = $request->user();
        if (!$this->checkPermission($user, 'repair_view')) {
            return response()->json(['success' => false, 'message' => 'Forbidden – no repair permission'], 403);
        }

        $query = Repair::with(['buyer', 'craftsman']);
        $this->scopeQueryByRole($query, $user);
        $this->applyFilters($query, $request, $user);

        $repairs = $query->latest('date')->get();

        if ($repairs->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No repairs found matching criteria.'], 404);
        }

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'sans-serif');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('api.common.repairs.generate-pdf', compact('repairs'))->render());
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $filename = count($repairs) === 1
                ? "Repair_Report_" . ($repairs->first()->order_number ?: $repairs->first()->id) . ".pdf"
                : "Repairs_Report_" . now()->format('Ymd_His') . ".pdf";

            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Access-Control-Expose-Headers', 'Content-Disposition');
        } catch (\Exception $e) {
            Log::error('Repair PDF Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
        }
    }
}
