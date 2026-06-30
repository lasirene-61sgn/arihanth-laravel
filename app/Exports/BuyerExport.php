<?php

namespace App\Exports;

use App\Models\Buyer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BuyerExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $fields;

    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Buyer::query();
        
        // Apply any existing filters from the request
        if (request()->filled('search')) {
            $searchTerm = request()->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('bp_code', 'LIKE', "%$searchTerm%")
                  ->orWhere('business_name', 'LIKE', "%$searchTerm%")
                  ->orWhere('name', 'LIKE', "%$searchTerm%")
                  ->orWhere('mobile', 'LIKE', "%$searchTerm%")
                  ->orWhere('email', 'LIKE', "%$searchTerm%")
                  ->orWhere('city', 'LIKE', "%$searchTerm%");
            });
        }

        // Apply filters
        if (request()->filled('bp_code')) {
            $query->where('bp_code', request()->bp_code);
        }
        
        if (request()->filled('business_name')) {
            $query->where('business_name', 'LIKE', '%' . request()->business_name . '%');
        }
        
        if (request()->filled('city')) {
            $query->where('city', request()->city);
        }
        
        if (request()->filled('state')) {
            $query->where('state', request()->state);
        }

        // Apply sorting
        $sortBy = request()->get('sort_by', 'created_at');
        $sortOrder = request()->get('sort_order', 'desc');
        
        // Validate sort order
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
        
        $allowedSortColumns = ['bp_code', 'business_name', 'name', 'mobile', 'email', 'city', 'state', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }
        
        $query->orderBy($sortBy, $sortOrder);
        
        return $query->get();
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        return [
            'BP Code',
            'Business Name',
            'Contact Person',
            'Mobile',
            'Email',
            'City',
            'State',
            'Created At',
            'Updated At'
        ];
    }
}