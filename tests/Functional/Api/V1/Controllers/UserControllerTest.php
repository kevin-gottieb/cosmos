<?php

namespace Tests\Functional\Api\V1\Controllers;

use App\User;
use App\Sheet;
use App\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;

class UserControllerTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    private $token;
    private $admin_token;
    private $user;
    private $sheet;

    public function setUp()
    {
        parent::setUp();
        
        Artisan::call('migrate');
        Artisan::call('db:seed');

        // Create admin
        $admin = new User([
            'name' => 'Admin',
            'email' => 'admin@email.com',
            'role' => 'admin',
            'password' => '123456'
        ]);
        $admin->save();
        $content = $this->post('api/auth/login', [
            'email' => 'admin@email.com',
            'password' => '123456'
        ])->assertStatus(200)->decodeResponseJson();
        $this->admin_token = $content['token'];        

        // Create user
        $user = new User([
            'name' => 'Test User',
            'email' => 'test@email.com',
            'role' => 'user',
            'password' => '123456'
        ]);
        $user->save();
        $this->user = $user;
        $content = $this->post('api/auth/login', [
            'email' => 'test@email.com',
            'password' => '123456'
        ])->assertStatus(200)->decodeResponseJson();
        $this->token = $content['token'];        
    }

    public function testUserCreatedSuccessfully(){
        $headers = ['Authorization' => "Bearer " . $this->admin_token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->post('api/users', [
            'name' => 'Test User1',
            'email' => 'test1@email.com',
            'role' => 'user',
            'password' => '123456'
        ], $headers)->assertJson([
            'status' => 'ok'
        ])->assertStatus(201);     
    }

    public function testUserListSuccessfully(){
        $headers = ['Authorization' => "Bearer " . $this->admin_token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->post('api/users', [
            'name' => 'Test User1',
            'email' => 'test1@email.com',
            'role' => 'user',
            'password' => '123456'
        ], $headers)->assertJson([
            'status' => 'ok'
        ])->assertStatus(201);
    }

    public function testUserGetSuccessfully(){
        $headers = ['Authorization' => "Bearer " . $this->admin_token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->get('api/users/' . $this->user->id, $headers
        )->assertJsonStructure([
            'id', 'name', 'email', 'role'
        ])->assertJson([
            'name' => $this->user->name,
            'email' => $this->user->email,
            'role' => $this->user->role
        ])->assertStatus(200);
    }

    public function testUserUpdateSuccessfully(){
        $headers = ['Authorization' => "Bearer " . $this->admin_token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->put('api/users/' . $this->user->id, [
            'name' => 'Updated User',
            'email' => 'updated@email.com',
            'password' => '654321',
            'role' => 'manager'
        ], $headers)->assertjson([
            'status' => 'ok'
        ])->assertStatus(200);
    }

    public function testUserDeleteSuccessfully(){
        $headers = ['Authorization' => "Bearer " . $this->admin_token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->delete('api/users/' . $this->user->id, [], $headers)->assertjson([
            'status' => 'ok'
        ])->assertStatus(200);
    }

    public function testUserCreatedWithReturnsInvalidPermission(){
        $headers = ['Authorization' => "Bearer " . $this->token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->post('api/users', [
            'name' => 'Test User1',
            'email' => 'test1@email.com',
            'role' => 'user',
            'password' => '123456'
        ], $headers)->assertStatus(403);     
    }

    public function testUserListWithReturnsInvalidPermission(){
        $headers = ['Authorization' => "Bearer " . $this->token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->post('api/users', [
            'name' => 'Test User1',
            'email' => 'test1@email.com',
            'role' => 'user',
            'password' => '123456'
        ], $headers)->assertStatus(403);
    }

    public function testUserGetWithReturnsInvalidPermission(){
        $headers = ['Authorization' => "Bearer " . $this->token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->get('api/users/' . $this->user->id, $headers
        )->assertStatus(403);
    }

    public function testUserUpdateSWithReturnsInvalidPermission(){
        $headers = ['Authorization' => "Bearer " . $this->token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->put('api/users/' . $this->user->id, [
            'name' => 'Updated User',
            'email' => 'updated@email.com',
            'password' => '654321',
            'role' => 'manager'
        ], $headers)->assertStatus(403);
    }

    public function testUserDeleteWithReturnsInvalidPermission(){
        $headers = ['Authorization' => "Bearer " . $this->token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->delete('api/users/' . $this->user->id, [], $headers)->assertStatus(403);
    }

    public function testUserMeSuccessfully(){
        $headers = ['Authorization' => "Bearer " . $this->token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->get('api/me', $headers
        )->assertJsonStructure([
            'id', 'name', 'email', 'role'
        ])->assertJson([
            'name' => $this->user->name,
            'email' => $this->user->email,
            'role' => $this->user->role,        
        ])->assertStatus(200);
    }

    public function testPreferredHoursSuccessfully(){
        $headers = ['Authorization' => "Bearer " . $this->token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->post('api/preferred_hours', [
            'hours' => 12
        ], $headers)->assertJson([
            'status' => 'ok'
        ])->assertStatus(200);
    }
}
