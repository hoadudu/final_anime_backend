<?php

namespace App\Console\Commands\Import;

use App\Helpers\Import\AnimeImportService;
use Illuminate\Console\Command;

class ImportAnimeComplete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-anime-complete {mal_id : MAL ID of the anime to import} {--force : Force update if anime already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import complete anime data (post, characters, episodes, genres, producers) from Jikan API by MAL ID';

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

        $this->info("Starting complete import for anime with MAL ID: {$malId}");
        $this->newLine();

        try {
            // Step 1: Import basic anime data
            $this->info('Step 1: Importing basic anime data...');
            $result = $this->importService->importAnime($malId, $force);

            if (!$result['success']) {
                $this->error("Failed to import anime: " . $result['message']);
                return Command::FAILURE;
            }

            $post = $result['post'];
            $this->info("✓ Anime imported: {$post->title}");
            $this->newLine();

            // Step 2: Import characters
            $this->info('Step 2: Importing characters...');
            sleep(1); // Rate limiting
            $charactersResult = $this->importService->importCharacters($post, $force);
            
            if ($charactersResult['success']) {
                $stats = $charactersResult['stats'];
                $this->info("✓ Characters: {$stats['created']} created, {$stats['updated']} updated, {$stats['linked']} linked");
            } else {
                $this->warn("⚠ Characters import failed: " . $charactersResult['message']);
            }

            // Step 3: Import episodes
            $this->info('Step 3: Importing episodes...');
            sleep(1); // Rate limiting
            $episodesResult = $this->importService->importEpisodes($post, $force);
            
            if ($episodesResult['success']) {
                $stats = $episodesResult['stats'];
                $this->info("✓ Episodes: {$stats['created']} created, {$stats['updated']} updated");
            } else {
                $this->warn("⚠ Episodes import failed: " . $episodesResult['message']);
            }

            // Step 4: Import genres (already done in basic import, but let's show stats)
            $genresStats = $result['related_stats']['genres'] ?? [];
            if (!empty($genresStats)) {
                $this->info("✓ Genres: {$genresStats['created']} created, {$genresStats['linked']} linked");
            }

            // Step 5: Import producers (already done in basic import, but let's show stats)
            $producersStats = $result['related_stats']['producers'] ?? [];
            if (!empty($producersStats)) {
                $this->info("✓ Producers: {$producersStats['created']} created, {$producersStats['linked']} linked");
            }

            $this->newLine();
            $this->info('🎉 Complete anime import finished successfully!');
            $this->newLine();

            // Display final summary
            $this->info('Final Summary:');
            $this->line("Anime: {$post->title} (ID: {$post->id})");
            $this->line("MAL ID: {$post->mal_id}");
            $this->line("Type: {$post->type}");
            $this->line("Episodes: {$post->episodes}");
            $this->line("Status: {$post->status}");
            $this->line("Score: {$post->score}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Import failed with exception: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
