<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Craftman;
use Dompdf\Dompdf;
use Dompdf\Options;

class CraftsmanController extends Controller
{
    /**
     * Get scoping for queries based on user role
     */
    private function getScope($user)
    {
        $role = $user->role ?? null;
        if ($role === 'super_admin' || $role === 'admin') {
            return 'global';
        }

        if ($user instanceof Craftman) {
            return 'self';
        }

        return 'none';
    }

    /**
     * Display a listing of craftsmen (Admin/SuperAdmin)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if ($this->getScope($user) !== 'global') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $query = Craftman::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('craftman_code', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('kyc_status')) {
             if ($request->kyc_status == 'pending') {
                $query->where(function($q) {
                    $q->where('kyc_status', 'pending')->orWhereNull('kyc_status');
                });
            } else {
                $query->where('kyc_status', $request->kyc_status);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate($request->get('per_page', 10))
        ]);
    }

    /**
     * Get Craftsman profile
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();
        if (!($user instanceof Craftman)) {
            return response()->json(['message' => 'Unauthorized or not a Craftsman'], 401);
        }

        $user->load(['aadharDetails', 'panDetails', 'bankDetails', 'workers', 'workOrders']);
        return response()->json(['craftsman' => $user]);
    }

    /**
     * Display specific Craftsman (Admin or Self)
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $scope = $this->getScope($user);

        if ($scope === 'none') return response()->json(['message' => 'Forbidden'], 403);

        $query = Craftman::with(['aadharDetails', 'panDetails', 'bankDetails', 'workers', 'workOrders']);
        if ($scope === 'self' && $user->id != $id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $craftsman = $query->find($id);
        if (!$craftsman) return response()->json(['message' => 'Craftsman not found'], 404);

        return response()->json($craftsman);
    }

    /**
     * Update Craftsman profile (Self)
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        if (!($user instanceof Craftman)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return $this->update($request, $user->id);
    }

    /**
     * Update Craftsman profile/record
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $scope = $this->getScope($user);

        if ($scope === 'none') return response()->json(['message' => 'Forbidden'], 403);
        if ($scope === 'self' && $user->id != $id) return response()->json(['message' => 'Forbidden'], 403);

        $craftsman = Craftman::find($id);
        if (!$craftsman) return response()->json(['message' => 'Craftsman not found'], 404);

        // KYC Edit Restriction for Self Update
        if ($scope === 'self' && $craftsman->kyc_status === 'approved') {
            return response()->json(['message' => 'Profile is approved and cannot be edited.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'dear' => 'sometimes|required|string|unique:craftmen,dear,' . $craftsman->id,
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:craftmen,email,' . $craftsman->id,
            'craftman_code' => 'sometimes|string|unique:craftmen,craftman_code,' . $craftsman->id,
            'gst_no' => 'nullable|string|max:20|unique:craftmen,gst_no,' . $craftsman->id,
            'bis_no' => 'nullable|string|max:255',
            'msme_no' => 'nullable|string|max:255',
            'cin_no' => 'nullable|string|max:255',
            'tan_no' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'bis_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'gst_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'msme_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'pan_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'tan_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'cin_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'brand_logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $data = $request->except([
            'image', 'bis_attachment', 'gst_attachment', 'msme_attachment', 
            'pan_attachment', 'tan_attachment', 'cin_attachment', 'brand_logo',
            'aadhar_details'
        ]);

        // Admins can change kyc_status, users can't
        if ($scope !== 'global') {
            unset($data['kyc_status']);
        }

        // Handle File Uploads
        $files = ['image', 'bis_attachment', 'gst_attachment', 'msme_attachment', 'pan_attachment', 'tan_attachment', 'cin_attachment', 'brand_logo'];
        foreach ($files as $file) {
            if ($request->hasFile($file)) {
                if ($craftsman->$file) Storage::disk('public')->delete($craftsman->$file);
                $data[$file] = $request->file($file)->store('craftsmen/' . $file, 'public');
            }
        }

        $craftsman->update($data);

        // Related Details (Aadhar/PAN/Bank/Workers)
        if ($request->has('aadhar_details')) {
             $aadharDetails = is_string($request->aadhar_details) ? json_decode($request->aadhar_details, true) : $request->aadhar_details;
             if (is_array($aadharDetails)) {
                 $craftsman->aadharDetails()->delete();
                 foreach ($aadharDetails as $index => $detail) {
                     $imagePath = $detail['aadhar_image'] ?? null;
                     if ($request->hasFile("aadhar_image_file.$index")) {
                         $imagePath = $request->file("aadhar_image_file.$index")->store('craftsmen/aadhar', 'public');
                     }
                     $craftsman->aadharDetails()->create([
                         'aadhar_name' => $detail['aadhar_name'] ?? $craftsman->name,
                         'aadhar_number' => $detail['aadhar_number'] ?? null,
                         'aadhar_image' => $imagePath,
                     ]);
                 }
             }
        }

        return response()->json(['message' => 'Craftsman updated successfully', 'craftsman' => $craftsman->load(['aadharDetails', 'panDetails', 'bankDetails', 'workers', 'workOrders'])]);
    }

    /**
     * Delete Craftsman
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if ($this->getScope($user) !== 'global') return response()->json(['message' => 'Forbidden'], 403);

        $craftsman = Craftman::find($id);
        if (!$craftsman) return response()->json(['message' => 'Craftsman not found'], 404);

        $craftsman->delete();
        return response()->json(['message' => 'Craftsman deleted successfully']);
    }

    /**
     * Generate PDF for Craftsmen
     */
    public function generatePdf(Request $request)
    {
        $user = $request->user();
        if ($this->getScope($user) !== 'global' && !($user instanceof Craftman)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $query = Craftman::query();
        if ($user instanceof Craftman) {
             $query->where('id', $user->id);
        } elseif ($request->filled('ids')) {
             $ids = is_string($request->ids) ? explode(',', $request->ids) : $request->ids;
             $query->whereIn('id', $ids);
        } else {
             return response()->json(['message' => 'No IDs provided'], 400);
        }

        $craftsmen = $query->get();
        if ($craftsmen->isEmpty()) return response()->json(['message' => 'No craftsmen found'], 404);

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'sans-serif');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('api.superadmin.craftsmen.generate-pdf', compact('craftsmen'))->render());
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $filename = count($craftsmen) === 1 ? "Craftsman_" . $craftsmen->first()->craftman_code . ".pdf" : "Craftsmen_Report_" . now()->format('Ymd_His') . ".pdf";

            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            Log::error('Craftsman PDF Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to generate PDF'], 500);
        }
    }
}
