<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductPriceTest extends TestCase
{

    public function test_product_price_store_successfully()
    {
        $product = new Product([
            'name' => 'Product 8',
            'price' => 1.23
        ]);

        $this->assertEquals(123, $product->price);
    }
}
