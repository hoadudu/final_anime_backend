<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestUserAnimeListApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:user-anime-list-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the User Anime List API endpoints';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing User Anime List API...');

        // Get test user
        $user = User::where('email', 'testuser@example.com')->first();
        if (!$user) {
            $this->error('Test user not found. Please run UserAnimeListSeeder first.');
            return;
        }

        // Create API token for testing
        $token = $user->createToken('test-token')->plainTextToken;
        $this->info("Created API token for user: {$user->name}");

        $baseUrl = config('app.url') . '/api';
        $headers = ['Authorization' => "Bearer {$token}"];

        // Test 1: Get anime list
        $this->info("\n1. Testing GET /api/me/anime-list");
        $response = Http::withHeaders($headers)->get("{$baseUrl}/me/anime-list");
        
        if ($response->successful()) {
            $data = $response->json();
            $this->info("✅ Success: Found {$data['total_items']} items in list '{$data['list_name']}'");
            $this->table(
                ['Status', 'Count'],
                collect($data['stats'])->map(fn($count, $status) => [$status, $count])->values()
            );
        } else {
            $this->error("❌ Failed: " . $response->body());
        }

        // Test 2: Get stats
        $this->info("\n2. Testing GET /api/me/anime-list/stats");
        $response = Http::withHeaders($headers)->get("{$baseUrl}/me/anime-list/stats");
        
        if ($response->successful()) {
            $data = $response->json();
            $this->info("✅ Success: Stats retrieved");
            $this->info("Total anime: {$data['total_anime']}");
            $this->info("Total episodes: {$data['total_episodes']}");
            $this->info("Average score: " . ($data['average_score'] ?? 'N/A'));
            $this->info("Time spent: {$data['time_spent_days']} days");
        } else {
            $this->error("❌ Failed: " . $response->body());
        }

        // Test 3: Add new anime to list
        $anime = Post::whereNotIn('id', $user->defaultAnimeList->items->pluck('post_id'))->first();
        if ($anime) {
            $this->info("\n3. Testing POST /api/me/anime-list/items (Add anime)");
            $response = Http::withHeaders($headers)->post("{$baseUrl}/me/anime-list/items", [
                'post_id' => $anime->id,
                'status' => 'watching',
                'score' => 8,
                'note' => 'Added via API test'
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->info("✅ Success: Added '{$data['item']['anime']['display_title']}' to list");
            } else {
                $this->error("❌ Failed: " . $response->body());
            }
        }

        // Test 4: Update anime in list
        $listItem = $user->defaultAnimeList->items->first();
        if ($listItem) {
            $this->info("\n4. Testing PATCH /api/me/anime-list/items/{id} (Update anime)");
            $response = Http::withHeaders($headers)->patch("{$baseUrl}/me/anime-list/items/{$listItem->id}", [
                'status' => 'completed',
                'score' => 9,
                'note' => 'Updated via API test'
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->info("✅ Success: Updated '{$data['item']['anime']['display_title']}'");
            } else {
                $this->error("❌ Failed: " . $response->body());
            }
        }

        $this->info("\n🎉 API testing completed!");
        $this->info("You can now use these endpoints in your frontend application.");
        
        // Clean up token
        $user->tokens()->delete();
        $this->info("Test token cleaned up.");
    }
}
