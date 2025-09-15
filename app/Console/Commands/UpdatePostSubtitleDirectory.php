<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class UpdatePostSubtitleDirectory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:update-subtitle-directory {--force : Force update even if directory is already set}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update subtitle_directory field for posts that do not have it set';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update subtitle directories...');

        $query = Post::query();
        
        if (!$this->option('force')) {
            $query->whereNull('subtitle_directory')
                  ->orWhere('subtitle_directory', '');
        }

        $posts = $query->get();
        
        if ($posts->isEmpty()) {
            $this->info('No posts need subtitle_directory updates.');
            return;
        }

        $this->info("Found {$posts->count()} posts to update.");

        $updated = 0;
        $bar = $this->output->createProgressBar($posts->count());

        foreach ($posts as $post) {
            $directory = "subtitles/{$post->id}_{$post->slug}";
            
            $post->subtitle_directory = $directory;
            
            if ($post->save()) {
                $updated++;
                $this->line("  Updated Post ID: {$post->id} - Directory: {$directory}");
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully updated {$updated} posts with subtitle directories.");
    }
}
