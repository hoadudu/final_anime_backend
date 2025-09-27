<?php

namespace App\Console\Commands\ConvertDatabase;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use CyrildeWit\EloquentViewable\Support\Period;

class FilmViewedToNewDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:film-viewed-to-new-db 
    {--batch-size=100 : Number of records to process at once} 
    {--dry-run : Show what would be done without making changes} 
    {--random : Generate random views instead of migrating from old DB}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer view counts from old database table_film to new anime_posts using eloquent-viewable';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $random = $this->option('random');
        $batchSize = (int) $this->option('batch-size');
        $dryRun = $this->option('dry-run');

        if ($random) {
            $this->info('RANDOM MODE - Generating fake views for posts');

            $batchSize = (int) $this->option('batch-size');
            $processed = 0;

            Post::orderBy('id')
                ->chunk($batchSize, function ($posts) use (&$processed, $dryRun) {
                    foreach ($posts as $post) {
                        $processed++;
                        $viewCount = rand(10, 100); // vài chục view ngẫu nhiên

                        if ($dryRun) {
                            $this->info("Would assign {$viewCount} random views to post {$post->id}");
                        } else {
                            $this->recordRandomViews($post, $viewCount);
                        }
                    }
                });

            $this->info("Random views generation complete. Processed: {$processed} posts");
            return 0;
        }



        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        // Check if old database connection exists
        try {
            $oldFilmsCount = DB::connection('anime47backup')->table('table_film')->count();
            $this->info("Found {$oldFilmsCount} records in old table_film");
        } catch (\Exception $e) {
            $this->error('Cannot connect to anime47backup database: ' . $e->getMessage());
            return 1;
        }

        // Get old films in batches
        $processed = 0;
        $updated = 0;
        $skipped = 0;

        DB::connection('anime47backup')->table('table_film')
            ->orderBy('film_id')
            ->chunk($batchSize, function ($oldFilms) use (&$processed, &$updated, &$skipped, $dryRun) {
                foreach ($oldFilms as $film) {
                    $processed++;

                    // Find matching post by film_id
                    $post = Post::where('film_id', $film->film_id)->first();

                    if (!$post) {
                        $skipped++;
                        $this->warn("Post with film_id {$film->film_id} not found, skipping");
                        continue;
                    }

                    if (!$dryRun) {
                        $this->transferViewData($post, $film);
                    } else {
                        $this->info("Would transfer views for post {$post->id} (film_id: {$film->film_id}): total={$film->film_viewed}, day={$film->film_viewed_day}, week={$film->film_viewed_week}, month={$film->film_viewed_month}");
                    }

                    $updated++;
                }
            });

        $this->info("Processing complete:");
        $this->info("- Processed: {$processed} records");
        $this->info("- Updated: {$updated} posts");
        $this->info("- Skipped: {$skipped} posts (not found)");

        return 0;
    }

    /**
     * Transfer view data from old film record to new post
     */
    private function transferViewData(Post $post, $film)
    {
        try {
            // Update total views in post table
            if (isset($film->film_viewed) && $film->film_viewed > 0) {
                $post->film_viewed = $film->film_viewed;
                $post->save();
            }

            // Record views for different periods using eloquent-viewable
            $this->recordPeriodViews($post, $film->film_viewed_day ?? 0, Period::create(now()->startOfDay(), now()->endOfDay()));
            $this->recordPeriodViews($post, $film->film_viewed_week ?? 0, Period::create(now()->startOfWeek(), now()->endOfWeek()));
            $this->recordPeriodViews($post, $film->film_viewed_month ?? 0, Period::create(now()->startOfMonth(), now()->endOfMonth()));
        } catch (\Exception $e) {
            $this->error("Error transferring views for post {$post->id}: " . $e->getMessage());
        }
    }

    /**
     * Record views for a specific period
     */
    private function recordPeriodViews(Post $post, int $viewCount, Period $period)
    {
        if ($viewCount <= 0) {
            return;
        }

        // For bulk migration, directly insert into views table for efficiency
        $viewableType = get_class($post);
        $viewableId = $post->getKey();
        $viewedAt = $period->getStartDateTime();

        // Create bulk insert data
        $insertData = [];
        for ($i = 0; $i < $viewCount; $i++) {
            $insertData[] = [
                'viewable_type' => $viewableType,
                'viewable_id' => $viewableId,
                'visitor' => null, // No visitor tracking for historical data
                'collection' => null,
                'viewed_at' => $viewedAt->format('Y-m-d H:i:s'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert in batches of 1000 to avoid memory issues
            if (count($insertData) >= 1000) {
                DB::table('views')->insert($insertData);
                $insertData = [];
            }
        }

        // Insert remaining records
        if (!empty($insertData)) {
            DB::table('views')->insert($insertData);
        }

        $this->info("Recorded {$viewCount} views for post {$post->id} in period: " . $period->getStartDateTime()->format('Y-m-d') . ' to ' . $period->getEndDateTime()->format('Y-m-d'));
    }
    
    private function recordRandomViews(Post $post, int $viewCount)
    {
        $viewableType = get_class($post);
        $viewableId = $post->getKey();
        $now = now();

        // For efficiency, limit the number of actual view records created
        // eloquent-viewable works fine with fewer records for historical data
        $maxRecords = min($viewCount, 1000); // Max 1000 records per post
        
        // If view count is very high, we'll create representative records
        // and store the total count separately
        if ($viewCount > $maxRecords) {
            // Store total count in post model if it has a view_count field
            if (isset($post->view_count)) {
                $post->view_count = $viewCount;
                $post->save();
            }
        }

        $actualRecords = min($viewCount, $maxRecords);
        $insertData = [];

        for ($i = 0; $i < $actualRecords; $i++) {
            $insertData[] = [
                'viewable_type' => $viewableType,
                'viewable_id'   => $viewableId,
                'visitor'       => null,
                'collection'    => null,
                'viewed_at'     => $now->subMinutes(rand(0, 60 * 24 * 30)) // random trong 30 ngày gần nhất
                    ->format('Y-m-d H:i:s')                
            ];

            // Insert in larger batches for better performance
            if (count($insertData) >= 5000) {
                DB::table('views')->insert($insertData);
                $insertData = [];
            }
        }

        if (!empty($insertData)) {
            DB::table('views')->insert($insertData);
        }

        $this->info("Inserted {$actualRecords} view records for post {$post->id} (representing {$viewCount} total views)");
    }
}
