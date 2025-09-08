<?php

namespace App\Console\Commands\Import;

use App\Helpers\Import\AnimeImportService;
use App\Models\Post;
use Illuminate\Console\Command;

class ImportCharactersSimple extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-characters-simple {post_id : Post ID to import characters for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import characters for a specific post';

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
        $postId = (int) $this->argument('post_id');
        
        $post = Post::find($postId);
        
        if (!$post) {
            $this->error("Post with ID {$postId} not found");
            return Command::FAILURE;
        }

        if (!$post->mal_id) {
            $this->error("Post {$postId} has no MAL ID");
            return Command::FAILURE;
        }

        $this->info("Importing characters for: {$post->title} (MAL ID: {$post->mal_id})");

        try {
            $result = $this->importService->importCharacters($post, true);
            
            if ($result['success']) {
                $stats = $result['stats'];
                $this->info("✓ Characters import completed!");
                $this->line("Created: {$stats['created']}");
                $this->line("Updated: {$stats['updated']}");
                $this->line("Linked: {$stats['linked']}");
            } else {
                $this->error("Import failed: " . $result['message']);
                return Command::FAILURE;
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Import failed with exception: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
