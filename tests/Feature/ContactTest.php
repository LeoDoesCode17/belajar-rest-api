<?php

use App\Models\Contact;
use App\Models\User;
use Database\Seeders\ContactSearchSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEquals;

uses(RefreshDatabase::class);
test('Succeed create a new contact.', function () {
    $this->seed([UserSeeder::class]); // imitate logged in user
    $user = User::where('username', 'creator09')->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->post('/api/contacts', [
        'first_name' => 'Leonardo',
        'last_name' => 'Nifinluri',
        'phone' => '082188889999',
        'email' => 'leonardo@gmail.com'
    ])->assertStatus(201)->assertJson([
        'data' => [
            'first_name' => 'Leonardo',
            'last_name' => 'Nifinluri',
            'phone' => '082188889999',
            'email' => 'leonardo@gmail.com'
        ]
    ]);
});

test('Failed to create new contact due to missing/invalid token.', function () {
    $this->seed([UserSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $user->token = null;
    $user->save();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->post('/api/contacts', [
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

test('Failed to create contact due to first name is empty.', function () {
    $this->seed([UserSeeder::class]); // imitate logged in user
    $user = User::where('username', 'creator09')->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->post('/api/contacts', [
        'first_name' => '',
        'last_name' => 'Nifinluri',
        'phone' => '082188889999',
        'email' => 'leonardox@gmail.com'
    ])->assertStatus(400)->assertJson([
        'errors' => [
            'first_name' => ['The first name field is required.']
        ]
    ]);
});

test('Failed to create contact due to one field exceed limit length.', function () {
    $this->seed([UserSeeder::class]); // imitate logged in user
    $user = User::where('username', 'creator09')->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->post('/api/contacts', [
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

test('Succeed update a contact.', function () {
    $this->seed([UserSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $contact = new Contact([
        'first_name' => 'Leonardo',
        'last_name' => 'Nifinluri',
        'phone' => '082188889999',
        'email' => 'leonardo@gmail.com',
    ]);
    $contact->user_id = $user->id;
    $contact->save();
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

test('Failed update a contact due to missing/invalid token.', function () {
    $this->seed([UserSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $user->token = null;
    $user->save();
    $contact = new Contact([
        'first_name' => 'Leonardo',
        'last_name' => 'Nifinluri',
        'phone' => '082188889999',
        'email' => 'leonardo@gmail.com',
    ]);
    $contact->user_id = $user->id;
    $contact->save();
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

test('Failed update a contact due to one field exceed limit length.', function () {
    $this->seed([UserSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $contact = new Contact([
        'first_name' => 'Leonardo',
        'last_name' => 'Nifinluri',
        'phone' => '082188889999',
        'email' => 'leonardo@gmail.com',
    ]);
    $contact->user_id = $user->id;
    $contact->save();
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

test('Failed update a contact due to not found contact.', function () {
    $this->seed([UserSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->patch("/api/contacts/8", [
        'phone' => '082199994444'
    ])->assertStatus(404)->assertJson([
        'errors' => [
            'message' => ['not found']
        ]
    ]);
});

test('Succeed to get a contact by id.', function () {
    $this->seed([UserSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $contact = new Contact([
        'first_name' => 'Leonardo',
        'last_name' => 'Nifinluri',
        'phone' => '082188889999',
        'email' => 'leonardo@gmail.com',
    ]);
    $contact->user_id = $user->id;
    $contact->save();
    $contactId = $contact->id;
    $this->withHeaders([
        'Authorization' => $user->token
    ])->get("/api/contacts/$contactId")->assertStatus(200)->assertJson([
        'data' => [
            'user_id' => $user->id,
            'first_name' => 'Leonardo',
            'last_name' => 'Nifinluri',
            'phone' => '082188889999',
            'email' => 'leonardo@gmail.com'
        ]
    ]);
});

test('Failed to get a contact by id due to missing/invalid token.', function () {
    $this->seed([UserSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $user->token = null;
    $user->save();
    $contact = new Contact([
        'first_name' => 'Leonardo',
        'last_name' => 'Nifinluri',
        'phone' => '082188889999',
        'email' => 'leonardo@gmail.com',
    ]);
    $contact->user_id = $user->id;
    $contact->save();
    $contactId = $contact->id;
    $this->withHeaders([
        'Authorization' => $user->token
    ])->get("/api/contacts/$contactId")->assertStatus(401)->assertJson([
        'errors' => [
            'message' => ['unauthorized']
        ]
    ]);
});

test('Failed to get a contact by id due to the contact belongs to other user.', function () {
    $this->seed([UserSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->get("/api/contacts/8")->assertStatus(404)->assertJson([
        'errors' => [
            'message' => ['not found']
        ]
    ]);
});

test('Failed to get a contact by id due to not found contact.', function () {
    $this->seed([UserSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->get("/api/contacts/8")->assertStatus(404)->assertJson([
        'errors' => [
            'message' => ['not found']
        ]
    ]);
});

test('Succeed to delete a contact.', function () {
    $this->seed([UserSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $contact = new Contact([
        'first_name' => 'Leonardo',
        'last_name' => 'Nifinluri',
        'phone' => '082188889999',
        'email' => 'leonardo@gmail.com',
    ]);
    $contact->user_id = $user->id;
    $contact->save();
    $contactId = $contact->id;
    $this->withHeaders([
        'Authorization' => $user->token
    ])->delete("/api/contacts/$contactId")->assertStatus(200)->assertJson([
        'data' => true
    ]);
});

test('Failed to delete a contact due to missing/invalid token.', function () {
    $this->seed([UserSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $user->token = null;
    $user->save();
    $contact = new Contact([
        'first_name' => 'Leonardo',
        'last_name' => 'Nifinluri',
        'phone' => '082188889999',
        'email' => 'leonardo@gmail.com',
    ]);
    $contact->user_id = $user->id;
    $contact->save();
    $contactId = $contact->id;
    $this->withHeaders([
        'Authorization' => $user->token
    ])->delete("/api/contacts/$contactId")->assertStatus(401)->assertJson([
        'errors' => [
            'message' => ['unauthorized']
        ]
    ]);
});

test('Failed to delete a contact due to not found contact.', function () {
    $this->seed([UserSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->delete("/api/contacts/9")->assertStatus(404)->assertJson([
        'errors' => [
            'message' => ['not found']
        ]
    ]);
});

test('Success search first name by name parameter.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $response = $this->withHeaders([
        'Authorization' => $user->token
    ])->get('/api/contacts?name=Leonardo')->assertStatus(200)->json();
    Log::info(json_encode($response, JSON_PRETTY_PRINT));
    assertEquals(10, $response['meta']['per_page']);
    assertEquals(10, count($response['data']));
    assertEquals(20, $response['meta']['total']);
});

test('Success search last name by name parameter.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $response = $this->withHeaders([
        'Authorization' => $user->token
    ])->get('/api/contacts?name=Nifinluri')->assertStatus(200)->json();
    Log::info(json_encode($response, JSON_PRETTY_PRINT));
    assertEquals(10, $response['meta']['per_page']);
    assertEquals(10, count($response['data']));
    assertEquals(20, $response['meta']['total']);
});

test('Success search phone by phone parameter.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $response = $this->withHeaders([
        'Authorization' => $user->token
    ])->get('/api/contacts?phone=089999')->assertStatus(200)->json();
    Log::info(json_encode($response, JSON_PRETTY_PRINT));
    assertEquals(10, $response['meta']['per_page']);
    assertEquals(10, count($response['data']));
    assertEquals(20, $response['meta']['total']);
});

test('Success search email by email parameter.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $response = $this->withHeaders([
        'Authorization' => $user->token
    ])->get('/api/contacts?email=@gmail.com')->assertStatus(200)->json();
    Log::info(json_encode($response, JSON_PRETTY_PRINT));
    assertEquals(10, $response['meta']['per_page']);
    assertEquals(10, count($response['data']));
    assertEquals(20, $response['meta']['total']);
});

test('Success search using two parameters.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $response = $this->withHeaders([
        'Authorization' => $user->token
    ])->get('/api/contacts?name=Leonardo&email=leonardo1@gmail.com')->assertStatus(200)->json();
    Log::info(json_encode($response, JSON_PRETTY_PRINT));
    assertEquals(10, $response['meta']['per_page']);
    assertEquals(1, count($response['data']));
    assertEquals(1, $response['meta']['total']);
});

test('Success search for parameters value that do not exist.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $response = $this->withHeaders([
        'Authorization' => $user->token
    ])->get('/api/contacts?name=LeonardoX&email=leonardo1@gmail.com')->assertStatus(200)->json();
    Log::info(json_encode($response, JSON_PRETTY_PRINT));
    assertEquals(10, $response['meta']['per_page']);
    assertEquals(0, count($response['data']));
    assertEquals(0, $response['meta']['total']);
});

test('Success search by size(perpage).', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $response = $this->withHeaders([
        'Authorization' => $user->token
    ])->get('/api/contacts?size=5&page=2')->assertStatus(200)->json();
    Log::info(json_encode($response, JSON_PRETTY_PRINT));
    assertEquals(5, $response['meta']['per_page']);
    assertEquals(5, count($response['data']));
    assertEquals(2, $response['meta']['current_page']);
    assertEquals(20, $response['meta']['total']);
});
