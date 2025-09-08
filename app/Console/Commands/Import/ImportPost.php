<?php


namespace App\Console\Commands\Import;

use Illuminate\Console\Command;
use App\Helpers\ImportHelper;

class ImportPost extends Command
{
    protected $signature = 'app:import-post';
    protected $description = 'Importing anime titles';

    public function handle()
    {
        $this->info('Starting import...');
        
        try {
            $post = ImportHelper::importAnimeData( 1535 );
            
            // Display the results
            $this->info('Import successful!');
            $this->info('Post details:');
            $this->line("ID: {$post->id}");
            $this->line("MAL ID: {$post->mal_id}");
            $this->line("Slug: {$post->slug}");
            $this->line("Type: {$post->type}");
            
            $this->info('Processed titles:');
            foreach ($post->titles as $title) {
                $this->line(sprintf(
                    "Title: %s | Type: %s | Language: %s | Primary: %s",
                    $title->title,
                    $title->type,
                    $title->language ?? 'N/A',
                    $title->is_primary ? 'Yes' : 'No'
                ));
            }
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return 1; // Dừng command với mã lỗi
            $this->line($e->getTraceAsString());
        }
    }
}