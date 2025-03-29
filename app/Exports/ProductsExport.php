<?php

namespace App\Exports;

use App\Models\Product;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithColumnWidths
{

    protected $products;
    protected $withImages;

    public function __construct($products = [], $withImages = false)
    {
        $this->products = $products;
        $this->withImages = $withImages;
    }

    public function collection()
    {
        if ($this->products)
            return $this->products;
        return Product::all();
    }

    public function columnWidths(): array
    {
        return [
            'B' => (100 / 7 + 2), // Specific width for Image column (in characters)
            'E' => 20,
            'F' => 20
        ];
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
            $product->creation_date != '0000-00-00' ? Carbon::parse($product->creation_date)->format('Y-m-d') : null
        ];
    }

    public function registerEvents(): array
    {
        if ($this->withImages)
            return [
                AfterSheet::class => function (AfterSheet $event) {
                    $sheet = $event->sheet->getDelegate();
                    $rowCount = $this->products ? count($this->products) : Product::count();
                    $highestColumn = $sheet->getHighestColumn();
                    $highestRow = $rowCount + 1; // Including header

                    // Center all cells
                    $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    $tempFiles = []; // To keep track of temp files for cleanup

                    for ($row = 2; $row <= $rowCount + 1; $row++) {
                        $imageUrl = $sheet->getCell("B$row")->getValue();

                        if ($imageUrl && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                            try {
                                $isLocalImage = str_contains($imageUrl, config('app.url'));
                                $filePath = null;

                                if ($isLocalImage) {
                                    // Handle local storage image
                                    $relativePath = str_replace(config('app.url') . '/storage/', '', $imageUrl);
                                    $filePath = storage_path('app/public/' . $relativePath);

                                    if (!file_exists($filePath)) {
                                        continue;
                                    }
                                } else {
                                    // Handle external image
                                    $tempFilePath = tempnam(sys_get_temp_dir(), 'excel_img');
                                    $tempFiles[] = $tempFilePath; // Add to cleanup array

                                    $imageContent = @file_get_contents($imageUrl);
                                    if ($imageContent === false) {
                                        continue;
                                    }

                                    file_put_contents($tempFilePath, $imageContent);

                                    if (!file_exists($tempFilePath) || !getimagesize($tempFilePath)) {
                                        continue;
                                    }

                                    $filePath = $tempFilePath;
                                }

                                // Get image dimensions
                                [$width, $height] = getimagesize($filePath);

                                $drawing = new Drawing();
                                $drawing->setPath($filePath);
                                $drawing->setHeight(min($height, 100)); // Cap at 100px height
                                $drawing->setWidth(min($width, 100)); // Cap at 100px width
                                $drawing->setCoordinates("B$row");
                                $drawing->setOffsetX(5);
                                $drawing->setOffsetY(5);
                                $drawing->setWorksheet($sheet);

                                $sheet->setCellValue("B$row", ''); // Clear URL

                                // Set row height based on image (plus padding)
                                $sheet->getRowDimension($row)->setRowHeight($drawing->getHeight() + 10);
                                $sheet->getStyle("B$row")->getAlignment()
                                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                    ->setVertical(Alignment::VERTICAL_CENTER);
                            } catch (\Exception $e) {
                                continue;
                            }
                        }
                    }

                    // Register a cleanup function to run after the file is written
                    register_shutdown_function(function () use ($tempFiles) {
                        foreach ($tempFiles as $tempFile) {
                            if (file_exists($tempFile)) {
                                @unlink($tempFile);
                            }
                        }
                    });
                },
            ];
        else
            return [];
    }
}
