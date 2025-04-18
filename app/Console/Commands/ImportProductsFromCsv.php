<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportProductsFromCsv extends Command
{
    protected $signature = 'import:products';

    protected $description = 'Import products from a CSV file';

    public function handle()
    {
        $path = storage_path('app/products.csv');

        if (!file_exists($path)) {
            $this->error('CSV file not found at: ' . $path);
            return;
        }

        $file = fopen($path, 'r');

        $headers = fgetcsv($file); // Read header row
        $counter = 2;
        while (($row = fgetcsv($file)) !== false) {
            try {
                $data = array_combine($headers, $row);
                Product::insert($data);
                Log::info('Product imported successfully: ' . $counter);
                $counter++;
            } catch (\Throwable $th) {
                dd($th->getMessage());
            }
        }

        fclose($file);

        $this->info('CSV imported successfully.');
    }
}
