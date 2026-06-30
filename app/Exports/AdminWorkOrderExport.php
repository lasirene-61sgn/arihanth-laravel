<?php

namespace App\Exports;

use App\Models\WorkOrder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminWorkOrderExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = WorkOrder::with(['productCategory', 'subcategoryRelation', 'buyer', 'craftsman']);

        // Filter by selected IDs if provided
        if ($this->request->filled('work_order_ids')) {
            $ids = $this->request->work_order_ids;
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            return $query->whereIn('id', $ids);
        }

        // Apply filters from request
        if ($this->request->filled('search')) {
            $searchTerm = '%' . $this->request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('work_order_number', 'LIKE', $searchTerm)
                  ->orWhere('customer_name', 'LIKE', $searchTerm)
                  ->orWhere('product_name', 'LIKE', $searchTerm)
                  ->orWhere('product_code', 'LIKE', $searchTerm)
                  ->orWhere('bp_code', 'LIKE', $searchTerm)
                  ->orWhere('reference_no', 'LIKE', $searchTerm);
            });
        }

        if ($this->request->filled('bp_code_filter')) {
            $query->where('bp_code', $this->request->bp_code_filter);
        }

        if ($this->request->filled('category_filter')) {
            $query->where('product_category', $this->request->category_filter);
        }

        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        if ($this->request->filled('craftsman_status')) {
            $query->where('craftsman_status', $this->request->craftsman_status);
        }

        // Apply sorting
        $sortBy = $this->request->get('sort_by', 'id');
        $sortOrder = $this->request->get('sort_order', 'desc');
        
        $allowedSortColumns = ['id', 'work_order_number', 'customer_name', 'product_name', 'quantity', 'due_date','craftsman_due_date', 'weight_from', 'status', 'bp_code', 'product_category', 'reference_no', 'created_at'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Work Order#',
            'Order Type',
            'BP Code',
            'Customer Name',
            'Reference No',
            'Product Code',
            'Product Name',
            'Category',
            'Sub Category',
            'Quantity',
            'Order Date',
            'Customer Due Date',
            'Craftsman Due Date',
            'Weight From',
            'Weight To',
            'Status',
            'Craftsman Status',
            'Allocated Craftsman',
            'Created At'
        ];
    }

    public function map($order): array
    {
        return [
            $order->work_order_number,
            $order->order_type,
            $order->bp_code,
            $order->customer_name,
            $order->reference_no,
            $order->product_code,
            $order->product_name,
            $order->product_category,
            $order->subcategory,
            $order->quantity,
            $order->created_at ? $order->created_at->format('d-m-Y') : 'N/A',
            $order->due_date ? $order->due_date->format('d-m-Y') : 'N/A',
            $order->craftsman_due_date ? $order->craftsman_due_date->format('d-m-Y') : 'N/A',
            $order->weight_from,
            $order->weight_to,
            ucfirst($order->status),
            ucfirst($order->craftsman_status ?? 'N/A'),
            $order->craftsman ? ($order->craftsman->name ?? $order->craftsman->full_name ?? $order->allocated_craftsman_bp_code) : ($order->allocated_craftsman_bp_code ?? 'N/A'),
            $order->created_at->format('d-m-Y H:i')
        ];
    }
}
