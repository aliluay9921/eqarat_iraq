<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            "user_name" => "admin",
            "phone_number" => "07713982404",
            "password" => bcrypt("11111111"),
            "user_type" => 0

        ]);
        // \App\Models\User::factory(10)->create();
    }
}
