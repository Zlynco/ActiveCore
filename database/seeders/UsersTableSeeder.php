<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; 
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        //User::create([
        //    'name' => 'Admin User',
        //    'email' => 'admin@example.com', // Ganti dengan email admin
        //    'password' => Hash::make('password'), // Ganti dengan password admin
        //    'role' => 'admin',
        //]);

       // \App\Models\User::create([
       //     'name' => 'Coach John',
       //     'email' => 'coach@example.com',
       //     'password' => bcrypt('password'),
       //     'role' => 'coach',
       // ]);

       // \App\Models\User::create([
       //     'name' => 'Member Jane',
       //     'email' => 'member@example.com',
       //     'password' => bcrypt('password'),
       //     'role' => 'member',
       // ]);
    }
}
