<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
test('User register successfully', function () {
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
