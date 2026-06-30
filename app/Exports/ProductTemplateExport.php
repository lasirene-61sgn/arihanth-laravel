<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductTemplateExport implements FromArray, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings() : array
    {
        return [
            'product_name',
            'product_code',
            'bp_code',
            'craftsman_code',
            'category_id',
            'subcategory_id',
            'type',
            'size',
            'weight_from',
            'weight_to'
        ];
    }

    public function array(): array
    {
        return [[
            'Example Ring', 
                'PROD001', 
                'BP-101', 
                'CRAFT-55', 
                '1', 
                '1', 
                'Piece', 
                '7', 
                '2.5', 
                '3.0'
        ],
        ];
    }
}
