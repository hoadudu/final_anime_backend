<?php

namespace App\Console\Commands\Import;

use App\Models\Producer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportProducers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-producers {--page=1 : Start page for import} {--limit= : Limit number of pages to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import producers data from Jikan API';

    /**
     * Base URL for Jikan API
     */
    private const API_BASE_URL = 'https://api.jikan.moe/v4/producers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startPage = (int) $this->option('page');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $this->info('Starting producers import from Jikan API...');

        $currentPage = $startPage;
        $totalImported = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;

        $progressBar = $this->output->createProgressBar();
        $progressBar->start();

        while (true) {
            try {
                $this->info("\nFetching page {$currentPage}...");

                $response = Http::timeout(30)->get(self::API_BASE_URL, [
                    'page' => $currentPage,
                ]);

                if (!$response->successful()) {
                    $this->error("Failed to fetch page {$currentPage}: HTTP {$response->status()}");
                    break;
                }

                $data = $response->json();

                if (empty($data['data'])) {
                    $this->info("No more data on page {$currentPage}");
                    break;
                }

                $pageResults = $this->processProducersData($data['data']);
                $totalImported += $pageResults['imported'];
                $totalUpdated += $pageResults['updated'];
                $totalSkipped += $pageResults['skipped'];

                $progressBar->advance(count($data['data']));

                // Check if we should stop based on limit
                if ($limit && ($currentPage - $startPage + 1) >= $limit) {
                    $this->info("Reached limit of {$limit} pages");
                    break;
                }

                // Check pagination
                if (!($data['pagination']['has_next_page'] ?? false)) {
                    $this->info("Reached last page");
                    break;
                }

                $currentPage++;

                // Add delay to be respectful to the API
                sleep(1);

            } catch (\Exception $e) {
                $this->error("Error processing page {$currentPage}: " . $e->getMessage());
                break;
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Import completed!");
        $this->info("Imported: {$totalImported}");
        $this->info("Updated: {$totalUpdated}");
        $this->info("Skipped: {$totalSkipped}");
        $this->info("Total processed: " . ($totalImported + $totalUpdated + $totalSkipped));

        return Command::SUCCESS;
    }

    /**
     * Process producers data from API response
     */
    private function processProducersData(array $producers): array
    {
        $results = [
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        foreach ($producers as $producerData) {
            try {
                $result = $this->processSingleProducer($producerData);
                $results[$result]++;
            } catch (\Exception $e) {
                $this->error("Error processing producer {$producerData['mal_id']}: " . $e->getMessage());
                $results['skipped']++;
            }
        }

        return $results;
    }

    /**
     * Process a single producer
     */
    private function processSingleProducer(array $data): string
    {
        $malId = $data['mal_id'];

        // Skip producers with mal_id 0 (these are usually placeholders)
        if ($malId === 0) {
            return 'skipped';
        }

        // Check if producer already exists
        $existingProducer = Producer::where('mal_id', $malId)->first();

        // Prepare data for saving
        $producerData = [
            'mal_id' => $malId,
            'titles' => $this->formatTitles($data['titles'] ?? []),
            'images' => $this->formatImages($data['images'] ?? []),
            'established' => $data['established'] ?? null,
            'about' => $data['about'] ?? null,
        ];

        // Generate slug from primary title
        $producerData['slug'] = $this->generateSlug($producerData['titles']);

        if ($existingProducer) {
            // Update existing producer
            $existingProducer->update($producerData);
            return 'updated';
        } else {
            // Create new producer
            Producer::create($producerData);
            return 'imported';
        }
    }

    /**
     * Format titles array
     */
    private function formatTitles(array $titles): array
    {
        $formattedTitles = [];

        foreach ($titles as $title) {
            $formattedTitles[] = [
                'type' => $title['type'] ?? 'Default',
                'title' => $title['title'] ?? '',
            ];
        }

        return $formattedTitles;
    }

    /**
     * Format images array
     */
    private function formatImages(array $images): array
    {
        return [
            'jpg' => [
                'image_url' => $images['jpg']['image_url'] ?? null,
            ],
        ];
    }

    /**
     * Generate slug from titles
     */
    private function generateSlug(array $titles): string
    {
        // Find primary title or first available title
        $primaryTitle = '';
        foreach ($titles as $title) {
            if (($title['type'] ?? '') === 'Default') {
                $primaryTitle = $title['title'] ?? '';
                break;
            }
        }

        // If no primary title found, use the first title
        if (empty($primaryTitle) && !empty($titles)) {
            $primaryTitle = $titles[0]['title'] ?? '';
        }

        return Str::slug($primaryTitle);
    }
}
