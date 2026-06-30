<?php

namespace App\Imports;

use App\Models\WorkOrder;
use App\Models\Product;
use App\Models\Craftman;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Services\ImageWatermarkService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;

class WorkOrderImport implements ToCollection, WithHeadingRow
{
    protected $created_by;
    protected $creator_type;
    protected $creator_user_code;
    public $importedCount = 0;

    public function __construct($user, $type = 'admin')
    {
        $this->created_by = $user->id;
        $this->creator_type = $type;
        $this->creator_user_code = $user->user_code ?? null;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Validate the row data
            $validator = Validator::make($row->toArray(), [
                'customer_name' => 'required|string|max:255',
                'product_name' => 'required|string|max:255',
                'quantity' => 'required|integer|min:1',
                'due_date' => 'required|date',
                'product_category' => 'nullable|string|max:255',
                'subcategory' => 'nullable|string|max:255',
                'allocated_craftsman_bp_code' => 'nullable|exists:craftmen,craftman_code',
                'bp_code' => 'nullable|string|max:255',
                'reference_no' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:50',
                'open_close' => 'nullable|string|max:20',
                'weight_from' => 'nullable|string|max:50',
                'weight_to' => 'nullable|string|max:50',
                'hallmark' => 'nullable|string|max:255',
                'rodium' => 'nullable|string|max:255',
                'hook' => 'nullable|string|max:255',
                'size' => 'nullable|string|max:50',
                'stone' => 'nullable|string|max:255',
                'enamel' => 'nullable|string|max:50',
                'length' => 'nullable|string|max:50',
                'product_code' => 'nullable|string|max:255',
                'relabel_code' => 'nullable|string|max:255',
                'craftsman_due_date' => 'nullable|date',
                'narration_craftsman' => 'nullable|string',
                'narration_admin' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                // Log validation errors or handle as needed
                continue;
            }

            // Check if a product exists with the given product code
            $product = null;
            if (!empty($row['product_code'])) {
                $product = Product::where('product_code', $row['product_code'])
                    ->orWhere('design_code', $row['product_code'])
                    ->first();
            }

            // Generate a unique product code if not provided
            $finalProductCode = $row['product_code'] ?? null;
            if (empty($finalProductCode)) {
                $finalProductCode = $this->generateUniqueProductCode();
            }

            // Get category and subcategory IDs if provided
            $categoryId = null;
            $subcategoryId = null;
            
            if (!empty($row['product_category'])) {
                $category = ProductCategory::firstOrCreate(
                    ['name' => $row['product_category']],
                    ['name' => $row['product_category']]
                );
                $categoryId = $category->id;
            }
            
            if (!empty($row['subcategory'])) {
                $subcategory = ProductSubcategory::firstOrCreate(
                    ['name' => $row['subcategory'], 'category_id' => $categoryId],
                    ['name' => $row['subcategory'], 'category_id' => $categoryId]
                );
                $subcategoryId = $subcategory->id;
            }

            // Automatic BP Matching: If bp_code is empty, try to match customer_name with Buyer business_name
            $bpCode = $row['bp_code'] ?? null;
            if (empty($bpCode) && !empty($row['customer_name'])) {
                $buyer = \App\Models\Buyer::where('business_name', 'LIKE', trim($row['customer_name']))->first();
                if ($buyer) {
                    $bpCode = $buyer->bp_code;
                }
            }

            // Duplicate Check
            // 1. Check by Reference No (if provided)
            if (!empty($row['reference_no']) && WorkOrder::where('reference_no', $row['reference_no'])->exists()) {
                continue;
            }

            // 2. Check by Key Fields (if Reference No is missing or not unique enough for your flow)
            // matching: customer, product, quantity, due_date, status=new
            $existingOrder = WorkOrder::where('customer_name', $row['customer_name'])
                ->where('product_name', $row['product_name'])
                ->where('quantity', $row['quantity'])
                ->where('due_date', $row['due_date']) // Ensure date format matches
                ->where('status', 'new')
                ->first();

            if ($existingOrder) {
                continue;
            }

            // Create work order
            $workOrder = WorkOrder::create([
                'work_order_number' => WorkOrder::generateWorkOrderNumber(),
                'product_image' => null, // No image from Excel
                'bp_code' => $bpCode,
                'customer_name' => $row['customer_name'],
                'reference_no' => $row['reference_no'] ?? null,
                'due_date' => $row['due_date'],
                'product_category' => $row['product_category'] ?? null,
                'product_category_id' => $categoryId,
                'subcategory' => $row['subcategory'] ?? null,
                'subcategory_id' => $subcategoryId,
                'quantity' => $row['quantity'],
                'type' => $row['type'] ?? null,
                'open_close' => $row['open_close'] ?? null,
                'weight_from' => $row['weight_from'] ?? null,
                'weight_to' => $row['weight_to'] ?? null,
                'narration_craftsman' => $row['narration_craftsman'] ?? null,
                'narration_admin' => $row['narration_admin'] ?? null,
                'hallmark' => $row['hallmark'] ?? null,
                'rodium' => $row['rodium'] ?? null,
                'hook' => $row['hook'] ?? null,
                'size' => $row['size'] ?? null,
                'stone' => $row['stone'] ?? null,
                'enamel' => $row['enamel'] ?? null,
                'length' => $row['length'] ?? null,
                'product_code' => $finalProductCode,
                'relabel_code' => $row['relabel_code'] ?? null,
                'product_name' => $row['product_name'],
                'craftsman_due_date' => $row['craftsman_due_date'] ?? null,
                'allocated_craftsman_bp_code' => $row['allocated_craftsman_bp_code'] ?? null,
                'status' => !empty($row['allocated_craftsman_bp_code']) ? 'allocated' : 'new',
                'craftsman_status' => !empty($row['allocated_craftsman_bp_code']) ? 'allocated' : null,
                'created_by' => $this->created_by,
                'creator_type' => $this->creator_type,
                'creator_user_code' => $this->creator_user_code,
            ]);

            $this->importedCount++;

            // If product exists, copy product details to work order
            if ($product) {
                $updateData = [
                    'product_category' => $product->category->name ?? $workOrder->product_category,
                    'product_category_id' => $product->product_category_id ?? $workOrder->product_category_id,
                    'subcategory' => $product->subcategory->name ?? $workOrder->subcategory,
                    'subcategory_id' => $product->product_subcategory_id ?? $workOrder->subcategory_id,
                    'type' => $product->type ?? $workOrder->type,
                    'open_close' => $product->open_close ?? $workOrder->open_close,
                    'weight_from' => $product->weight_from ?? $workOrder->weight_from,
                    'weight_to' => $product->weight_to ?? $workOrder->weight_to,
                    'hallmark' => $product->hallmark ?? $workOrder->hallmark,
                    'rodium' => $product->rodium ?? $workOrder->rodium,
                    'hook' => $product->hook ?? $workOrder->hook,
                    'size' => $product->size ?? $workOrder->size,
                    'stone' => $product->stone ?? $workOrder->stone,
                    'enamel' => $product->enamel ?? $workOrder->enamel,
                    'length' => $product->length ?? $workOrder->length,
                    'relabel_code' => $product->relabel_code ?? $workOrder->relabel_code,
                ];

                // Copy image if the product has one
                if ($product->images->count() > 0) {
                    $existingImage = $product->images->first();
                    $sourceImagePath = storage_path('app/public/' . $existingImage->path);
                    
                    if (file_exists($sourceImagePath)) {
                        $imageName = time() . '_imported_from_product_' . basename($existingImage->path);
                        $destinationPath = public_path('images/work-orders/' . $imageName);
                        
                        if (!file_exists(dirname($destinationPath))) {
                            mkdir(dirname($destinationPath), 0755, true);
                        }
                        
                        copy($sourceImagePath, $destinationPath);
                        $productImage = 'images/work-orders/' . $imageName;
                        
                        // Apply watermark
                        $watermarkService = new ImageWatermarkService();
                        $watermarkService->addWatermark($productImage, true);
                        
                        $updateData['product_image'] = $productImage;
                        
                        // Create gallery record as well
                        \App\Models\WorkOrderImage::create([
                            'work_order_id' => $workOrder->id,
                            'image_path' => $productImage,
                        ]);
                    }
                }

                $workOrder->update($updateData);
            }
        }
    }

    /**
     * Generates a unique product code starting with 'OO' followed by numbers.
     */
    private function generateUniqueProductCode()
    {
        // Find the latest work order that has a code starting with 'OO'
        $latestOrder = WorkOrder::where('product_code', 'LIKE', 'OO%')
            ->orderBy('product_code', 'desc')
            ->first();

        if (!$latestOrder) {
            return 'OO001';
        }

        // Extract the numeric part and increment it
        $numericPart = preg_replace('/[^0-9]/', '', $latestOrder->product_code);
        $nextNumber = intval($numericPart) + 1;

        // Pad with zeros to keep the format consistent (OO001)
        return 'OO' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}