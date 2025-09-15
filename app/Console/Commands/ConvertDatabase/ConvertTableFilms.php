<?php

namespace App\Console\Commands\ConvertDatabase;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use Illuminate\Support\Str;
use voku\helper\UTF8;
use App\Console\Commands\ConvertDatabase\ConvertTableHelper;
use App\Models\PostMorphable;
use App\Console\Commands\ConvertDatabase\ConvertCatsHelper;
use App\Models\PostImage;
use App\Models\PostTitle;

class ConvertTableFilms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:films {--chunk=1000 : Number of records to process at a time} {--dry-run : Show what would be done without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert table_film data to anime_posts table';

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
        $totalRecords = $externalDb->table('table_film')->count();
        $this->info("📊 Total records to process: {$totalRecords}");

        if ($totalRecords === 0) {
            $this->warn('⚠️  No records found in table_film');
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
        //SELECT CONVERT(CAST(film_name AS BINARY) USING utf8mb4) AS fixed_name
        //FROM films;
        $externalDb->table('table_film')
            ->select('*')  // Các cột khác
            ->selectRaw("CONVERT(CAST(film_info AS BINARY) USING utf8mb4) as film_info")
            ->selectRaw("CONVERT(CAST(film_name AS BINARY) USING utf8mb4) as film_name")
            ->selectRaw("CONVERT(CAST(film_name_real AS BINARY) USING utf8mb4) as film_name_real")
            // ->whereRaw('film_myanimelist IS NOT NULL AND film_myanimelist != ""')       
            // ->whereRaw('film_myanimelist IS NULL OR film_myanimelist = ""')
            ->orderBy('film_id')
            // ->limit(10)
            ->chunk($chunkSize, function ($films) use (&$processed, &$skipped, &$errors, $dryRun, $bar) {
                foreach ($films as $film) {
                    try {
                        // Check if already exists

                        $existingPost = Post::where('film_id', $film->film_id)->first();

                        if ($existingPost) {
                            $skipped++;
                            $bar->advance();
                            continue;
                        }

                        $postData = $this->transformFilmData($film);

                        if ($dryRun) {
                            // Just show what would be done
                            $this->line("🔄 Would create: {$postData['title']} (MAL ID: {$postData['mal_id']})");

                            // write to txt file to test
                            // file_put_contents(storage_path('app/convert_films_dry_run.txt'), $postData['title'] . "\n", FILE_APPEND);
                        } else {
                            $post = Post::create($postData);
                            // Attach genres
                            $this->attachGenresToPost($post->id, $film->film_cat);
                            // Create post image if film_img exists
                            if (!empty($film->film_img)) {
                                PostImage::insertFromUrl($post->id, $film->film_img, null, true, 'poster', $film->film_name ?? 'Anime Poster');
                            }
                            // Create post titles from film_name_real
                            $this->createPostTitles($post->id, $film->film_name_real);
                        }

                        $processed++;
                        $bar->advance();
                    } catch (\Exception $e) {
                        $this->error("❌ Error processing film_id {$film->film_id}: {$e->getMessage()}");
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
     * Transform film data to post data
     */
    private function transformFilmData(object $film): array
    {


        //`film_id`, `film_name`, `film_name_real`, `film_name_ascii`, `film_name_ascii_real`, `film_actor`, `film_img`, `film_info`, `film_viewed`, `film_viewed_day`, `film_cat`, `film_director`, `film_time`, `film_year`, `film_area`, `film_broken`, `film_rating`, `film_rating_total`, `film_rate`, `film_country`, `film_lb`, `film_type`, `film_incomplete`, `film_server`, `film_request`, `film_trailer`, `film_upload`, `film_tag`, `film_download`, `film_sub`, `film_date`, `film_date_created`, `film_show`, `film_actor_ascii`, `film_director_ascii`, `film_tbphim`, `film_thumb`, `film_tag_ascii`, `film_name_seo`, `film_tapphim`, `film_fan`, `film_viewed_week`, `film_viewed_month`, `episode_info`, `name_fansub`, `film_img2`, `last_user`, `film_comment`, `film_fbcomment`, `film_hide`, `film_adult`, `film_myanimelist`, `film_completed`, `film_cm_link`, `film_leech`, `film_review`
        return [
            // Basic info
            'mal_id' => ConvertTableHelper::getMalIdFromUrl($film->film_myanimelist),
            'title' => ($film->film_name) ?? ($film->film_name_real) ?? 'Unknown Title',
            'slug' => ConvertTableHelper::generateUniqueSlug($film->film_name_ascii ?? $film->film_name ?? 'unknown', Post::class),

            // Description/Synopsis
            'synopsis' => $film->film_info ? ConvertTableHelper::cleanSynopsis($film->film_info) : null,


            // Status and type
            'status' => ConvertTableHelper::mapStatus($film->film_director ?? null),
            'type' => ConvertTableHelper::mapType($film->film_type ?? null),

            // Episodes
            //'status' => $film->film_time ?? null,

            // Dates
            'aired_from' => null,
            'aired_to' => null, // Will be set if completed


            // Additional fields
            'source' => null, // Mark as imported from anime47
            'approved' => true, // Auto-approve imported data


            'film_id' => $film->film_id ?? null, // For reference
            'facebook_comment' => $film->film_cm_link ?? null,
            'film_tag' => $film->film_tag ?? null,
            //film_viewed,film_year,film_country,film_lb,film_type,
            'film_viewed' => $film->film_viewed ?? null,
            'film_year' => $film->film_year ?? null,
            'film_country' => $film->film_country ?? null,
            'film_lb' => $film->film_lb ?? null,
            'film_type' => $film->film_type ?? null,
            // Timestamps
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    
    /**
     * Attach genres to post based on film_cat
     */
    private function attachGenresToPost($postId, $filmCat)
    {
        if (!$filmCat) return;

        // Parse film_cat string to array of cat_ids
        $catIds = explode(',', $filmCat);
        $catIds = array_map('intval', array_filter($catIds)); // Clean and convert to int

        if (empty($catIds)) return;

        // Get genre ids from ConvertCatsHelper
        $helper = new ConvertCatsHelper();
        $genreIds = $helper->getGenreIdsByCatIds($catIds);

        if (empty($genreIds)) return;

        // Attach each genre to post
        foreach ($genreIds as $genreId) {
            PostMorphable::attachGenreToPost($postId, $genreId);
        }
    }

    /**
     * Create post titles from film_name_real
     */
    private function createPostTitles($postId, $filmNameReal)
    {
        if (!$filmNameReal) return;

        // Split by | or ,
        $titles = preg_split('/\s*\|\s*|\s*,\s*/', $filmNameReal);
        $titles = array_map('trim', array_filter($titles));

        if (empty($titles)) return;

        $isFirst = true;
        foreach ($titles as $title) {
            if (!empty($title)) {
                PostTitle::create([
                    'post_id' => $postId,
                    'title' => $title,
                    'type' => 'Official',
                    'language' => null, // Or detect language if needed
                    'is_primary' => $isFirst,
                ]);
                $isFirst = false;
            }
        }
    }
}
