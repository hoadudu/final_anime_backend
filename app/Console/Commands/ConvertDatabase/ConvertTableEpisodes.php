<?php

namespace App\Console\Commands\ConvertDatabase;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Episode;
use App\Models\Stream;
use App\Models\Post;
use Illuminate\Support\Str;
use voku\helper\UTF8;
use App\Console\Commands\ConvertDatabase\ConvertTableHelper;

class ConvertTableEpisodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:episodes {--chunk=1000 : Number of records to process at a time} {--dry-run : Show what would be done without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert table_episode data to anime_episodes and anime_episode_streams tables';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No data will be modified');
        }

        // Connect to external database
        $externalDb = DB::connection('anime47backup');

        // Get total count
        $totalRecords = $externalDb->table('table_episode')->count();
        $this->info("📊 Total records to process: {$totalRecords}");

        if ($totalRecords === 0) {
            $this->warn('⚠️  No records found in table_episode');
            return self::SUCCESS;
        }

        // Confirm before proceeding
        if (!$dryRun && !$this->confirm('Do you want to proceed with the conversion?')) {
            $this->info('❌ Operation cancelled');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($totalRecords);
        $bar->start();

        $processed = 0;
        $skipped = 0;
        $errors = 0;

        $externalDb->table('table_episode')
            ->select('*')
            ->selectRaw("CONVERT(CAST(episode_name AS BINARY) USING utf8mb4) as episode_name")
            ->orderBy('episode_id')
            // ->limit(20)
            ->chunk($chunkSize, function ($episodes) use (&$processed, &$skipped, &$errors, $dryRun, $bar) {
                foreach ($episodes as $episode) {
                    try {
                        // Check if already exists
                        $existingEpisode = Episode::where('episode_id', $episode->episode_id)->first();

                        if ($existingEpisode) {
                            $skipped++;
                            $bar->advance();
                            continue;
                        }

                        $episodeData = $this->transformEpisodeData($episode);

                        if ($dryRun) {
                            // Just show what would be done
                            $this->line("🔄 Would create episode: {$episodeData['episode_number']} for post {$episodeData['post_id']}");
                        } else {
                            $newEpisode = Episode::create($episodeData);
                            // Create streams
                            $this->createEpisodeStreams($newEpisode->id, $episode);
                        }

                        $processed++;
                        $bar->advance();
                    } catch (\Exception $e) {
                        $this->error("❌ Error processing episode_id {$episode->episode_id}: {$e->getMessage()}");
                        $errors++;
                        $bar->advance();
                    }
                }
            });

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('📈 Conversion Summary:');
        $this->line("✅ Processed: {$processed}");
        $this->line("⏭️  Skipped (already exists): {$skipped}");
        $this->line("❌ Errors: {$errors}");

        if ($dryRun) {
            $this->warn('🔍 This was a dry run - no data was actually modified');
        } else {
            $this->info('🎉 Conversion completed successfully!');
        }

        return self::SUCCESS;
    }

    /**
     * Transform episode data to episode data
     */
    private function transformEpisodeData(object $episode): array
    {
        // Find the corresponding Post by film_id
        $post = Post::where('film_id', $episode->episode_film)->first();
        if (!$post) {
            throw new \Exception("Post not found for film_id: {$episode->episode_film}");
        }

        return [
            'post_id' => $post->id,
            'number' => $this->extractEpisodeNumber($episode->episode_name),
            'titles' => [
                'eng' => $episode->episode_name,
                'vie' => $episode->episode_name
            ],
            'thumbnail' => null,
            // 'release_date' => $episode->episode_date ? ConvertTableHelper::parseDate($episode->episode_date) : null,
            'release_date' => now(),
            'description' => null,            
            'group' => $episode->episode_group ?? 1,
            'sort_number' => $this->extractEpisodeNumber($episode->episode_name),
            'episode_film' => $episode->episode_film ?? null,
            'episode_id' => $episode->episode_id ?? null,
            'episode_type' => $episode->episode_type ?? null,
            'episode_subtitles' => $episode->episode_subtitles ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create episode streams
     */
    private function createEpisodeStreams($episodeId, object $episode)
    {
        $streams = [];

        // episode_url -> backup
        if (!empty($episode->episode_url)) {
            $streams[] = [
                'episode_id' => $episodeId,
                'server_name' => 'backup1',
                'url' => $episode->episode_url,
                'stream_type' => 'direct',
                'quality' => 'auto',
                'language' => 'sub',
                'is_active' => true,
                'sort_order' => 0,
            ];
        }

        if (!empty($episode->episode_url_11)) {
            $streams[] = [
                'episode_id' => $episodeId,
                'server_name' => 'backup2',
                'url' => $episode->episode_url_11,
                'stream_type' => 'direct',
                'quality' => 'auto',
                'language' => 'sub',
                'is_active' => true,
                'sort_order' => 1,
            ];
        }

        // episode_url_4 -> server 1
        if (!empty($episode->episode_url_4)) {
            $streams[] = [
                'episode_id' => $episodeId,
                'server_name' => 'server1',
                'url' => $episode->episode_url_4,
                'stream_type' => 'direct',
                'quality' => 'auto',
                'language' => 'sub',
                'is_active' => true,
                'sort_order' => 2,
            ];
        }

        // episode_url_7 -> server 2
        if (!empty($episode->episode_url_7)) {
            $streams[] = [
                'episode_id' => $episodeId,
                'server_name' => 'server2',
                'url' => $episode->episode_url_7,
                'stream_type' => 'direct',
                'quality' => 'auto',
                'language' => 'sub',
                'is_active' => true,
                'sort_order' => 3,
            ];
        }

        // You can add more if needed for other urls

        foreach ($streams as $streamData) {
            Stream::create($streamData);
        }
    }

    /**
     * Extract episode number from name
     */
    private function extractEpisodeNumber(?string $name): ?int
    {
        if (!$name) return null;

        // Try to match patterns like "Episode 1", "Ep. 1", "Tập 1", etc.
        if (preg_match('/(?:episode|ep\.?|tập)\s*(\d+)/i', $name, $matches)) {
            return (int)$matches[1];
        }
        // if only number is present
        if (preg_match('/\b(\d+)\b/', $name, $matches)) {
            return (int)$matches[1];
        }

        return null;
    }

    /**
     * Map episode type
     */

    
    private function mapServerName(?string $url): string
    {
        if (str_contains($url, 'googledrive') || str_contains($url, 'drive.google')) {
            return 'BA';
        } elseif (str_contains($url, 'player.animevui.com')) {
            return 'PL';
        } elseif (str_contains($url, 'hydrax') || str_contains($url, 'short') || str_contains($url, 'abyss')) {
            return 'HY';
        }

        return 'BA'; // Default to backup
    }
}
