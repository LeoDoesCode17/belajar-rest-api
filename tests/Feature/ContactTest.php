<?php

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
test('Create contact success.', function () {
    $this->seed(UserSeeder::class); // imitate logged in user
    $user = User::where('username', 'creator09')->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->post('/api/contacts', [
        'user_id' => $user->id,
        'first_name' => 'Leonardo',
        'last_name' => 'Nifinluri',
        'phone' => '082188889999',
        'email' => 'leonardo@gmail.com'
    ])->assertStatus(201)->assertJson([
        'data' => [
            'user_id' => $user->id,
            'first_name' => 'Leonardo',
            'last_name' => 'Nifinluri',
            'phone' => '082188889999',
            'email' => 'leonardo@gmail.com'
        ]
    ]);
});

test('Fail to create new contact due to missing/invalid token.', function () {
    $this->seed(UserSeeder::class);
    $user = User::where('username', 'creator09')->first();
    $user->token = null;
    $user->save();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->post('/api/contacts', [
        'user_id' => $user->id,
        'first_name' => 'Leonardo',
        'last_name' => 'Nifinluri',
        'phone' => '082188889999',
        'email' => 'leonardo@gmail.com'
    ])->assertStatus(401)->assertJson([
        'errors' => [
            'message' => ['unauthorized']
        ]
    ]);
});

test('Fail to create contact due to one field exceed limit length.', function () {
    $this->seed(UserSeeder::class); // imitate logged in user
    $user = User::where('username', 'creator09')->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->post('/api/contacts', [
        'user_id' => $user->id,
        'first_name' => 'Leonardo',
        'last_name' => 'Nifinluri',
        'phone' => '082188889999099908218888999909990821888899990999082188889999099908218888999909990821888899990999',
        'email' => 'leonardox@gmail.com'
    ])->assertStatus(400)->assertJson([
        'errors' => [
            'phone' => ['The phone field must not be greater than 20 characters.']
        ]
    ]);
});
