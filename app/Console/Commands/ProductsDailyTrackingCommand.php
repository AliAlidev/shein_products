<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Console\Command;

class ProductsDailyTrackingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command aims to fetch products from the nodes that already exists, first we get node_id from products table after using it we can reach nodes table and start fetching and updating products';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return app(ProductController::class)->syncProductsDailyCommand();
    }
}
