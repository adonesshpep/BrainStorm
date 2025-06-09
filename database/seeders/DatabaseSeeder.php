<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Category::create([
            'name'=>'other',
            'avatar_id'=>1
        ]);
        User::create([
            'name'=>'BrainStorm',
            'nanoid'=>'ABCDEFGHIJQL',
            'email'=>'brainstorm@gmail.com',
            'password'=>Hash::make(env('BDP')),
            'isadmin'=>1,
            'is_activated'=>1,
        ]);
    }
}
