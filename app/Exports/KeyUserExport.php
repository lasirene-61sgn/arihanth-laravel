<?php

namespace App\Exports;

use App\Models\KeyUser;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KeyUserExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
    * Get the data collection based on current filters/search
    */
    public function collection()
    {
        $query = KeyUser::query();

        // Apply same filters as your index page
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('email_id', 'like', "%$search%")
                  ->orWhere('user_code', 'like', "%$search%");
            });
        }

        if ($this->request->filled('status_filter')) {
            $query->where('status', $this->request->status_filter);
        }

        // Apply sorting
        $sortBy = $this->request->get('sort_by', 'created_at');
        $sortOrder = $this->request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        return $query->get();
    }

    /**
    * Define the Excel Headings
    */
    public function headings(): array
    {
        return [
            'User Code',
            'Full Name',
            'Email ID',
            'Mobile No',
            'DOB',
            'City',
            'Country',
            'State',
            'pincode',
            'aadhar_number',
            'Status',
            'Permissions',
            'Created At'
        ];
    }

    /**
    * Map the data for each row
    */
    public function map($keyUser): array
    {
        return [
            $keyUser->user_code,
            $keyUser->full_name,
            $keyUser->email_id,
            $keyUser->mobile_no,
            $keyUser->dob,
            $keyUser->city,
            $keyUser->country,
            $keyUser->state,
            $keyUser->pincode,
            $keyUser->aadhar_number,
            $keyUser->status ? 'Active' : 'Inactive',
            implode(', ', $keyUser->getPermissionsArray()), // Converts array to comma-separated text
            $keyUser->created_at->format('d M, Y'),
        ];
    }
}