<?php

namespace App\Console\Commands\ConvertDatabase;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ConvertTableUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:convert-table-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert users from anime47backup table_user to new users table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting conversion of table_user from anime47backup...');

        // Get total count first
        $totalUsers = DB::connection('anime47backup')->table('table_user')->count();
        $this->info("Found {$totalUsers} users to convert.");

        $converted = 0;
        $skipped = 0;
        $processed = 0;

        // Process in chunks to avoid memory issues
        DB::connection('anime47backup')->table('table_user')->orderBy('user_id')->chunk(100, function ($oldUsers) use (&$converted, &$skipped, &$processed, $totalUsers) {
            foreach ($oldUsers as $oldUser) {
                $processed++;

                // Check if email already exists
                $existingUser = User::where('email', $oldUser->user_email)->first();

                if ($existingUser) {
                    $this->warn("Skipping user with email {$oldUser->user_email} - already exists.");
                    $skipped++;
                    continue;
                }

                // Validate email format
                if (!filter_var($oldUser->user_email, FILTER_VALIDATE_EMAIL)) {
                    $this->warn("Skipping user with invalid email: {$oldUser->user_email}");
                    $skipped++;
                    continue;
                }

                // Check if email is null or empty
                if (empty($oldUser->user_email)) {
                    $this->warn("Skipping user with empty email (user_id: {$oldUser->user_id})");
                    $skipped++;
                    continue;
                }

                // Check if username already exists
                if ($oldUser->user_name) {
                    $existingUserByUsername = User::where('username', $oldUser->user_name)->first();
                    if ($existingUserByUsername) {
                        $this->warn("Skipping user with username {$oldUser->user_name} - already exists.");
                        $skipped++;
                        continue;
                    }
                }

                // Create new user
                User::create([
                    'name' => $oldUser->user_name ?? $oldUser->user_fullname ?? 'User',
                    'email' => $oldUser->user_email,
                    'password' => null, // Will be set later with Hash::make
                    'username' => $oldUser->user_name,
                    'password_legacy' => $oldUser->user_password, // MD5 password
                    'fullname' => $oldUser->user_fullname,
                    'user_id' => $oldUser->user_id, // Preserve old user ID for reference
                ]);

                $converted++;

                // Show progress every 100 users
                if ($processed % 100 == 0) {
                    $this->info("Processed: {$processed}/{$totalUsers}, Converted: {$converted}, Skipped: {$skipped}");
                }
            }
        });

        $this->info("Conversion completed. Total: {$totalUsers}, Converted: {$converted}, Skipped: {$skipped}");
    }
}
