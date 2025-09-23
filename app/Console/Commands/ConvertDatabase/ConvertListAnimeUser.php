<?php
namespace App\Console\Commands\ConvertDatabase;
ini_set('memory_limit', '4G'); // hoặc '1G'
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;
use App\Models\UserAnimeList;
use App\Models\UserAnimeListItem;

class ConvertListAnimeUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:convert-list-anime-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert favorites from anime47backup table_favorite to user_anime_list_items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting optimized conversion of table_favorite from anime47backup...');

        // Get total count first
        $totalFavorites = DB::connection('anime47backup')->table('table_favorite')->count();
        $this->info("Found {$totalFavorites} favorites to convert.");

        // Pre-load mappings to speed up processing
        $this->info('Pre-loading mappings...');
        $filmIdToPostId = Post::whereNotNull('film_id')->pluck('id', 'film_id')->toArray();
        $oldUserIdToNewUserId = User::whereNotNull('user_id')->pluck('id', 'user_id')->toArray();
        $userIdToDefaultListId = [];

        // Get all users and their default lists
        User::with('defaultAnimeList')->chunk(1000, function ($users) use (&$userIdToDefaultListId) {
            foreach ($users as $user) {
                $defaultList = $user->getOrCreateDefaultAnimeList();
                $userIdToDefaultListId[$user->id] = $defaultList->id;
            }
        });

        $this->info('Mappings loaded. Starting conversion with larger chunks...');

        $converted = 0;
        $skipped = 0;
        $processed = 0;
        $chunkSize = 2000; // Increased chunk size for better performance

        // Process in larger chunks
        DB::connection('anime47backup')->table('table_favorite')
            ->orderBy('film_id')
            ->chunk($chunkSize, function ($favorites) use (&$converted, &$skipped, &$processed, $totalFavorites, $filmIdToPostId, $oldUserIdToNewUserId, $userIdToDefaultListId) {

            $itemsToInsert = [];

            foreach ($favorites as $favorite) {
                $processed++;

                // Check if film_id exists in mapping
                if (!isset($filmIdToPostId[$favorite->film_id])) {
                    $skipped++;
                    continue;
                }

                // Check if user_id exists in mapping
                if (!isset($oldUserIdToNewUserId[$favorite->user_id])) {
                    $skipped++;
                    continue;
                }

                $newUserId = $oldUserIdToNewUserId[$favorite->user_id];
                $postId = $filmIdToPostId[$favorite->film_id];

                // Check if default list exists for user
                if (!isset($userIdToDefaultListId[$newUserId])) {
                    $skipped++;
                    continue;
                }

                $listId = $userIdToDefaultListId[$newUserId];

                // Prepare item for bulk insert
                $itemsToInsert[] = [
                    'list_id' => $listId,
                    'post_id' => $postId,
                    'status' => 'completed',
                    'score' => 10,
                    'note' => 'Imported from old favorites',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $converted++;
            }

            // Bulk insert items
            if (!empty($itemsToInsert)) {
                // Remove duplicates within this batch
                $uniqueItems = collect($itemsToInsert)->unique(function ($item) {
                    return $item['list_id'] . '-' . $item['post_id'];
                })->toArray();

                // Check for existing items in database (more efficient with smaller batches)
                $existingKeys = [];
                foreach ($uniqueItems as $item) {
                    $existingKeys[] = $item['list_id'] . '-' . $item['post_id'];
                }

                $existingItems = DB::table('user_anime_list_items')
                    ->whereIn(DB::raw("CONCAT(list_id, '-', post_id)"), $existingKeys)
                    ->pluck(DB::raw("CONCAT(list_id, '-', post_id)"))
                    ->toArray();

                // Filter out existing items
                $itemsToInsert = array_filter($uniqueItems, function ($item) use ($existingItems) {
                    $key = $item['list_id'] . '-' . $item['post_id'];
                    return !in_array($key, $existingItems);
                });

                if (!empty($itemsToInsert)) {
                    DB::table('user_anime_list_items')->insert($itemsToInsert);
                }
            }

            // Show progress
            if ($processed % 10000 == 0 || $processed >= $totalFavorites) {
                $this->info("Processed: {$processed}/{$totalFavorites}, Converted: {$converted}, Skipped: {$skipped}");
            }
        });

        $this->info("Conversion completed. Total: {$totalFavorites}, Converted: {$converted}, Skipped: {$skipped}");
    }
}
