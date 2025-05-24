<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Hash;

use function PHPUnit\Framework\assertNotNull;

uses(RefreshDatabase::class);
test('User register successfully.', function () {
    $this->post('/api/users', [
        'username' => 'creator09',
        'password' => '12345678',
        'name' => 'Leonardo'
    ])->assertStatus(201)
        ->assertJson([
            'data' => [
                'username' => 'creator09',
                'name' => 'Leonardo'
            ]
        ]);
});

test('Register failed due to all fields are empty.', function () {
    $this->post('/api/users', [
        'username' => '',
        'password' => '',
        'name' => ''
    ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'username' => ['The username field is required.'],
                'name' => ['The name field is required.'],
                'password' => ['The password field is required.']
            ]
        ]);
});

test('Register failed due to one field is empty.', function () {
    $this->post('/api/users', [
        'username' => '',
        'password' => '12345678',
        'name' => 'Leonardo'
    ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'username' => ['The username field is required.'],
            ]
        ]);
});

test('Register failed due to existed username.', function () {
    // create a user
    $username = 'Creator09';
    $user = new User();
    $user->username = $username;
    $user->password = Hash::make('12345678');
    $user->name = 'Leonardo';
    $user->save();

    $this->post('/api/users', [
        'username' => $username,
        'password' => '12345678',
        'name' => 'Leonardo'
    ])->assertStatus(400)->assertJson([
        'errors' => [
            'username' => ['The username has already been taken.'],
        ]
    ]);
});

test('User login successfully.', function () {
    $this->seed([UserSeeder::class]);
    $this->post('/api/users/login', [
        'username' => 'creator09',
        'password' => '12345678'
    ])->assertStatus(200)->assertJson([
        'data' => [
            'username' => 'creator09',
            'name' => 'Leonardo'
        ]
    ]);
    $user = User::where('username', 'creator09')->first();
    $this->assertNotNull($user->token);
});

test('User login failed.', function () {
    $this->seed([UserSeeder::class]);
    $this->post('/api/users/login', [
        'username' => 'creator09',
        'password' => '123456789'
    ])->assertStatus(401)->assertJson([
        'errors' => [
            'message' => ['Wrong credentials']
        ]
    ]);
});
