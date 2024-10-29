<?php

namespace Tests\Unit;

use App\Exceptions\CurrencyRateNotFoundException;
use App\Services\CurrencyService;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_convert_usd_to_eur_successful()
    {
        $result = (new CurrencyService())->convert(100, 'USD', 'EUR');

        $this->assertEquals(98, $result);
    }

    public function test_convert_usd_to_eur_successfully()
    {
        $this->assertTrue(98 == (new CurrencyService())->convert(100, 'USD', 'EUR'));
    }

    public function test_convert_usd_to_gbp_return_zero()
    {
        $result = (new CurrencyService())->convert(100, 'USD', 'gbp');

        $this->assertEquals(0, $result);
    }


    public function test_convert_gbp_to_pkr_throws_exception()
    {
        $this->expectException(CurrencyRateNotFoundException::class);
        $result = (new CurrencyService())->convert(100, 'GBP', 'PKR');

        $this->assertEquals(0, $result);
    }
}
