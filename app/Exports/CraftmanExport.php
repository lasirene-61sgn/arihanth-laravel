<?php

namespace App\Exports;

use App\Models\Craftman;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CraftmanExport implements FromCollection, WithHeadings, ShouldAutoSize
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
        $query = Craftman::query();
        
        // Apply any existing filters from the request
        if (request()->filled('search')) {
            $searchTerm = request()->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('craftman_code', 'LIKE', "%$searchTerm%")
                  ->orWhere('business_name', 'LIKE', "%$searchTerm%")
                  ->orWhere('name', 'LIKE', "%$searchTerm%")
                  ->orWhere('mobile', 'LIKE', "%$searchTerm%")
                  ->orWhere('email', 'LIKE', "%$searchTerm%")
                  ->orWhere('city', 'LIKE', "%$searchTerm%");
            });
        }

        // Apply filters
        if (request()->filled('craftman_code')) {
            $query->where('craftman_code', request()->craftman_code);
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
        
        $allowedSortColumns = ['craftman_code', 'business_name', 'name', 'mobile', 'email', 'city', 'state', 'created_at'];
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
            'Craftman Code',
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