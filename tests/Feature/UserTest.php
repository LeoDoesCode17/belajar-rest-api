<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Hash;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;

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

test('User login failed credentials wrong.', function () {
    $this->seed([UserSeeder::class]);
    $this->post('/api/users/login', [
        'username' => 'creator09',
        'password' => '123456789' // wrong password
    ])->assertStatus(401)->assertJson([
        'errors' => [
            'message' => ['Wrong credentials']
        ]
    ]);
});

test('Get authenticated user successful.', function () {
    $this->seed([UserSeeder::class]);
    $this->withHeaders([
        'Authorization' => 'TEKNIK'
    ])->get('/api/users/current')->assertStatus(200)->assertJson([
        'data' => [
            'username' => 'creator09',
            'name' => 'Leonardo',
        ]
    ]);
});

test('Unauthorized get current user failed.', function () {
    $this->seed([UserSeeder::class]);
    $this->withHeaders([
        'Autorization' => null
    ])->get('/api/users/current')->assertStatus(401)->assertJson([
        'errors' => [
            'message' => ['unauthorized']
        ]
    ]);
});

test('Update name success.', function () {
    $this->seed([UserSeeder::class]);
    $oldName = User::where('username', 'creator09')->first()->name;
    $this->withHeaders([
        'Authorization' => 'TEKNIK'
    ])->patch('/api/users/current', [
        'name' => 'Aldo'
    ])->assertStatus(200)->assertJson([
        'data' => [
            'username' => 'creator09',
            'name' => 'Aldo',
        ]
    ]);
    $newName = User::where('username', 'creator09')->first()->name;
    assertNotEquals($oldName, $newName);
});

test('Update password success.', function () {
    $this->seed([UserSeeder::class]);
    $oldPassword = User::where('username', 'creator09')->first()->password;
    $this->withHeaders([
        'Authorization' => 'TEKNIK'
    ])->patch('/api/users/current', [
        'password' => 'new_password'
    ])->assertStatus(200)->assertJson([
        'data' => [
            'username' => 'creator09',
            'name' => 'Leonardo'
        ]
    ]);
    $newPassword = User::where('username', 'creator09')->first()->password;
    assertNotEquals($oldPassword, $newPassword);
});

test('Update password failed due to exceed length limit.', function () {
    $this->seed([UserSeeder::class]);
    $oldPassword = User::where('username', 'creator09')->first()->password;
    $this->withHeaders([
        'Authorization' => 'TEKNIK'
    ])->patch('/api/users/current', [
        'password' => 'fKeUYazLDQREtmIb1whMG2nAO8rjVZCl7qpHdJTk54NgBFExycus0vSWXRPYoi936aGzbKmhqfUn3pMwVJdLrtZCnX
leapiejgiejaoie'
    ])->assertStatus(400)->assertJson([
        'errors' => [
            'password' => ['The password field must not be greater than 100 characters.']
        ]
    ]);
    $newPassword = User::where('username', 'creator09')->first()->password;
    assertEquals($oldPassword, $newPassword);
});

test('Update name failed due to exceed length limit.', function () {
    $this->seed([UserSeeder::class]);
    $oldName = User::where('username', 'creator09')->first()->name;
    $this->withHeaders([
        'Authorization' => 'TEKNIK'
    ])->patch('/api/users/current', [
        'name' => 'fKeUYazLDQREtmIb1whMG2nAO8rjVZCl7qpHdJTk54NgBFExycus0vSWXRPYoi936aGzbKmhqfUn3pMwVJdLrtZCnX
leapiejgiejaoie'
    ])->assertStatus(400)->assertJson([
        'errors' => [
            'name' => ['The name field must not be greater than 100 characters.']
        ]
    ]);
    $newName = User::where('username', 'creator09')->first()->name;
    assertEquals($oldName, $newName);
});

test('Logout success.', function () {
    $this->seed([UserSeeder::class]);
    $this->withHeaders([
        'Authorization' => 'TEKNIK'
    ])->delete('/api/users/logout')->assertStatus(200)->assertJson([
        'data' => true
    ]);
    $user = User::where('username', 'creator09')->first();
    assertNull($user->token); // make sure the token is deleted
});
test('Logout failed due to unauthorization.', function () {
    $this->seed([UserSeeder::class]);
    $this->withHeaders([
        'Authorization' => null
    ])->delete('/api/users/logout')->assertStatus(401)->assertJson([
        'errors' => [
            'message' => ['unauthorized']
        ]
    ]);
});
