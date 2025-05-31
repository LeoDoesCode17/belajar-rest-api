<?php

use Database\Seeders\ContactSearchSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User};
use Database\Seeders\AddressSeeder;

use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;

uses(RefreshDatabase::class);

test('Succeed to create an address with complete columns value.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $contact = $user->contacts->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->post('/api/contacts/' . $contact->id . '/addresses', [
        'street' => 'Badak',
        'city' => 'Manado',
        'province' => 'Sulawesi Utara',
        'country' => 'Indonesia',
        'postal_code' => '90124',
    ])->assertStatus(201)->assertJson([
        'data' => [
            'street' => 'Badak',
            'city' => 'Manado',
            'province' => 'Sulawesi Utara',
            'country' => 'Indonesia',
            'postal_code' => '90124',
        ]
    ]);
});

test('Succeed to create an address with empty optional columns.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $contact = $user->contacts->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->post('/api/contacts/' . $contact->id . '/addresses', [
        'country' => 'Indonesia',
        'postal_code' => '90124',
    ])->assertStatus(201)->assertJson([
        'data' => [
            'street' => null,
            'city' => null,
            'province' => null,
            'country' => 'Indonesia',
            'postal_code' => '90124',
        ]
    ]);
});

test('Failed to create an address using other contact.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class]);
    $user1 = User::where('username', 'creator09')->first();
    $user2 = User::where('username', 'bowo09')->first();
    $contact = $user1->contacts->first();
    $this->withHeaders([
        'Authorization' => $user2->token
    ])->post('/api/contacts/' . $contact->id . '/addresses', [
        'street' => 'Badak',
        'city' => 'Manado',
        'province' => 'Sulawesi Utara',
        'country' => 'Indonesia',
        'postal_code' => '90124',
    ])->assertStatus(404)->assertJson([
        'errors' => [
            'message' => ['not found']
        ]
    ]);
});

test('Failed to create an address due to missing/invalid token.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $user->token = null;
    $user->save();
    $contact = $user->contacts->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->post('/api/contacts/' . $contact->id . '/addresses', [
        'street' => 'Badak',
        'city' => 'Manado',
        'province' => 'Sulawesi Utara',
        'country' => 'Indonesia',
        'postal_code' => '90124',
    ])->assertStatus(401)->assertJson([
        'errors' => [
            'message' => ['unauthorized']
        ]
    ]);
});

test('Failed to create an address due to one column exceeds characters length.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $contact = $user->contacts->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->post('/api/contacts/' . $contact->id . '/addresses', [
        'street' => 'Badak',
        'city' => 'Manado',
        'province' => 'Sulawesi Utara',
        'country' => 'Indonesia',
        'postal_code' => '901249012490124901249012490124',
    ])->assertStatus(400)->assertJson([
        'errors' => [
            'postal_code' => ['The postal code field must not be greater than 10 characters.']
        ]
    ]);
});

test('Succeed update an address.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $contact = $user->contacts->first();
    $address = $contact->addresses->first();
    $oldStreet = $address->street;
    $this->withHeaders([
        'Authorization' => $user->token
    ])->put('/api/contacts/' . $contact->id . '/addresses/' . $address->id, [
        'street' => 'Kebijaksanaan',
        'city' => 'Manado',
        'province' => 'Sulawesi Selatan',
        'country' => 'Indonesia',
        'postal_code' => '90124'
    ])->assertStatus(200)->assertJson([
        'data' => [
            'street' => 'Kebijaksanaan',
            'city' => 'Manado',
            'province' => 'Sulawesi Selatan',
            'country' => 'Indonesia',
            'postal_code' => '90124',
        ]
    ]);
    $newStreet = App\Models\Address::where('id', $address->id)->first()->street;
    assertNotEquals($oldStreet, $newStreet);
});

