<?php

namespace App\Console\Commands\Import;

use App\Helpers\Import\AnimeImportService;
use Illuminate\Console\Command;

class ImportPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-post {mal_id : MAL ID of the anime to import} {--force : Force update if anime already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a single anime post from Jikan API by MAL ID';

    protected AnimeImportService $importService;

    public function __construct()
    {
        parent::__construct();
        $this->importService = new AnimeImportService();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $malId = (int) $this->argument('mal_id');
        $force = $this->option('force');

        if ($malId <= 0) {
            $this->error('Invalid MAL ID. Please provide a valid positive integer.');
            return Command::FAILURE;
        }

        $this->info("Starting import for anime with MAL ID: {$malId}");

        try {
            $result = $this->importService->importAnime($malId, $force);

            if ($result['success']) {
                $post = $result['post'];
                $relatedStats = $result['related_stats'] ?? [];

                $this->info('Import successful!');
                $this->newLine();
                
                $this->info('Post details:');
                $this->line("ID: {$post->id}");
                $this->line("MAL ID: {$post->mal_id}");
                $this->line("Title: {$post->title}");
                $this->line("Slug: {$post->slug}");
                $this->line("Type: {$post->type}");
                $this->line("Status: {$post->status}");
                $this->line("Episodes: {$post->episodes}");
                $this->newLine();

                // Display titles
                if ($post->titles->count() > 0) {
                    $this->info('Titles:');
                    foreach ($post->titles as $title) {
                        $primary = $title->is_primary ? ' [PRIMARY]' : '';
                        $this->line("  - {$title->title} ({$title->type}){$primary}");
                    }
                    $this->newLine();
                }

                // Display related import stats
                if (!empty($relatedStats)) {
                    $this->info('Related data imported:');
                    
                    if (isset($relatedStats['genres'])) {
                        $genres = $relatedStats['genres'];
                        $this->line("Genres: {$genres['created']} created, {$genres['linked']} linked");
                    }
                    
                    if (isset($relatedStats['producers'])) {
                        $producers = $relatedStats['producers'];
                        $this->line("Producers: {$producers['created']} created, {$producers['linked']} linked");
                    }
                }

                return Command::SUCCESS;

            } else {
                $this->error("Import failed: " . $result['message']);
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('Import failed with exception: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}