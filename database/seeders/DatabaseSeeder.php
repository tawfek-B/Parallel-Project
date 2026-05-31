<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        for ($i = 0; $i < 10; $i++) {
            User::factory()->create(
                [
                    'name' => 'Test User ' . $i,
                    'email' => 'test' . $i . '@example.com',
                ]
            );
        }

        \App\Models\Product::factory(10)->create();
    }
}
