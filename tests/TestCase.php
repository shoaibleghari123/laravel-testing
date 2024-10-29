<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public $user, $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
        $this->admin = $this->createUser(true);
    }

    private function createUser($isAdmin=false) : User
    {
        return User::factory()->create(['is_admin' => $isAdmin]);
    }
}
