<?php

namespace App\Console\Commands\Import;

use Illuminate\Console\Command;
use App\Models\Genres;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ImportGenres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-genres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $enums = ['genres', 'explicit_genres', 'themes', 'demographics'];
        $baseUrl = 'https://api.jikan.moe/v4/genres/anime';
        $total = 0;

        foreach ($enums as $type) {
            $url = $baseUrl . '?filter=' . $type;
            $this->info("Requesting $url ...");
            try {
                $response = Http::timeout(20)->get($url);
                if (!$response->ok()) {
                    $this->error("Failed to fetch $type: " . $response->status());
                    continue;
                }
                $data = $response->json('data') ?? [];
                $count = 0;
                foreach ($data as $item) {
                    // Tạo description từ url và count nếu có
                    $description = null;
                    if (isset($item['url']) || isset($item['count'])) {
                        $description = '';
                        if (isset($item['url'])) {
                            $description .= 'URL: ' . $item['url'];
                        }
                        if (isset($item['count'])) {
                            $description .= ($description ? ' | ' : '') . 'Count: ' . $item['count'];
                        }
                    }
                    $this->info("Trying to insert: slug=" . Str::slug($item['name']) . ", type=$type");
                    
                    Genres::updateOrCreate(
                        [
                            'slug' => Str::slug($item['name']),
                            'type' => $type,
                        ],
                        [
                            'mal_id' => $item['mal_id'],
                            'name' => $item['name'],
                            'description' => $description,
                        ]
                    );
                    $count++;
                }
                $this->info("Imported $count $type.");
                $total += $count;

                // Thêm delay để tránh rate limit
                sleep(1);
            } catch (\Exception $e) {
                $this->error("Error for $type: " . $e->getMessage());
            }
        }
        $this->info("Done! Total genres imported/updated: $total");
    }
}
