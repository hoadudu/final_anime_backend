<?php

namespace Database\Seeders;


// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Cog\Laravel\Love\ReactionType\Models\ReactionType;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        ReactionType::firstOrCreate([
            'name' => 'Like',
        ]);

        ReactionType::firstOrCreate([
            'name' => 'Favorite',
        ]);

        ReactionType::firstOrCreate([
            'name' => 'Bookmark',
        ]);
        ReactionType::firstOrCreate([
            'name' => 'Haha',
        ]);
    }
}
