<?php

namespace App\Console\Commands\ConvertDatabase;

use voku\helper\UTF8;
use Illuminate\Support\Str;
use App\Models\TranslationTeam;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\AnimeTranslationTeam;

class ConvertFansubTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:fansub {--chunk=1000 : Number of records to process at a time} {--dry-run : Show what would be done without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert table_fansub data to anime_translation_teams table';

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
        $totalRecords = $externalDb->table('table_fansub')->count();
        $this->info("📊 Total records to process: {$totalRecords}");

        if ($totalRecords === 0) {
            $this->warn('⚠️  No records found in table_fansub');
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

        $externalDb->table('table_fansub')
            ->select('*')
            ->selectRaw("CONVERT(CAST(fansub_name AS BINARY) USING utf8mb4) as fansub_name")
            ->orderBy('fansub_type')
            ->chunk($chunkSize, function ($fansubs) use (&$processed, &$skipped, &$errors, $dryRun, $bar) {
                foreach ($fansubs as $fansub) {
                    try {
                        // Check if already exists
                        $existingTeam = TranslationTeam::where('name', $fansub->fansub_name)->first();

                        if ($existingTeam) {
                            $skipped++;
                            $bar->advance();
                            continue;
                        }

                        $teamData = $this->transformFansubData($fansub);

                        if ($dryRun) {
                            // Just show what would be done
                            $this->line("🔄 Would create team: {$teamData['name']}");
                        } else {
                            TranslationTeam::create($teamData);
                        }

                        $processed++;
                        $bar->advance();
                    } catch (\Exception $e) {
                        $this->error("❌ Error processing fansub_type {$fansub->fansub_type}: {$e->getMessage()}");
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
     * Transform fansub data to translation team data
     */
    private function transformFansubData(object $fansub): array
    {
        return [
            'id'        => $fansub->fansub_type,
            'name'      => $fansub->fansub_name ?? 'Unknown Team',
            'home'      => $fansub->fansub_home ?? null,
            'logo'      => $fansub->fansub_logo ?? null,
        ];
    }
}
