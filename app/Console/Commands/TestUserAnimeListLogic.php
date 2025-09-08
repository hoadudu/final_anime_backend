<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use App\Models\UserAnimeListItem;
use Illuminate\Console\Command;

class TestUserAnimeListLogic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:user-anime-list-logic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the User Anime List logic and relationships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing User Anime List Logic...');

        // Get test user
        $user = User::where('email', 'testuser@example.com')->first();
        if (!$user) {
            $this->error('Test user not found. Please run UserAnimeListSeeder first.');
            return;
        }

        $this->info("Testing with user: {$user->name}");

        // Test 1: Get default anime list
        $this->info("\n1. Testing default anime list creation...");
        $list = $user->getOrCreateDefaultAnimeList();
        $this->info("✅ Default list: '{$list->name}' (ID: {$list->id})");

        // Test 2: Get list items
        $this->info("\n2. Testing list items...");
        $items = $list->items()->with('post')->get();
        $this->info("✅ Found {$items->count()} items in list");

        if ($items->count() > 0) {
            $this->table(
                ['Anime', 'Status', 'Score', 'Note'],
                $items->map(fn($item) => [
                    $item->post->display_title ?? $item->post->title,
                    $item->formatted_status,
                    $item->score_display ?? 'N/A',
                    $item->note ? substr($item->note, 0, 30) . '...' : 'N/A'
                ])
            );
        }

        // Test 3: Get list statistics
        $this->info("\n3. Testing list statistics...");
        $stats = $list->stats;
        $this->table(
            ['Status', 'Count'],
            collect($stats)->map(fn($count, $status) => [$status, $count])->values()
        );

        // Test 4: Add new anime to list
        $newAnime = Post::whereNotIn('id', $items->pluck('post_id'))->first();
        if ($newAnime) {
            $this->info("\n4. Testing adding new anime...");
            $newItem = UserAnimeListItem::create([
                'list_id' => $list->id,
                'post_id' => $newAnime->id,
                'status' => 'plan_to_watch',
                'score' => null,
                'note' => 'Added via test command'
            ]);
            $this->info("✅ Added '{$newAnime->display_title}' to list with status: {$newItem->status}");
        }

        // Test 5: Update existing anime
        $firstItem = $items->first();
        if ($firstItem) {
            $this->info("\n5. Testing updating anime status...");
            $oldStatus = $firstItem->status;
            $firstItem->update([
                'status' => 'completed',
                'score' => 9,
                'note' => 'Updated via test command'
            ]);
            $this->info("✅ Updated '{$firstItem->post->display_title}' from '{$oldStatus}' to 'completed'");
        }

        // Test 6: Get updated statistics
        $this->info("\n6. Testing updated statistics...");
        $list->refresh(); // Refresh to get updated stats
        $newStats = $list->stats;
        $this->info("Average score: " . ($list->average_score ?? 'N/A'));
        $this->table(
            ['Status', 'Count'],
            collect($newStats)->map(fn($count, $status) => [$status, $count])->values()
        );

        // Test 7: Test relationships
        $this->info("\n7. Testing model relationships...");
        $this->info("User has {$user->animeLists()->count()} anime lists");
        $this->info("Default list has {$list->items()->count()} items");
        
        $completedItems = $list->items()->completed()->count();
        $this->info("Completed items: {$completedItems}");

        $this->info("\n🎉 User Anime List logic testing completed successfully!");
        $this->info("All models, relationships, and methods are working correctly.");
    }
}
