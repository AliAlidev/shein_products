<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Console\Command;

class FetchProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Fetch products from shein using rapidapi portal with some filters channel name 'Women', 'Men', 'Kids', 'Beauty', 'Curve', 'Home', 'All'";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // return app(ProductController::class)->syncProductsCommand('Home');
    }
}