test('Failed to update an address using other user contact.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user1 = User::where('username', 'creator09')->first();
    $user2 = User::where('username', 'bowo09')->first();
    $contact = $user1->contacts->first();
    $address = $contact->addresses->first();
    $this->withHeaders([
        'Authorization' => $user2->token
    ])->put('/api/contacts/' . $contact->id . '/addresses/' . $address->id, [
        'street' => 'Kebijaksanaan',
        'city' => 'Manado',
        'province' => 'Sulawesi Selatan',
        'country' => 'Indonesia',
        'postal_code' => '90124'
    ])->assertStatus(404)->assertJson([
        'errors' => [
            'message' => ['not found']
        ]
    ]);
});

test('Failed to update an address due to invalid/missing token.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $user->token = null;
    $user->save();
    $contact = $user->contacts->first();
    $address = $contact->addresses->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->put('/api/contacts/' . $contact->id . '/addresses/' . $address->id, [
        'street' => 'Kebijaksanaan',
        'city' => 'Manado',
        'province' => 'Sulawesi Selatan',
        'country' => 'Indonesia',
        'postal_code' => '90124'
    ])->assertStatus(401)->assertJson([
        'errors' => [
            'message' => ['unauthorized']
        ]
    ]);
});

test('Failed to update an address due to not found address.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $contact = $user->contacts->first();
    $address = $contact->addresses->first();
    $oldStreet = $address->street;
    $this->withHeaders([
        'Authorization' => $user->token
    ])->put('/api/contacts/' . $contact->id . '/addresses/' . ($address->id + 100), [
        'street' => 'Kebijaksanaan',
        'city' => 'Manado',
        'province' => 'Sulawesi Selatan',
        'country' => 'Indonesia',
        'postal_code' => '90124'
    ])->assertStatus(404)->assertJson([
        'errors' => [
            'message' => ['not found']
        ]
    ]);
});

test('Failed to update an address due to one column value exceeds length limit.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $contact = $user->contacts->first();
    $address = $contact->addresses->first();
    $oldStreet = $address->street;
    $this->withHeaders([
        'Authorization' => $user->token
    ])->put('/api/contacts/' . $contact->id . '/addresses/' . $address->id, [
        'street' => 'Kebijaksanaan',
        'city' => 'Manado',
        'province' => 'Sulawesi Selatan',
        'country' => 'Indonesia',
        'postal_code' => '9012490124901249012490124901249012490124'
    ])->assertStatus(400)->assertJson([
        'errors' => [
            'postal_code' => ['The postal code field must not be greater than 10 characters.']
        ]
    ]);
});

test('Succeed delete an address.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $contact = $user->contacts->first();
    $address = $contact->addresses->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->delete('/api/contacts/' . $contact->id . '/addresses/' . $address->id)->assertStatus(200)->assertJson([
        'data' => true
    ]);
    $deletedAddress = App\Models\Address::where('id', $address->id)->first();
    assertNull($deletedAddress);
});

test('Failed to delete an address of other user.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user1 = User::where('username', 'creator09')->first();
    $user2 = User::where('username', 'bowo09')->first();
    $contact = $user1->contacts->first();
    $address = $contact->addresses->first();
    $this->withHeaders([
        'Authorization' => $user2->token
    ])->delete('/api/contacts/' . $contact->id . '/addresses/' . $address->id)->assertStatus(404)->assertJson([
        'errors' => [
            'message' => ['not found']
        ]
    ]);
    $deletedAddress = App\Models\Address::where('id', $address->id)->first();
    assertNotNull($deletedAddress);
});

test('Failed to delete an address due to invalid/missing token.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $user->token = null;
    $user->save();
    $contact = $user->contacts->first();
    $address = $contact->addresses->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->delete('/api/contacts/' . $contact->id . '/addresses/' . $address->id)->assertStatus(401)->assertJson([
        'errors' => [
            'message' => ['unauthorized']
        ]
    ]);
    $deletedAddress = App\Models\Address::where('id', $address->id)->first();
    assertNotNull($deletedAddress);
});

