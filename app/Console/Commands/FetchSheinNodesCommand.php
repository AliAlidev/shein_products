<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Console\Command;

class FetchSheinNodesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:nodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all sections, sections types and categories then add them to shein_nodes table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return app(ProductController::class)->syncNodesCommand(['Home']);
    }
}
