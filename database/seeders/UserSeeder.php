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
     *
     * @return void
     */
    public function run()
    {
        User::firstOrCreate(['email' => 'admin@example.com'],[
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('12345678'),
            'number' => '1234567890',
            'address' => 'address',
            'is_admin' => 1,
            'email_verified_at' => now()
        ]);
        User::firstOrCreate(['email' => 'test_api@example.com'],[
            'name' => 'test_api',
            'email' => 'test_api@example.com',
            'password' => Hash::make('12345678'),
            'number' => '1234567891',
            'address' => 'address',
            'is_admin' => 0,
            'email_verified_at' => now()
        ]);
    }
}
