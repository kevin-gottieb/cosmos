<?php

namespace Tests\Functional\Api\V1\Controllers;

use Config;
use App\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;

class SignUpControllerTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        Artisan::call('migrate');
        Artisan::call('db:seed');        
    }

    public function testSignUpSuccessfully()
    {
        $this->post('api/auth/signup', [
            'name' => 'Test User',
            'email' => 'test@email.com',
            'role' => 'user',
            'password' => '123456'
        ])->assertJson([
            'status' => 'ok'
        ])->assertStatus(201);
    }

    public function testSignUpSuccessfullyWithTokenRelease()
    {
        Config::set('boilerplate.sign_up.release_token', true);

        $this->post('api/auth/signup', [
            'name' => 'Test User',
            'email' => 'test@email.com',
            'role' => 'user',
            'password' => '123456'
        ])->assertJsonStructure([
            'status', 'token'
        ])->assertJson([
            'status' => 'ok'
        ])->assertStatus(201);
    }

    public function testSignUpReturnsValidationError()
    {
        $this->post('api/auth/signup', [
            'name' => 'Test User',
            'email' => 'test@email.com'
        ])->assertJsonStructure([
            'error'
        ])->assertStatus(422);
    }
}
