<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\ProcessOwner;
use App\Models\KeyUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FreezeAccountController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $frozenBuyers = Buyer::where('is_frozen', true);
        $frozenCraftsmen = Craftman::where('is_frozen', true);
        $frozenAdmins = ProcessOwner::where('is_frozen', true);
        $frozenKeyUsers = KeyUser::where('is_frozen', true);
        $frozenUsers = User::where('is_frozen', true);

        $allBuyers = Buyer::query();
        $allCraftsmen = Craftman::query();
        $allAdmins = ProcessOwner::query();
        $allKeyUsers = KeyUser::query();
        $allUsers = User::query();

        if ($search) {
            $frozenBuyers->where(function($q) use ($search) {
                $q->where('bp_code', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
            $allBuyers->where(function($q) use ($search) {
                $q->where('bp_code', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });

            $frozenCraftsmen->where(function($q) use ($search) {
                $q->where('craftman_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
            $allCraftsmen->where(function($q) use ($search) {
                $q->where('craftman_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });

            $frozenAdmins->where(function($q) use ($search) {
                $q->where('user_code', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });
            $allAdmins->where(function($q) use ($search) {
                $q->where('user_code', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });

            $frozenKeyUsers->where(function($q) use ($search) {
                $q->where('user_code', 'like', "%{$search}%")
                  ->orWhere('bp_code', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });
            $allKeyUsers->where(function($q) use ($search) {
                $q->where('user_code', 'like', "%{$search}%")
                  ->orWhere('bp_code', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });

            $frozenUsers->where(function($q) use ($search) {
                $q->where('user_code', 'like', "%{$search}%")
                  ->orWhere('bp_code', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });
            $allUsers->where(function($q) use ($search) {
                $q->where('user_code', 'like', "%{$search}%")
                  ->orWhere('bp_code', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        $frozenBuyers = $frozenBuyers->get();
        $frozenCraftsmen = $frozenCraftsmen->get();
        $frozenAdmins = $frozenAdmins->get();
        $frozenKeyUsers = $frozenKeyUsers->get();
        $frozenUsers = $frozenUsers->get();

        $allBuyers = $allBuyers->get();
        $allCraftsmen = $allCraftsmen->get();
        $allAdmins = $allAdmins->get();
        $allKeyUsers = $allKeyUsers->get();
        $allUsers = $allUsers->get();

        return view('admin.freeze-account.index', compact(
            'frozenBuyers', 'frozenCraftsmen', 'frozenAdmins', 'frozenKeyUsers', 'frozenUsers',
            'allBuyers', 'allCraftsmen', 'allAdmins', 'allKeyUsers', 'allUsers', 'search'
        ));
    }

    public function toggleFreeze(Request $request)
    {
        $request->validate([
            'model_type' => 'required|in:buyer,craftsman,admin,key_user,user',
            'model_id' => 'required|integer',
            'action' => 'required|in:freeze,unfreeze'
        ]);

        $modelType = $request->model_type;
        $modelId = $request->model_id;
        $action = $request->action;

        try {
            DB::beginTransaction();

            $model = null;
            
            switch ($modelType) {
                case 'buyer':
                    $model = Buyer::findOrFail($modelId);
                    break;
                case 'craftsman':
                    $model = Craftman::findOrFail($modelId);
                    break;
                case 'admin':
                    $model = ProcessOwner::findOrFail($modelId);
                    break;
                case 'key_user':
                    $model = KeyUser::findOrFail($modelId);
                    break;
                case 'user':
                    $model = User::findOrFail($modelId);
                    break;
            }

            if ($model) {
                $model->is_frozen = ($action === 'freeze');
                $model->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => ucfirst($modelType) . ' account ' . $action . 'd successfully!',
                'is_frozen' => $model->is_frozen
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}