<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request) {
        $this->request = $request;
    }

    public function collection() {
        $query = Product::with(['category', 'subcategory', 'creator']);
        
        // --- SELECTION FILTER ---
        if ($this->request->filled('selected_ids')) {
            $ids = explode(',', $this->request->selected_ids);
            return $query->whereIn('id', $ids)->get();
        }

        // Apply the same search/filter logic here to export only what is filtered
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('product_name', 'like', "%$search%")
                  ->orWhere('product_code', 'like', "%$search%")
                  ->orWhere('bp_code', 'like', "%$search%")
                  ->orWhere('design_code', 'like', "%$search%");
            });
        }

        // Specific filters
        if ($this->request->filled('bp_code')) {
            $query->where('bp_code', $this->request->bp_code);
        }

        if ($this->request->filled('product_name')) {
            $query->where('product_name', 'like', '%' . $this->request->product_name . '%');
        }

        if ($this->request->filled('product_code')) {
            $query->where('product_code', 'like', '%' . $this->request->product_code . '%');
        }
        
        // Category filter (supports both names)
        if ($this->request->filled('category_filter')) {
            $query->where('product_category_id', $this->request->category_filter);
        } elseif ($this->request->filled('category_id')) {
            $query->where('product_category_id', $this->request->category_id);
        }
        
        // Tab filtering
        if ($this->request->filled('tab')) {
            $tab = $this->request->get('tab');
            switch($tab) {
                case 'accepted':
                    $query->where('design_status', 'Accepted');
                    break;
                case 'rejected':
                    $query->where('design_status', 'Rejected');
                    break;
                case 'pending':
                    $query->whereNotIn('design_status', ['Accepted', 'Rejected']);
                    break;
                case 'all':
                default:
                    // No additional filtering for 'all' tab
                    break;
            }
        }
        
        return $query->get();
    }

    public function headings(): array {
        return ['Product Code', 'Product Name', 'Category', 'Subcategory', 'Type',
         'Open/Close', 'Hook', 'Enamel', 'Rodium', 'Stone', 'Size', 'Length',
         'Weight From', 'Weight To', 'Created At'];
    }

    public function map($product): array {
        return [
            $product->product_code,
            $product->product_name,
            $product->category->name ?? 'N/A',
            $product->subcategory->name ?? 'N/A',
            $product->type,
            $product->open_close,
            $product->hook,
            $product->enamel,
            $product->rodium,
            $product->stone,
            $product->size,
            $product->length,
            $product->weight_from,
            $product->weight_to,
            $product->created_at->format('d M, Y')
        ];
    }
}