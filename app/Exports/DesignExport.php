namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DesignExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // We export all products that are not from frozen accounts, same as your index
        return Product::with(['category', 'subcategory', 'creator'])
            ->notFromFrozenAccounts()
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'Product Code',
            'Product Name',
            'Category',
            'Subcategory',
            'Type',
            'Order Type',
            'Created By',
            'Design Status',
            'Design Code',
            'Created At'
        ];
    }

    public function map($product): array
    {
        return [
            $product->product_code,
            $product->product_name,
            $product->category->name ?? 'N/A',
            $product->subcategory->name ?? 'N/A',
            $product->type,
            $product->order_type,
            $product->creator_name,
            $product->design_status,
            $product->design_code ?? '-',
            $product->created_at->format('d-m-Y'),
        ];
    }
}