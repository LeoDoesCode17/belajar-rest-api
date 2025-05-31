<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('username', 'creator09')->first();
        $contacts = $user->contacts;
        foreach ($contacts as $key => $value) {
            Address::create([
                'street' => "Badak$key",
                'city' => "Manado$key",
                'province' => "Sulawesi Utara",
                'country' => "Indonesia",
                'postal_code' => "9023$key",
                'contact_id' => $value->id
            ]);
        }
        foreach ($contacts as $key => $value) {
            $tempIdx = $key + 11;
            Address::create([
                'street' => "Badak$tempIdx",
                'city' => "Manado$tempIdx",
                'province' => "Sulawesi Utara",
                'country' => "Indonesia",
                'postal_code' => "9023$tempIdx",
                'contact_id' => $value->id
            ]);
        }
    }
}