test('Failed to delete an address due to not found address.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $contact = $user->contacts->first();
    $address = $contact->addresses->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->delete('/api/contacts/' . $contact->id . '/addresses/' . ($address->id + 100))->assertStatus(404)->assertJson([
        'errors' => [
            'message' => ['not found']
        ]
    ]);
    $deletedAddress = App\Models\Address::where('id', $address->id)->first();
    assertNotNull($deletedAddress);
});

test('Succeed get an address.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $contact = $user->contacts->first();
    $address = $contact->addresses->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->get('/api/contacts/' . $contact->id . '/addresses/' . $address->id)->assertStatus(200)->assertJson([
        'data' => [
            'street' => $address->street,
            'city' => $address->city,
            'province' => $address->province,
            'country' => $address->country,
            'postal_code' => $address->postal_code,
        ]
    ]);
});

test('Failed to get an address of other user.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user1 = User::where('username', 'creator09')->first();
    $user2 = User::where('username', 'bowo09')->first();
    $contact = $user1->contacts->first();
    $address = $contact->addresses->first();
    $this->withHeaders([
        'Authorization' => $user2->token
    ])->get('/api/contacts/' . $contact->id . '/addresses/' . $address->id)->assertStatus(404)->assertJson([
        'errors' => [
            'message' => ['not found']
        ]
    ]);
});

test('Failed to get an address due to invalid/missing token.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $user->token = null;
    $user->save();
    $contact = $user->contacts->first();
    $address = $contact->addresses->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->get('/api/contacts/' . $contact->id . '/addresses/' . $address->id)->assertStatus(401)->assertJson([
        'errors' => [
            'message' => ['unauthorized']
        ]
    ]);
});

test('Failed to get an address due to not found address.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $contact = $user->contacts->first();
    $address = $contact->addresses->first();
    $this->withHeaders([
        'Authorization' => $user->token
    ])->get('/api/contacts/' . $contact->id . '/addresses/' . ($address->id + 100))->assertStatus(404)->assertJson([
        'errors' => [
            'message' => ['not found']
        ]
    ]);
});

test('Succeed get a list of addresses.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $contact = $user->contacts->first();
    $addresses = $contact->addresses;
    $response = [
        'data' => [],
    ];
    foreach ($addresses as $address) {
        $response['data'][] = [
            'street' => $address->street,
            'city' => $address->city,
            'province' => $address->province,
            'country' => $address->country,
            'postal_code' => $address->postal_code,
        ];
    }
    $this->withHeaders([
        'Authorization' => $user->token
    ])->get('/api/contacts/' . $contact->id . '/addresses')->assertStatus(200)->assertJson($response);
});

test('Failed to get a list of addresses of other user.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user1 = User::where('username', 'creator09')->first();
    $user2 = User::where('username', 'bowo09')->first();
    $contact = $user1->contacts->first();
    $this->withHeaders([
        'Authorization' => $user2->token
    ])->get('/api/contacts/' . $contact->id . '/addresses')->assertStatus(404)->assertJson([
        'errors' => [
            'message' => ['not found']
        ]
    ]);
});

test('Failed to get a list of addresses due to invalid/missing token.', function () {
    $this->seed([UserSeeder::class, ContactSearchSeeder::class, AddressSeeder::class]);
    $user = User::where('username', 'creator09')->first();
    $user->token = null;
    $user->save();
    $contact = $user->contacts->first();
    $addresses = $contact->addresses;
    $response = [
        'data' => [],
    ];
    foreach ($addresses as $address) {
        $response['data'][] = [
            'street' => $address->street,
            'city' => $address->city,
            'province' => $address->province,
            'country' => $address->country,
            'postal_code' => $address->postal_code,
        ];
    }
    $this->withHeaders([
        'Authorization' => $user->token
    ])->get('/api/contacts/' . $contact->id . '/addresses')->assertStatus(401)->assertJson([
        'errors' => [
            'message' => ['unauthorized']
        ]
    ]);
});
