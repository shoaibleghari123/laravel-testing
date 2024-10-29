<?php

namespace Tests\Feature;

use App\Jobs\NewProductNotifyJob;
use App\Jobs\ProductPublishJob;
use App\Mail\NewProductCreated;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewProductCreatedNotification;
use App\Services\ProductService;
use app\Services\YouTubeService;
use Brick\Math\Exception\NumberFormatException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
//use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Carbon\Carbon;
class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_contains_empty_table()
    {
        $this->markTestSkipped('skipped for now');
        $response = $this->actingAs($this->user)->get('/products_rename');

        $response->assertOk(); //200 code
        $response->assertDontSee('No products found');
    }

    public function test_homepage_contains_non_empty_table()
    {
       // $this->withoutExceptionHandling();

        $product = Product::create([
            'name' => 'Product 1',
            'price' => 100
        ]);

        $response = $this->actingAs($this->user)->get('/products');

        //$response->dd() or $response->dump();

        $response->assertOk(); //200 code
        $response->assertDontSee('No products found');
        $response->assertSee('Product 1');
        $response->assertViewHas('products', function ($products) use ($product) {
            return $products->contains($product);
        });
    }

    public function test_homepage_contains_table_product()
    {
        $product = Product::create([
            'name' => 'table',
            'price' => 100
        ]);

        $response = $this->actingAs($this->user)->get('/products');

        $response->assertOk();
        $response->assertSeeText('table');
    }

    public function test_homepage_contains_product_in_order()
    {
        [$product1, $product2] = Product::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get('/products');
        $response->assertOk();
        $response->assertSeeInOrder([$product1->name, $product2->name]);
    }

    public function test_paginated_products_table_doesnt_contains_11th_records()
    {
        $products = Product::factory()->count(11)->create();
        $lastProduct = $products->last();

        $response = $this->actingAs($this->user)->get('/products');

        $response->assertOk(); //200 code
        $response->assertViewHas('products', function ($collection) use ($lastProduct) {
            return !$collection->contains($lastProduct);
        });
    }

    public function test_admin_can_see_product_create_button()
    {
        $response = $this->actingAs($this->admin)->get('/products');

        $response->assertOk(); //200 code
        $response->assertSee('Create Product');
    }

    public function test_non_admin_cannot_see_product_create_button()
    {
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertOk(); //200 code
        $response->assertDontSee('Create Product');
    }

    public function test_admin_can_access_product_create_page()
    {
        $response = $this->actingAs($this->admin)->get('/product/create');

        $response->assertOk(); //200 code
    }

    public function test_non_admin_cannot_access_product_create_page()
    {
        $response = $this->actingAs($this->user)->get('/product/create');

        $response->assertForbidden(); //403 code
    }

    public function test_product_create_with_youtube_service()
    {
        $this->mock(YoutubeService::class)
            ->shouldReceive('getThumbnailByID')
            ->with('2acw84D45V')
            ->once()
            ->andReturn('https://i.ytimg.com/vi/2acw84D45V/default.jpg');

        $product = [
            'name' => 'Product 1',
            'price' => 100,
            'youtube_id' => '2acw84D45V'
        ];

        $response = $this->followingRedirects()->actingAs($this->admin)->post('/product/store', $product);
        $response->assertOk();
    }

    public function test_product_store_successfully()
    {
        $product = [
            'name' => 'Product 1',
            'price' => 36
        ];

        $response = $this->followingRedirects()->actingAs($this->admin)->post('/product/store', $product);

        $response->assertOk(); //200 code
        $response->assertSeeText($product['name']);
        $this->assertDatabaseHas('product', [
            'name' => 'Product 1',
            'price' => 3600
        ]);

        $lastProduct = Product::latest()->first();
        $this->assertEquals($product['name'], $lastProduct->name);
        $this->assertEquals($product['price'], $lastProduct->price/100);
    }

    public function test_edit_form_contains_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->get('/product/' . $product->id . '/edit');

        $response->assertOk(); //200 code
        $response->assertSee('value="'. $product->name . '"', false);
        $response->assertSee('value="'. $product->price . '"', false);
        $response->assertViewHas('product', $product);
    }

    public function test_product_update_successfully()
    {
        $product = Product::factory()->create();
        $newProduct = [
            'name' => 'Product 2',
            'price' => 28
        ];

        $response = $this->actingAs($this->admin)->put('/product/' . $product->id, $newProduct);

        $response->assertRedirect('/products'); //302 code
        $this->assertDatabaseHas('product', [
            'name' => 'Product 2',
            'price' => 2800
        ]);
        $this->assertDatabaseMissing('product', $product->toArray());
    }

    public function test_product_update_validation_error_redirects_back_to_form()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->put('/product/' . $product->id, [
            'name' => '',
            'price' => ''
        ]);

        $response->assertRedirect(); //302 code
        $response->assertSessionHasErrors(['name', 'price']);
        $response->assertInvalid('price');
    }

    public function test_product_deleted_successfully()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->delete('product/'.$product->id);

        $response->assertRedirect('/products');//302 code

        $this->assertDatabaseMissing('product', $product->toArray());
        $this->assertModelMissing($product);
        $this->assertDatabaseCount('product', 0);
    }



    public function test_api_return_products_list()
    {
        $products = Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertOk(); //200 code
        $response->assertJsonCount(3, 'data');

    }

    public function test_api_products_returns_list()
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $response = $this->getJson('/api/products');

        $response->assertJsonFragment([
            'name' => $product1->name,
            'price' => $product2->price
        ]);

        $response->assertJsonCount('2', 'data');
    }

    public function test_product_updated_successful()
    {
        $productData =[
            'name' => 'Product 1',
            'price' => 100
        ];

        $product = Product::create($productData);

        $response = $this->putJson('/api/products/' . $product->id, [
            'name' => 'Product 2',
            'price' => 200
        ]);

        $response->assertOk();
        $response->assertJsonMissing($productData);
    }

    public function test_api_product_show_successful()
    {
        $product = Product::create([
            'name' => 'Product 1',
            'price' => 100
        ]);

        $response = $this->getJson('/api/products/' . $product->id);

        $response->assertJsonPath('data.name', $product->name);
        $response->assertJsonMissing(['created_at', 'updated_at']);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'price'
            ]
        ]);
    }

    public function test_api_product_store_successful()
    {
        $product = [
            'name' => 'Product 1',
            'price' => 25
        ];

        $response = $this->postJson('/api/products', $product);

        $response->assertCreated(); //201 code
        $response->assertSuccessful(); //but not assertOk()
        $response->assertJson([
            'name' => 'Product 1',
            'price' => 2500
        ]);
    }

    public function test_api_product_invalid_store_return_error()
    {
        $product = [
            'name' => '',
            'price' => 100
        ];

        $response = $this->postJson('/api/products', $product);

        $response->assertUnprocessable(); //422 code
        $response->assertJsonMissingValidationErrors('price');
        $response->assertJsonValidationErrors('name');
        $response->assertInvalid('name');

    }

    public function test_product_service_create_returns_product()
    {
        $product = (new ProductService())->create('Product 1', 100);

        $this->assertInstanceOf(Product::class, $product);
    }

    public function test_product_service_create_validation()
    {
        $this->expectException(NumberFormatException::class);
        (new ProductService())->create('Product 1', 100001);
    }

    public function test_product_service_create_return_validation()
    {
        try {
            (new ProductService())->create('Product 1', 100001);
        } catch (NumberFormatException $e) {
            $this->assertEquals('Price is too high', $e->getMessage());
        }
    }

    public function test_product_service_create_return_validations()
    {
        try{
            (new ProductService())->create('Product 1', 1000000);
        }catch (\Exception $e){
            $this->assertInstanceOf(NumberFormatException::class, $e);
        }
    }

    public function test_product_edit_contains_correct_values()
    {
        $product = Product::factory()->create();

        $this->assertDatabaseHas('product',[
            'name' => $product->name,
            'price' => $product->price
        ]);

        //instead of checking column name values, we can use assertModelExists
        $this->assertModelExists($product); //check eloquent model exists in database
        $response = $this->actingAs($this->admin)->get('/product/' . $product->id . '/edit');
        $response->assertOk();
        $response->assertSee('value="'. $product->name . '"', false);
        $response->assertSee('value="'. $product->price . '"', false);
        $response->assertViewHas('product', $product);
    }


    public function test_artisan_publish_command_successful()
    {
        //$product  = Product::factory()->create();
        $this->artisan('product:publish 1')
            ->assertExitCode(-1)
            ->expectsOutput('Product not found');
    }

    public function test_job_product_publish_successful()
    {
        $product = Product::factory()->create();
        $this->assertNull($product->published_at);
        $this->assertNull($product->is_published);

        (new ProductPublishJob($product->id))->handle();

        $product->refresh();

        $this->assertNotNull($product->published_at);
        $this->assertEquals('1', $product->is_published);
    }

    public function test_product_shows_when_published_at_correct_time()
    {
        $this->markTestSkipped('skipped for now as irrelevant');
        $product = Product::factory()->create([
            'published_at' => now()->addDay()->setTime(14, 00)
        ]);

        $response = $this->actingAs($this->user)->get('/products');
        $response->assertDontSeeText($product->name);

        $this->travelTo(now()->addDay()->setTime(14, 01));
        $response = $this->actingAs($this->admin)->get('/products');
        $response->assertSeeText($product->name);
    }

    public function test_product_create_photo_upload_successful()
    {
        Storage::fake();
        $fileName = 'photo1.jpg';
        $product = [
            'name' => 'Product 123',
            'price' => 100,
            'photo' => UploadedFile::fake()->image($fileName)
        ];

        $response = $this->actingAs($this->admin)->post('/product/store', $product);
        $response->assertRedirect('/products');

        $lastProduct = Product::latest()->first();
        $this->assertEquals($lastProduct->name, $lastProduct->name);
        Storage::assertExists('product/photos/' . $fileName);
    }

    public function test_product_create_job_notification_dispatched_successfully()
    {
        Bus::fake();
        $product = [
            'name' => 'Product 123',
            'price' => 100
        ];

        $response = $this->actingAs($this->admin)->post('/product/store', $product);
        $response->assertRedirect('/products');

        Bus::assertDispatched(NewProductNotifyJob::class);
    }

    public function test_product_create_mail_sent_successfully()
    {
        Mail::fake();
        Notification::fake();

        $product = [
            'name' => 'Product 123',
            'price' => 100
        ];

        $this->followingRedirects()->actingAs($this->admin)->post('/product/store', $product);

        Mail::assertSent(NewProductCreated::class);
        Notification::assertSentTo($this->admin, NewProductCreatedNotification::class);
    }

    public function test_registration_fires_events()
    {
        Event::fake();

        $this->expectsEvents(Registered::class);

        $response = $this->post('/register', [
            'name' => 'user',
            'email' => 'user@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);

        Event::assertDispatched(Registered::class);
    }



}
