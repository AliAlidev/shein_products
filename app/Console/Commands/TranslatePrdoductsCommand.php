<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ChatGPTTranslationService;
use Illuminate\Console\Command;

class TranslatePrdoductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all products from products_table and using openai to translate english fields to arabic';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $translationService =
        app(ChatGPTTranslationService::class)->translateBulkRecords();
        // $products = Product::whereNull('ar_name')->map(function($product){
        //     $translationService->
        // });
        // dd($products);
    }
}
