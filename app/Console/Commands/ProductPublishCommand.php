<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class ProductPublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:publish {id : The ID of the product}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes the product';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $product = Product::find($this->argument('id'));
        if (!$product) {
            $this->error('Product not found');
            return -1;
        }
        if ($product->is_published) {
            $this->error('Product already published');
            return -1;
        }

        $product->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
        $this->info('Product published successfully');
        return 0;
    }
}
