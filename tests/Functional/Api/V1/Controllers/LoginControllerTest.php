<?php

namespace Tests\Functional\Api\V1\Controllers;

use Hash;
use App\User;
use App\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;

class LoginControllerTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        Artisan::call('migrate');
        Artisan::call('db:seed');
        
        $user = new User([
            'name' => 'Test',
            'email' => 'test@email.com',
            'role' => 'user',
            'password' => '123456'
        ]);

        $user->save();
    }

    public function testLoginSuccessfully()
    {
        $this->post('api/auth/login', [
            'email' => 'test@email.com',
            'password' => '123456'
        ])->assertJson([
            'status' => 'ok'
        ])->assertJsonStructure([
            'status',
            'token'
        ])->isOk();
    }

    public function testLoginWithReturnsWrongCredentialsError()
    {
        $this->post('api/auth/login', [
            'email' => 'unknown@email.com',
            'password' => '123456'
        ])->assertJsonStructure([
            'error'
        ])->assertStatus(403);
    }

    public function testLoginWithReturnsValidationError()
    {
        $this->post('api/auth/login', [
            'email' => 'test@email.com'
        ])->assertJsonStructure([
            'error'
        ])->assertStatus(422);
    }
}
