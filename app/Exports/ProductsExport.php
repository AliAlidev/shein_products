<?php

namespace App\Exports;

use App\Models\Product;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{

    protected $products;

    public function __construct($products = [])
    {
        $this->products = $products;
    }

    public function collection()
    {
        if ($this->products)
            return $this->products;
        return Product::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Image',
            'Product Name (AR)',
            'Product Name (EN)',
            'Product Description (AR)',
            'Product Description (EN)',
            'Brand (AR)',
            'Brand (EN)',
            'Price',
            'Store',
            'Barcode',
            'Creation Date'
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->primaryImage(),
            $product->ar_name,
            $product->en_name,
            $product->ar_description,
            $product->en_description,
            $product->ar_brand,
            $product->en_brand,
            $product->price,
            $product->store,
            $product->barcode,
            Carbon::parse($product->creation_date)->format('Y-m-d')
        ];
    }
}
