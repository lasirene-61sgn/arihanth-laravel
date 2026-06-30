<?php

namespace App\Imports;

use App\Models\WorkOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\Buyer;
use App\Services\ImageWatermarkService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class OrderListImport implements ToModel, WithHeadingRow, WithValidation, WithEvents
{
    use RemembersRowNumber;

    protected $sheetDrawings = [];
    protected $sheetHyperlinks = [];
    protected $extractPath = null;
    public $importedCount = 0;
    protected $created_by;
    protected $creator_type;
    protected $creator_user_code;

    public function __construct($extractPath = null, $user = null, $type = 'admin')
    {
        $this->extractPath = $extractPath;
        if ($user) {
            $this->created_by = $user->id;
            $this->creator_user_code = $user->user_code ?? ($user->bp_code ?? null);
        } else {
            $this->created_by = Auth::id();
        }
        $this->creator_type = $type;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function(BeforeSheet $event) {
                $logPath = public_path('debug_images.txt');
                file_put_contents($logPath, "Event triggered at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
                
                $sheet = $event->sheet->getDelegate();
                
                // 1. Drawings (Embedded Images)
                $drawings = $sheet->getDrawingCollection();
                file_put_contents($logPath, "Drawings count: " . count($drawings) . "\n", FILE_APPEND);
                
                foreach ($drawings as $drawing) {
                    // Check if it's an image
                    if ($drawing instanceof Drawing || $drawing instanceof MemoryDrawing) {
                        // Get coordinates (e.g., 'H2')
                        $coordinates = $drawing->getCoordinates();
                        file_put_contents($logPath, "Found drawing at: " . $coordinates . "\n", FILE_APPEND);
                        
                        // Extract row number (e.g., 2)
                        $row = (int) preg_replace('/[A-Z]+/', '', $coordinates);
                        
                        // Generate unique filename
                        $filename = 'imported_' . Str::random(10) . '.jpg';
                        $relativePath = 'uploads/work-orders/imported/' . $filename;
                        $absolutePath = public_path('uploads/work-orders/imported');
                        
                        // Ensure directory exists
                        if (!file_exists($absolutePath)) {
                            file_put_contents($logPath, "Creating dir: " . $absolutePath . "\n", FILE_APPEND);
                            mkdir($absolutePath, 0755, true);
                        }
                        
                        $fullSavePath = $absolutePath . '/' . $filename;
                        
                        // Save image
                        if ($drawing instanceof MemoryDrawing) {
                            ob_start();
                            call_user_func(
                                $drawing->getRenderingFunction(),
                                $drawing->getImageResource()
                            );
                            $imageContents = ob_get_contents();
                            ob_end_clean();
                            
                            file_put_contents($fullSavePath, $imageContents);
                            file_put_contents($logPath, "Saved MemoryDrawing to " . $fullSavePath . "\n", FILE_APPEND);
                        } else {
                            // Standard Drawing (File)
                            $zipReader = $drawing->getPath();
                            file_put_contents($logPath, "ZipReader path: " . $zipReader . "\n", FILE_APPEND);
                            
                            if (file_exists($zipReader)) {
                                copy($zipReader, $fullSavePath);
                                file_put_contents($logPath, "Copied file to " . $fullSavePath . "\n", FILE_APPEND);
                            } else {
                                file_put_contents($logPath, "ZipReader file not found\n", FILE_APPEND);
                            }
                        }
                        
                        $this->sheetDrawings[$row] = $relativePath; // Store public path (no 'storage/' prefix needed)
                    }
                }

                // 2. Hyperlinks (View_Image links)
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $headerRow = 1; // Assuming header is row 1
                
                // Find 'Image' column index
                $imageColumnIndex = null;
                for ($col = 1; $col <= \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); $col++) {
                    $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $val = $sheet->getCell($colString . $headerRow)->getValue();
                    if (in_array(strtolower(trim($val)), ['image', 'img', 'photo', 'picture', 'view image', 'product image'])) {
                        $imageColumnIndex = $colString;
                        break;
                    }
                }
                
                if ($imageColumnIndex) {
                    file_put_contents($logPath, "Image column found at: " . $imageColumnIndex . "\n", FILE_APPEND);
                    
                    for ($row = 2; $row <= $highestRow; $row++) {
                        $cell = $sheet->getCell($imageColumnIndex . $row);
                        if ($cell->hasHyperlink()) {
                            $url = $cell->getHyperlink()->getUrl();
                            file_put_contents($logPath, "Row $row has hyperlink: $url\n", FILE_APPEND);
                            
                            if (filter_var($url, FILTER_VALIDATE_URL)) {
                                try {
                                    $contents = @file_get_contents($url);
                                    if ($contents) {
                                        $filename = 'imported_link_' . Str::random(10) . '.jpg';
                                        $absolutePath = public_path('uploads/work-orders/imported');
                                         if (!file_exists($absolutePath)) mkdir($absolutePath, 0755, true);
                                        file_put_contents($absolutePath . '/' . $filename, $contents);
                                        $this->sheetHyperlinks[$row] = 'uploads/work-orders/imported/' . $filename;
                                        file_put_contents($logPath, "Downloaded hyperlink to file\n", FILE_APPEND);
                                    }
                                } catch (\Exception $e) {
                                    file_put_contents($logPath, "Failed download: " . $e->getMessage() . "\n", FILE_APPEND);
                                }
                            } else {
                                file_put_contents($logPath, "Skipped non-URL hyperlink: $url\n", FILE_APPEND);
                            }
                        }
                    }
                } else {
                     file_put_contents($logPath, "Image column not found via headers.\n", FILE_APPEND);
                }
            },
        ];
    }
    protected $errors = [];

    public function model(array $row)
    {
        // Helper to get value from multiple possible keys
        $getValue = function($keys) use ($row) {
            foreach ((array)$keys as $key) {
                if (isset($row[$key])) return $row[$key];
            }
            return null;
        };

        // Define Aliases
        $orderNo = $getValue(['order_no', 'order_number', 'ref_no', 'reference_no', 'po_number']);
        
        // UNIQUE CHECK: If duplicate Reference No, skip this row
        if (!$orderNo || WorkOrder::where('reference_no', $orderNo)->exists()) {
            return null;
        }

        $rawDate = $getValue(['due_date', 'date', 'delivery_date', 'completion_date', 'order_date', 'orderdate']); 
        $expectedDate = $getValue(['expected_date', 'craftsman_due_date', 'est_date', 'expecteddate']); 
        $productCategoryRaw = $getValue(['product', 'product_name', 'item', 'item_name', 'category', 'product_category']); 
        $subcategoryRaw = $getValue(['form', 'shape', 'structure', 'sub_category', 'subcategory', 'forum']); 
        $designCode = $getValue(['design', 'design_code', 'model', 'style_no', 'product_code']);
        $weight = ((float)$getValue(['weight', 'wt', 'gross_weight'])); // Cast to float
        $size = $getValue(['size', 'dimension']);
        $quantity = $getValue(['quantity', 'qty', 'pcs', 'count']);
        $image = $getValue(['image', 'img', 'photo', 'picture']);
        
        // NEW: Specific lookups for Customer and Product Name to avoid using Design Code
        $customerName = $getValue(['customer', 'customer_name', 'client', 'party_name', 'party', 'assign_by', 'assigned_by', 'assignby']);
        $productName = $getValue(['product_name', 'item_name', 'description', 'particulars']);

        // 1. Robust Date Parsing
        $formattedDate = null;
        if ($rawDate) {
            if (is_numeric($rawDate)) {
                 $formattedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate)->format('Y-m-d');
            } else {
                $dateFormats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y', 'Y/m/d', 'd.m.Y', 'Y.m.d', 'd M Y', 'j/n/Y', 'j-n-Y'];
                foreach ($dateFormats as $format) {
                    $parsedDate = date_parse_from_format($format, $rawDate);
                    if ($parsedDate['error_count'] === 0) {
                         $formattedDate = date('Y-m-d', mktime(0, 0, 0, $parsedDate['month'], $parsedDate['day'], $parsedDate['year']));
                         break;
                    }
                }
            }
        }
        if (!$formattedDate) { $formattedDate = $rawDate; }

        $formattedExpectedDate = null;
        if ($expectedDate) {
            if (is_numeric($expectedDate)) {
                 $formattedExpectedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($expectedDate)->format('Y-m-d');
            } else {
                $dateFormats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y', 'Y/m/d', 'd.m.Y', 'Y.m.d', 'd M Y', 'j/n/Y', 'j-n-Y'];
                foreach ($dateFormats as $format) {
                    $parsedDate = date_parse_from_format($format, $expectedDate);
                    if ($parsedDate['error_count'] === 0) {
                         $formattedExpectedDate = date('Y-m-d', mktime(0, 0, 0, $parsedDate['month'], $parsedDate['day'], $parsedDate['year']));
                         break;
                    }
                }
            }
        }
        if (!$formattedExpectedDate) { $formattedExpectedDate = $formattedDate; }

        if ($designCode) {
            $designCode = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', (string)$designCode);
            $designCode = trim($designCode);
        }
        
        if (!$designCode) { return null; }

        // Category Lookup & Creation
        $categoryId = null;
        $categoryName = $productCategoryRaw;
        
        if ($productCategoryRaw) {
            $catName = trim($productCategoryRaw);
            // Try to find existing first (case-insensitive for MySQL usually, but be specific if needed)
            $cat = ProductCategory::where('name', 'LIKE', $catName)->first();
            
            if (!$cat) {
                // Not found? Create it! (As per user requirement to ensure it 'shows up')
                $cat = ProductCategory::create([
                    'name' => $catName,
                    // Default booleans to false
                    'has_hook' => false,
                    'has_enamel' => false,
                    'has_rodium' => false,
                    'has_open_close' => false,
                    'has_stone' => false
                ]);
            }
            
            if ($cat) {
                $categoryId = $cat->id;
                $categoryName = $cat->name; 
            }
        }

        // Subcategory Lookup & Creation
        $subcategoryId = null;
        $subcategoryName = $subcategoryRaw;
        
        if ($subcategoryRaw && $categoryId) { // Only create subcategory if we have a parent category
            $subName = trim($subcategoryRaw);
            $sub = ProductSubcategory::where('name', 'LIKE', $subName)
                                     ->where('product_category_id', $categoryId)
                                     ->first();
                                     
            if (!$sub) {
                // Check if it exists globally? No, scope to category.
                // Create it!
                $sub = ProductSubcategory::create([
                    'product_category_id' => $categoryId,
                    'name' => $subName
                ]);
            }
            
            if ($sub) {
                $subcategoryId = $sub->id;
                $subcategoryName = $sub->name;
            }
        } elseif ($subcategoryRaw && !$categoryId) {
            // If we have a subcategory string but no category ID (shouldn't happen with Create logic above unless empty name)
            // We can't create a subcategory without a parent.
            // Just store the name string.
        }

        // Image Handling
        $importedImage = null;
        // 1. ZIP Extraction Logic
        if ($this->extractPath && $designCode) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->extractPath));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $filename = $file->getFilename();
                    $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
                    $cleanFilename = trim((string)$nameWithoutExt);

                    $match = (strcasecmp($cleanFilename, $designCode) === 0);
                    if (!$match && strpos($designCode, '_') !== false) {
                        $parts = explode('_', $designCode);
                        if (strcasecmp((string)$nameWithoutExt, trim(end($parts))) === 0) $match = true;
                    }
                    if (!$match && strpos($designCode, '-') !== false) {
                         $parts = explode('-', $designCode);
                         if (strcasecmp($cleanFilename, trim(end($parts))) === 0) $match = true;
                    }
                    if (!$match && strpos($cleanFilename, '_') !== false) {
                        $fParts = explode('_', $cleanFilename);
                        if (strcasecmp(trim(end($fParts)), $designCode) === 0) $match = true;
                    }
                     if (!$match && strpos($cleanFilename, '-') !== false) {
                        $fParts = explode('-', $cleanFilename);
                        if (strcasecmp(trim(end($fParts)), $designCode) === 0) $match = true;
                    }
                    
                    if ($match) {
                        $ext = strtolower($file->getExtension());
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'pdf'])) {
                            $newFilename = 'matched_' . $designCode . '_' . time() . '.' . $ext;
                            $destPath = public_path('uploads/work-orders/imported');
                            if (!file_exists($destPath)) mkdir($destPath, 0755, true);
                            copy($file->getPathname(), $destPath . '/' . $newFilename);
                            $importedImage = 'uploads/work-orders/imported/' . $newFilename;
                            break; 
                        }
                    }
                }
            }
        }
        
        if (!$importedImage) {
            $importedImage = $this->sheetDrawings[$this->getRowNumber()] ?? $this->sheetHyperlinks[$this->getRowNumber()] ?? null;
        }

        if (!$importedImage && $image) {
             $cleanImage = trim($image);
             if (preg_match('/\.(jpg|jpeg|png|gif|pdf|webp)$/i', $cleanImage) || strpos($cleanImage, '/') !== false) {
                 $importedImage = $cleanImage;
             }
        }

        if ($designCode) {
            // Find Existing or Prepare New
            $product = Product::firstOrNew(['design_code' => $designCode]);
            
            // If NEW, set defaults that shouldn't be overwritten if they already exist
            if (!$product->exists) {
                $product->product_code = $designCode;
                $product->product_name = $productName ?: $designCode; // Default to Design Code if no name
                $product->design_status = 'Accepted';
                $product->created_by = $this->created_by;
            }

            // Sync/Update fields if provided in Excel
            if ($productName) $product->product_name = $productName;
            
            if ($categoryId) $product->product_category_id = $categoryId;
            if ($subcategoryId) $product->product_subcategory_id = $subcategoryId;
            if ($weight) {
                $product->weight_from = $weight;
                $product->weight_to = $weight;
            }
            if ($size) $product->size = $size;
            if ($importedImage) $product->product_image = $importedImage;

            $product->save();
        }

        // Automatic BP Matching: If customerName matches a Buyer's business_name, use that bp_code
        $bpCode = null;
        if ($customerName) {
            $buyer = Buyer::where('business_name', 'LIKE', trim($customerName))->first();
            if ($buyer) {
                $bpCode = $buyer->bp_code;
            }
        }

        // Create work order
        $workOrder = new WorkOrder([
            'work_order_number' => WorkOrder::generateWorkOrderNumber(),
            // 'product_id' => $product->id, // Removed: Column does not exist
            'bp_code' => $bpCode,
            'customer_name' => $customerName ?? 'N/A', // Uses explicit Customer Name or N/A
            'reference_no' => $orderNo, 
            'due_date' => $formattedDate,
            'product_category' => $categoryName, // Stores Name
            'product_category_id' => $categoryId, // Stores ID if found (Needs DB column support, check Model fillable)
            'subcategory' => $subcategoryName, // Stores Name
            'subcategory_id' => $subcategoryId, // Stores ID if found
            'quantity' => $quantity ?? 1,
            'type' => 'Piece',
            'open_close' => null,
            'weight_from' => $weight ?? 0,
            'weight_to' => $weight ?? 0,
            'hallmark' => null,
            'rodium' => null,
            'hook' => null,
            'size' => $size ?? null,
            'stone' => null,
            'enamel' => null,
            'length' => null,
            'product_code' => $designCode,
            'relabel_code' => null,
            'product_name' => $productName ?? 'N/A', // Uses explicit Product Name or N/A
            'craftsman_due_date' => $formattedExpectedDate,
            'narration_craftsman' => '',
            'narration_admin' => '',
            'status' => 'new',
            'product_image' => $importedImage,
            'created_by' => $this->created_by,
            'creator_type' => $this->creator_type,
            'creator_user_code' => $this->creator_user_code,
        ]);

        $this->importedCount++;
        return $workOrder;
    }

    public function rules(): array
    {
        return []; 
        // We removed strict rules here because we are handling validation/aliases manually in the model/controller 
        // and we want to allow partial rows to be skipped gracefully rather than halting everything.
        // Or we could implement dynamic rules but Maatwebsite validation is rigid on keys.
    }
    
    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [];
    }
}