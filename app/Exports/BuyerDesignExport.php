<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BuyerDesignExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Product::query()
            ->whereNotNull('design_code')
            ->where('design_status', 'Accepted');

        $buyer = \Illuminate\Support\Facades\Auth::guard('buyer')->user();

        // Apply filters identical to the index page
        if ($this->request->filled('search')) {
            $query->where('product_name', 'like', '%' . $this->request->search . '%');
        }
        if ($this->request->filled('category')) {
            $query->where('product_category_id', $this->request->category);
        }
        if ($this->request->filled('subcategory')) {
            $query->where('product_subcategory_id', $this->request->subcategory);
        }
        if ($this->request->filled('lock_status')) {
            $query->filterByLockStatus($buyer, $this->request->lock_status);
        }

        $query->orderByLockStatus($buyer);

        $sort = $this->request->get('sort', 'created_at');
        $direction = $this->request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        return $query;
    }

    public function headings(): array
    {
        return ['Design Code', 'Product Name', 'Category', 'Type', 'Weight From', 'Weight To'];
    }

    public function map($design): array
    {
        return [
            $design->design_code,
            $design->product_name,
            $design->category->name ?? 'N/A',
            $design->type,
            $design->weight_from,
            $design->weight_to,
        ];
    }
}