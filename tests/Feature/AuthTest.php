<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;


    public function test_unauthenticated_user_cannot_product()
    {
        $response = $this->get('/products');

        $response->assertStatus(302);

        $response->assertRedirect('/login');
    }

    public function test_user_created_and_logged_in_successfully()
    {
        $user = User::create([
            'name' => 'Test User 2',
            'email' => 'test_3@user.com',
            'password' => Hash::make('secret'),
        ]);

        $this->actingAs($user)->assertDatabaseHas('users', [
            'email' => $user->email,
        ]);

        $this->assertAuthenticated();
    }

    public function test_login_redirect_to_products()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test2@user.com',
            'password' => bcrypt('password1234'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test2@user.com',
            'password' => 'password1234',
        ]);



        $response->assertRedirect('/products');
        $response->assertStatus(302);
    }


}
