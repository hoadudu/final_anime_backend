<?php

namespace App\Console\Commands\Update;


use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Console\Commands\ConvertDatabase\ConvertTableHelper;

class UpdatePostsFromOldDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-posts-from-old-db {--dry-run : Show what would be updated without actually updating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update anime posts status from old database table_film data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No actual updates will be made');
        }

        try {
            // Check if anime47backup database connection exists
            $this->info('Checking database connections...');

            // Test connection to anime47backup
            try {
                DB::connection('anime47backup')->getPdo();
                $this->info('✓ Connected to anime47backup database');
            } catch (\Exception $e) {
                $this->error('✗ Failed to connect to anime47backup database: ' . $e->getMessage());
                $this->error('Please ensure the anime47backup database connection is configured in config/database.php');
                return 1;
            }

            // Get total count of films in old database
            $totalFilms = DB::connection('anime47backup')
                ->table('table_film')
                ->count();

            if ($totalFilms === 0) {
                $this->error('No films found in anime47backup.table_film');
                return 1;
            }

            $this->info("Found {$totalFilms} films in anime47backup.table_film");

            // Ask for confirmation unless in dry-run mode
            if (!$dryRun && !$this->confirm("This will update status for up to {$totalFilms} posts. Continue?")) {
                $this->info('Operation cancelled by user');
                return 0;
            }

            // Process films in batches
            $batchSize = 1000;
            $processed = 0;
            $updated = 0;
            $skipped = 0;

            $progressBar = $this->output->createProgressBar($totalFilms);
            $progressBar->start();

            DB::connection('anime47backup')
                ->table('table_film')
                ->select('film_id', 'film_director', 'film_time')
                ->orderBy('film_id')
                ->chunk($batchSize, function ($films) use (&$processed, &$updated, &$skipped, $dryRun, $progressBar) {
                    foreach ($films as $film) {
                        $processed++;

                        // Skip if film_id is null or empty
                        if (empty($film->film_id)) {
                            $skipped++;
                            $progressBar->advance();
                            continue;
                        }

                        // Find matching post in current database
                        $post = Post::where('film_id', $film->film_id)->first();

                        if (!$post) {
                            $skipped++;
                            $progressBar->advance();
                            continue;
                        }

                        // Determine new status using helper
                        $newStatus = ConvertTableHelper::checkCompletedFromArrayStringOfOldDb([
                            $film->film_director ?? null,
                            $film->film_time ?? null
                        ]);

                        // Check if status needs to be updated
                        if ($post->status === $newStatus) {
                            $skipped++;
                            $progressBar->advance();
                            continue;
                        }

                        // Show what would be updated in dry-run mode
                        if ($dryRun) {
                            $this->line("DRY RUN: Would update post {$post->id} '{$post->title}' status from '{$post->status}' to new status '{$newStatus}' (film_id: {$film->film_id})");
                        } else {
                            // Update the post
                            $post->update(['status' => $newStatus]);
                            $this->line("Updated post {$post->id} '{$post->title}' status from '{$post->status}' to new status '{$newStatus}' (film_id: {$film->film_id})");
                        }

                        $updated++;
                        $progressBar->advance();
                    }
                });

            $progressBar->finish();
            $this->newLine(2);

            // Show summary
            $this->info('=== Update Summary ===');
            $this->info("Total films processed: {$processed}");
            $this->info("Posts updated: {$updated}");
            $this->info("Posts skipped (no change needed or not found): {$skipped}");

            if ($dryRun) {
                $this->info('This was a dry run - no actual updates were made');
                $this->info('Remove --dry-run flag to perform actual updates');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}
