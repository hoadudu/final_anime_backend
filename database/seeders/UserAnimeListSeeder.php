<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\UserAnimeList;
use App\Models\UserAnimeListItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserAnimeListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user if not exists
        $user = User::firstOrCreate(
            ['email' => 'testuser@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Get or create default list (should be auto-created by observer)
        $list = $user->getOrCreateDefaultAnimeList();

        // Get some anime posts to add to the list
        $animePosts = Post::limit(10)->get();

        if ($animePosts->count() > 0) {
            foreach ($animePosts as $index => $post) {
                $statuses = ['watching', 'completed', 'on_hold', 'dropped', 'plan_to_watch'];
                $status = $statuses[$index % count($statuses)];
                
                UserAnimeListItem::updateOrCreate(
                    [
                        'list_id' => $list->id,
                        'post_id' => $post->id,
                    ],
                    [
                        'status' => $status,
                        'score' => $status === 'completed' ? rand(6, 10) : null,
                        'note' => $status === 'completed' ? 'Great anime!' : null,
                    ]
                );
            }

            $this->command->info("Added {$animePosts->count()} anime to {$user->name}'s list");
        } else {
            $this->command->warn('No anime posts found. Please run ImportPosts command first.');
        }
    }
}
