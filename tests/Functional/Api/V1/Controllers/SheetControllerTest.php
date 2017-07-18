<?php

namespace Tests\Functional\Api\V1\Controllers;

use App\User;
use App\Sheet;
use App\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;

class SheetControllerTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    private $token;
    private $admin_token;
    private $another_token;
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

        $user = new User([
            'name' => 'Test User',
            'email' => 'test@email.com',
            'role' => 'user',
            'password' => '123456'
        ]);
        $user->save();
        $content = $this->post('api/auth/login', [
            'email' => 'test@email.com',
            'password' => '123456'
        ])->assertStatus(200)->decodeResponseJson();
        $this->token = $content['token'];

        $sheet = new Sheet([
            'note' => 'Test Note',
            'wdate' => '2017-07-16',
            'hours' => 5
        ]);
        $sheet->user()->associate($user);        
        $sheet->save();
        $this->sheet = $sheet;

        $another_user = new User([
            'name' => 'Another User',
            'email' => 'another@email.com',
            'role' => 'user',
            'password' => '123456'
        ]);
        $another_user->save();
        $content = $this->post('api/auth/login', [
            'email' => 'another@email.com',
            'password' => '123456'
        ])->assertStatus(200)->decodeResponseJson();
        $this->another_token = $content['token'];
    }

    public function testSheetListSuccessfully()
    {
        $headers = ['Authorization' => "Bearer " . $this->token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $result = $this->get('api/sheets', $headers)->assertStatus(200)->decodeResponseJson();
        $first_item = $result[0];
        $this->assertTrue($first_item['id'] == $this->sheet->id);
    } 

    public function testSheetCreatedSuccessfully(){
        $headers = ['Authorization' => "Bearer " . $this->token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->post('api/sheets', [
            'note' => 'Test Note',
            'wdate' => '2017-07-16',
            'hours' => 10
        ], $headers)->assertJson([
            'status' => 'ok'
        ])->assertStatus(201);
    }

    public function testSheetUpdatedSuccessfully(){
        $headers = ['Authorization' => "Bearer " . $this->token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->put('api/sheets/' . $this->sheet->id, [
            'note' => 'Updated Note',
            'wdate' => '2017-07-13',
            'hours' => 8
        ], $headers)->assertJson([
            'status' => 'ok'
        ])->assertStatus(200);
    }

    public function testSheetDeletedSuccessfully(){
        $headers = ['Authorization' => "Bearer " . $this->token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->delete('api/sheets/' . $this->sheet->id, [

        ], $headers)->assertjson([
            'status' => 'ok'
        ])->assertStatus(200);
    }

    public function testSheetGetSuccessfully(){
        $headers = ['Authorization' => "Bearer " . $this->token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->get('api/sheets/' . $this->sheet->id, $headers
        )->assertJsonStructure([
            'id', 'note', 'wdate', 'hours'
        ])->assertJson([
            'note' => $this->sheet->note,
            'wdate' => $this->sheet->wdate,
            'hours' => $this->sheet->hours
        ])->assertStatus(200);
    }

    public function testSheetListWithAdmin(){
        $headers = ['Authorization' => "Bearer " . $this->admin_token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $result = $this->get('api/sheets', $headers)->assertStatus(200)->decodeResponseJson();
        $first_item = $result[0];
        $this->assertTrue($first_item['id'] == $this->sheet->id);
    }

    public function testSheetCreatedWithAdmin(){
        $headers = ['Authorization' => "Bearer " . $this->admin_token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->post('api/sheets', [
            'note' => 'Test Note',
            'wdate' => '2017-07-16',
            'hours' => 10
        ], $headers)->assertJson([
            'status' => 'ok'
        ])->assertStatus(201);
    }

    public function testSheetUpdatedWithAdmin(){
        $headers = ['Authorization' => "Bearer " . $this->admin_token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->put('api/sheets/' . $this->sheet->id, [
            'note' => 'Updated Note',
            'wdate' => '2017-07-13',
            'hours' => 8
        ], $headers)->assertJson([
            'status' => 'ok'
        ])->assertStatus(200);
    }

    public function testSheetDeletedWithAdmin(){
        $headers = ['Authorization' => "Bearer " . $this->admin_token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->delete('api/sheets/' . $this->sheet->id, [

        ], $headers)->assertjson([
            'status' => 'ok'
        ])->assertStatus(200);
    }

    public function testSheetGetWithReturnsInvalidPermission(){
        $headers = ['Authorization' => "Bearer " . $this->another_token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->get('api/sheets/' . $this->sheet->id, $headers
        )->assertStatus(403);
    }

    public function testSheetUpdateWithReturnsInvalidPermission(){
        $headers = ['Authorization' => "Bearer " . $this->another_token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->put('api/sheets/' . $this->sheet->id, [
            'note' => 'Updated Note',
            'wdate' => '2017-07-13',
            'hours' => 8
        ], $headers)->assertStatus(403);
    }

    public function testSheetDeleteWithReturnsInvalidPermission(){
        $headers = ['Authorization' => "Bearer " . $this->another_token, 'Content-Type' => "application/x-www-form-urlencoded"];
        $this->delete('api/sheets/' . $this->sheet->id, [

        ], $headers)->assertStatus(403);
    }
}
