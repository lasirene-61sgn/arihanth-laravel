<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;

class CatalogueController extends Controller
{
    /**
     * List catalogue products.
     *
     * - SuperAdmin/Admin : all accepted designs (full catalogue).
     * - Buyer/KeyUser/User: their own bp_code scoped accepted designs.
     * - Craftsman        : their own accepted designs.
     *
     * Supports:
     *   - search, category_id, subcategory_id, type, size, weight_from, weight_to, hallmark, rodium, hook, stone, enamel
     *   - sort_by, sort_order
     *   - export (CSV download), print (all data, no pagination)
     *   - ids[] for selected-only export/print
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $sortBy = $request->get('sort_by', 'id');

        // 2. Prioritize 'sort' parameter, then 'sort_order', default to 'asc'
        $sortOrder = strtolower($request->get('sort') ?: $request->get('sort_order', 'asc'));

        // 3. Validation for direction
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        // 4. Validation for allowed columns
        $allowedSortColumns = [
            'id',
            'design_code',
            'product_code',
            'product_name',
            'type',
            'size',
            'weight_from',
            'weight_to',
            'hallmark',
            'rodium',
            'hook',
            'stone',
            'enamel',
            'bp_code',
            'created_at',
        ];

        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'id';
        }

        $query = Product::with(['category', 'subcategory', 'images'])
            ->whereNotNull('design_code')
            ->where('design_status', 'Accepted')
            ->notFromFrozenAccounts();
        // ── Role scope ──
        if ($user->role === 'super_admin' || $user->role === 'admin') {
            $query->whereNotNull('bp_code');
        } else {
            if ($user instanceof \App\Models\Craftman) {
                $query->where('bp_code', $user->craftman_code);
            } else {
                $query->where('bp_code', $user->bp_code);
                $query->where(function ($q) {
                    $q->whereNull('design_view_unlocked_until')
                        ->orWhere('design_view_unlocked_until', '>=', now());
                });
            }
        }

        // ── Search ──
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('design_code', 'LIKE', "%$search%")
                    ->orWhere('product_name', 'LIKE', "%$search%")
                    ->orWhere('bp_code', 'LIKE', "%$search%")
                    ->orWhere('product_code', 'LIKE', "%$search%");
            });
        }

        // ── Filters ──
        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->category_id);
        }

        if ($request->filled('category_name')) {
            $categoryName = $request->category_name;
            $query->whereHas('category', function ($q) use ($categoryName) {
                $q->where('name', $categoryName);
            });
        }

        if ($request->filled('subcategory_id')) {
            $query->where('product_subcategory_id', $request->subcategory_id);
        }

        if ($request->filled('subcategory_name') || $request->filled('subcategory')) {
            $subcategoryName = $request->subcategory_name ?: $request->subcategory;
            $query->whereHas('subcategory', function ($q) use ($subcategoryName) {
                $q->where('name', $subcategoryName);
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('size')) {
            $query->where('size', $request->size);
        }

        if ($request->filled('weight_from')) {
            $query->where('weight_from', '>=', $request->weight_from);
        }

        if ($request->filled('weight_to')) {
            $query->where('weight_to', '<=', $request->weight_to);
        }

        if ($request->filled('hallmark')) {
            $query->where('hallmark', $request->hallmark);
        }

        if ($request->filled('rodium')) {
            $query->where('rodium', $request->rodium);
        }

        if ($request->filled('hook')) {
            $query->where('hook', $request->hook);
        }

        if ($request->filled('stone')) {
            $query->where('stone', $request->stone);
        }

        if ($request->filled('enamel')) {
            $query->where('enamel', $request->enamel);
        }

        if ($request->filled('craftsman_code')) {
            $query->where('bp_code', $request->craftsman_code);
        }

        if ($request->filled('craftman_code')) {
            $query->where('bp_code', $request->craftman_code);
        }

        if ($request->filled('bp_code')) {
            $query->where('bp_code', $request->bp_code);
        }

        // ── Selected IDs (for print/export selected) ──
        if ($request->filled('ids')) {
            $ids = $request->ids;
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            if (is_array($ids)) {
                $query->whereIn('id', $ids);
            }
        }

        $query->orderBy($sortBy, $sortOrder);

        // ── Export (CSV download) ──
        if ($request->has('export')) {
            $products = $query->get();

            $exportData = $products->map(function ($product) {
                return [
                    'Design Code'   => $product->design_code,
                    'Product Code'  => $product->product_code,
                    'Product Name'  => $product->product_name,
                    'Category'      => $product->category->name ?? '',
                    'Subcategory'   => $product->subcategory->name ?? '',
                    'Type'          => $product->type,
                    'Size'          => $product->size,
                    'Weight From'   => $product->weight_from,
                    'Weight To'     => $product->weight_to,
                    'Hallmark'      => $product->hallmark,
                    'Rodium'        => $product->rodium,
                    'Hook'          => $product->hook,
                    'Stone'         => $product->stone,
                    'Enamel'        => $product->enamel,
                    'BP Code'       => $product->bp_code,
                    'Created At'    => $product->created_at ? $product->created_at->format('Y-m-d') : '',
                ];
            });

            $filename = 'catalogue_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            return response()->stream(function () use ($exportData) {
                $file = fopen('php://output', 'w');
                if ($exportData->isNotEmpty()) {
                    fputcsv($file, array_keys($exportData->first()));
                    foreach ($exportData as $row) {
                        fputcsv($file, $row);
                    }
                }
                fclose($file);
            }, 200, $headers);
        }

        // ── Print (full data, no pagination) ──
        if ($request->has('print')) {
            $products = $query->get();

            return response()->json([
                'success' => true,
                'data'    => $products,
            ]);
        }

        // ── Paginated list ──
        return response()->json([
            'success' => true,
            'data'    => $query->paginate($request->get('per_page', 15)),
        ]);
    }

    /**
     * Show a single catalogue item.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $query = Product::with(['category', 'subcategory', 'images'])
            ->whereNotNull('design_code')
            ->where('design_status', 'Accepted');

        if ($user->role === 'super_admin' || $user->role === 'admin') {
            $query->whereNotNull('bp_code');
        } else {
            if ($user instanceof \App\Models\Craftman) {
                $query->where('bp_code', $user->craftman_code);
            } else {
                $query->where('bp_code', $user->bp_code);
            }
        }

        $product = $query->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Catalogue item not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $product]);
    }

    /**
     * Generate PDF for selected catalogue items.
     */
    public function generatePdf(Request $request)
    {
        $user = $request->user();
        $query = Product::with(['category', 'subcategory', 'images'])
            ->whereNotNull('design_code')
            ->where('design_status', 'Accepted')
            ->notFromFrozenAccounts();

        // ── Role scope (mirrors index logic) ──
        if ($user->role === 'super_admin' || $user->role === 'admin') {
            $query->whereNotNull('bp_code');
        } else {
            if ($user instanceof \App\Models\Craftman) {
                $query->where('bp_code', $user->craftman_code);
            } else {
                $query->where('bp_code', $user->bp_code);
                $query->where(function ($q) {
                    $q->whereNull('design_view_unlocked_until')
                        ->orWhere('design_view_unlocked_until', '>=', now());
                });
            }
        }

        // ── Selected IDs ──
        if ($request->filled('ids')) {
            $ids = $request->ids;
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            if (is_array($ids)) {
                $query->whereIn('id', $ids);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'No catalogue IDs provided'], 400);
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No catalogue items found'], 404);
        }

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'sans-serif');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('api.common.catalogue.generate-pdf', compact('products'))->render());
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = count($products) === 1
                ? "Catalogue_" . $products->first()->design_code . ".pdf"
                : "Official_Catalogue_" . now()->format('Ymd_His') . ".pdf";

            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Access-Control-Expose-Headers', 'Content-Disposition');
        } catch (\Exception $e) {
            Log::error('Catalogue PDF Generation Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate PDF. ' . $e->getMessage()], 500);
        }
    }
}
