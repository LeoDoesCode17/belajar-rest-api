<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'creator09',
            'password' => Hash::make('12345678'),
            'name' => 'Leonardo',
            'token' => 'TEKNIK'
        ]);
        User::create([
            'username' => 'bowo09',
            'password' => Hash::make('12345678'),
            'name' => 'Bowo',
            'token' => 'BOWO'
        ])
    }
}
