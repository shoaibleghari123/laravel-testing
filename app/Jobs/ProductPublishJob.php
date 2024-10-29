<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProductPublishJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $productId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $productId)
    {
        $this->productId = $productId;
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $product = Product::find($this->productId);

        if ($product && !$product->is_published) {
            $product->update([
                'is_published' => true,
                'published_at' => now(),
            ]);
        }
    }
}
