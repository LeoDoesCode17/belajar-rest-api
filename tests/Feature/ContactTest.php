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
