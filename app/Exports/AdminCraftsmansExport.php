<?php

namespace App\Exports;

use App\Models\Craftman;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminCraftsmansExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // This gets all active craftmen for the file
        return Craftman::all();
    }

    /**
    * Table Headings (Top row of Excel)
    */
    public function headings(): array
    {
        return [
            'Craftman Code',
            'Business Name',
            'Contact Person',
            'Email',
            'Mobile',
            'Created Date',
        ];
    }

    /**
    * Mapping Data (Matching columns to database fields)
    */
    public function map($craftman): array
    {
        return [
            $craftman->craftman_code,
            $craftman->business_name,
            $craftman->name,
            $craftman->email,
            $craftman->mobile,
            $craftman->created_at->format('d-m-Y'),
        ];
    }
}