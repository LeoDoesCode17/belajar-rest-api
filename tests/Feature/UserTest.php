<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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

test('Register failed due to one field is empty', function () {
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

test('Register failed due to existed username', function () {
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
    ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'username' => ['The username has already been taken.'],
            ]
        ]);
});
