<?php

namespace Database\Seeders;

use App\Models\{User, Contact};
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSearchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('username', 'creator09')->first();
        for ($i = 1; $i <= 20; $i++) {
            Contact::create([
                'first_name' => 'Leonardo' . $i,
                'last_name' => 'Nifinluri' . $i,
                'email' => 'leonardo' . $i . '@gmail.com',
                'phone' => '089999' . $i,
                'user_id' => $user->id
            ]);
        }
    }
}
