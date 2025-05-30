<?php

use App\Models\Contact;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function PHPUnit\Framework\assertNotEquals;

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

test('Success update a contact.', function () {
    $this->seed(UserSeeder::class);
    $user = User::where('username', 'creator09')->first();
    $contact = Contact::create([
        'user_id' => $user->id,
        'first_name' => 'Leonardo',
        'last_name' => 'Nifinluri',
        'phone' => '082188889999',
        'email' => 'leonardo@gmail.com'
    ]);
    $oldPhone = $contact->phone;
    $contactId = $contact->id;
    $this->withHeaders([
        'Authorization' => $user->token
    ])->patch("/api/contacts/$contactId", [
        'phone' => '082100001111'
    ])->assertStatus(200)->assertJson([
        'data' => [
            'user_id' => $user->id,
            'first_name' => 'Leonardo',
            'last_name' => 'Nifinluri',
            'phone' => '082100001111',
            'email' => 'leonardo@gmail.com'
        ]
    ]);
    $newPhone = Contact::findOrFail($contactId)->phone;
    assertNotEquals($oldPhone, $newPhone);
});

test('Fail update a contact due to missing/invalid token.', function () {
    $this->seed(UserSeeder::class);
    $user = User::where('username', 'creator09')->first();
    $user->token = null;
    $user->save();
    $contact = Contact::create([
        'user_id' => $user->id,
        'first_name' => 'Leonardo',
        'last_name' => 'Nifinluri',
        'phone' => '082188889999',
        'email' => 'leonardo@gmail.com'
    ]);
    $contactId = $contact->id;
    $this->withHeaders([
        'Authorization' => $user->token
    ])->patch("/api/contacts/$contactId", [
        'phone' => '082102819999'
    ])->assertStatus(401)->assertJson([
        'errors' => [
            'message' => ['unauthorized']
        ]
    ]);
});

test('Fail update a contact due to one field exceed limit length.', function () {
    $this->seed(UserSeeder::class);
    $user = User::where('username', 'creator09')->first();
    $contact = Contact::create([
        'user_id' => $user->id,
        'first_name' => 'Leonardo',
        'last_name' => 'Nifinluri',
        'phone' => '082188889999',
        'email' => 'leonardo@gmail.com'
    ]);
    $contactId = $contact->id;
    $this->withHeaders([
        'Authorization' => $user->token
    ])->patch("/api/contacts/$contactId", [
        'phone' => '082100001111082100001111082100001111082100001111082100001111'
    ])->assertStatus(400)->assertJson([
        'errors' => [
            'phone' => ['The phone field must not be greater than 20 characters.']
        ]
    ]);
});
