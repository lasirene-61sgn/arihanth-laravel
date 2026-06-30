<?php

namespace App\Exports;

use App\Models\ProcessOwner;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AdminExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $fields;

    public function __construct($fields = [])
    {
        // If no specific fields are provided, use all relevant fields from the process_owners table for admins
        if (empty($fields)) {
            $this->fields = [
                'user_code', 'bp_code', 'full_name', 'email_id', 'mobile_no', 'status', 
                'city', 'state', 'country', 'pincode', 'aadhar_number', 'role', 
                'permissions', 'created_at', 'updated_at'
            ];
        } else {
            $this->fields = $fields;
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ProcessOwner::where('role', 'admin')->select($this->fields)->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $headers = [];
        foreach ($this->fields as $field) {
            $headers[] = ucfirst(str_replace('_', ' ', $field));
        }
        return $headers;
    }

    /**
     * @param mixed $admin
     * @return array
     */
    public function map($admin): array
    {
        $row = [];
        foreach ($this->fields as $field) {
            if ($field === 'status') {
                $row[] = $admin->status == 1 ? 'Active' : 'Inactive';
            } elseif ($field === 'permissions') {
                $permissions = json_decode($admin->permissions, true);
                $row[] = !empty($permissions) ? implode(', ', array_map(function($perm) {
                    return ucfirst(str_replace('_', ' ', $perm));
                }, $permissions)) : 'No Permissions';
            } else {
                $row[] = $admin->$field;
            }
        }
        return $row;
    }
}