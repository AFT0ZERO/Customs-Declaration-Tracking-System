<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        DB::table('users')->insert([
            //Global User
            [
                'name' => 'Fuad',
                'email' => "fuad@atcc.com.jo",
                'userId' => 00236-1,
                'email_verified_at' => now(),
                'password' => Hash::make('5544220'), // password
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Mohammed O',
                'email' => "fuad@atcc.com.jo",
                'userId' => 00236-2,
                'email_verified_at' => now(),
                'password' => Hash::make('5544220'), // password
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Sohaib',
                'email' => "fuad@atcc.com.jo",
                'userId' => 00236-3,
                'email_verified_at' => now(),
                'password' => Hash::make('5544220'), // password
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Ghassan',
                'email' => "fuad@atcc.com.jo",
                'userId' => 00236-4,
                'email_verified_at' => now(),
                'password' => Hash::make('5544220'), // password
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Moatasem',
                'email' => "fuad@atcc.com.jo",
                'userId' => 00236-5,
                'email_verified_at' => now(),
                'password' => Hash::make('5544220'), // password
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Abdallah',
                'email' => "fuad@atcc.com.jo",
                'userId' => 00236-6,
                'email_verified_at' => now(),
                'password' => Hash::make('5544220'), // password
                'remember_token' => Str::random(10),
            ],
        ]);

    }
}
