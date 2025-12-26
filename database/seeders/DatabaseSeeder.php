<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
                'userId' => "00236-1",
                'email_verified_at' => now(),
                'password' => Hash::make('5544220'), // password
                'remember_token' => Str::random(10),
            ]
        ]);

    }
}
