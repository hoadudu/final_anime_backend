<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Cog\Laravel\Love\ReactionType\Models\ReactionType;

class RandomLikedPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:random-liked-posts 
                            {--post-id= : Specific post ID to add likes to}
                            {--reset : Clear existing likes before generating new ones}
                            {--limit= : Limit the number of posts to process}
                            {--min=50 : Minimum number of likes per post}
                            {--max=100 : Maximum number of likes per post}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate random likes (default: 50-100) for anime posts using Laravel Love';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get min and max like counts
        $min = (int)$this->option('min');
        $max = (int)$this->option('max');

        if ($min <= 0) $min = 5000;
        if ($max <= $min) $max = $min * 2;

        $this->info("Will generate {$min}-{$max} likes per post");

        // Ensure we have the 'Like' reaction type
        $likeType = $this->ensureLikeReactionType();
        if (!$likeType) {
            $this->error('Failed to create or find Like reaction type');
            return 1;
        }

        // Make sure we're using the reaction type object correctly
        $this->info('Using reaction type: ' . $likeType->name . ' (ID: ' . $likeType->getId() . ')');

        // Get posts to process
        $postId = $this->option('post-id');
        $limit = $this->option('limit');

        if ($postId) {
            // Process a single post
            $posts = Post::where('id', $postId)->get();
            $this->info("Processing specific post ID: {$postId}");
        } else {
            // Consider limit option
            if ($limit) {
                $this->info("Processing {$limit} random posts");
                $posts = Post::inRandomOrder()->limit($limit)->get();
            } else {
                // Ask for confirmation due to large number of posts
                $postCount = Post::count();
                if ($postCount > 10) {
                    if (!$this->confirm("This will generate likes for {$postCount} posts. This can take a long time and generate significant database load. Continue?")) {
                        $this->info('Command cancelled by user');
                        return 0;
                    }

                    // Allow limiting the number of posts to process
                    $limitPrompt = $this->ask('How many posts do you want to process? (Leave empty for all)', $postCount);
                    $posts = $limitPrompt < $postCount ? Post::inRandomOrder()->limit($limitPrompt)->get() : Post::all();
                } else {
                    $posts = Post::all();
                }
            }
        }

        if ($posts->isEmpty()) {
            $this->error('No posts found to process');
            return 1;
        }

        $this->info("Found {$posts->count()} posts to process");

        // Should we reset existing likes?
        if ($this->option('reset')) {
            if ($this->confirm('This will delete ALL existing likes for the selected posts. Are you sure?')) {
                $this->resetLikes($posts);
            }
        }

        // Get sample of users who will be doing the liking
        $userCount = User::count();
        $usersNeeded = max($max, 10000); // We need at least as many as our max likes setting

        if ($userCount < $usersNeeded) {
            $this->warn("You only have {$userCount} users, which is less than the ideal {$usersNeeded} for this operation.");
            if (!$this->confirm('Continue anyway? (This will result in users having multiple likes per post)')) {
                $this->info('Command cancelled by user');
                return 0;
            }
            $users = User::all();
        } else {
            // Take a random sample of users for efficiency
            $sampleSize = min($userCount, $usersNeeded);
            $this->info("Using a sample of {$sampleSize} random users from total {$userCount}");
            $users = User::inRandomOrder()->limit($sampleSize)->get();
        }

        if ($users->isEmpty()) {
            $this->error('No users found to generate likes');
            return 1;
        }

        $this->info('Starting to generate random likes for ' . $posts->count() . ' posts');
        $this->info("Using like range: {$min}-{$max} likes per post");

        // Process each post
        $progressBar = $this->output->createProgressBar($posts->count());
        $progressBar->start();

        foreach ($posts as $post) {
            $this->generateRandomLikesForPost($post, $users, $likeType);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info('Random likes generated successfully!');

        return 0;
    }

    /**
     * Ensure the Like reaction type exists
     */
    protected function ensureLikeReactionType()
    {
        try {
            $likeTypeName = 'Like';
            $likeType = ReactionType::fromName($likeTypeName);

            if (!$likeType) {
                $likeType = ReactionType::create(['name' => $likeTypeName]);
                $this->info("Created {$likeTypeName} reaction type");
            } else {
                $this->info("Using existing {$likeTypeName} reaction type");
            }

            return $likeType;
        } catch (\Exception $e) {
            $this->error('Error setting up reaction type: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Reset existing likes for the specified posts using Laravel Love's API
     */
    protected function resetLikes($posts)
    {
        $this->info('Clearing existing likes...');

        try {
            $bar = $this->output->createProgressBar(count($posts));
            $bar->start();
            $totalCleared = 0;

            foreach ($posts as $post) {
                try {
                    // Get current like count before clearing
                    $currentLikes = $post->viaLoveReactant()->getReactionCounterOfType('Like')->count ?? 0;

                    if ($currentLikes > 0) {
                        // Get all users who have liked this post and remove their reactions
                        $reactant = $post->loveReactant;
                        if ($reactant) {
                            // Remove all Like reactions for this post
                            $reactant->reactions()
                                ->whereHas('reactionType', function ($query) {
                                    $query->where('name', 'Like');
                                })
                                ->delete();

                            // Update the reaction counters
                            $post->viaLoveReactant()->decrementReactionCounterOfType('Like', $currentLikes);
                        }

                        $totalCleared += $currentLikes;
                    }
                } catch (\Exception $e) {
                    $this->warn("Error clearing likes for post {$post->id}: " . $e->getMessage());
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Cleared {$totalCleared} likes across " . $posts->count() . " posts");
        } catch (\Exception $e) {
            $this->error('Error clearing likes: ' . $e->getMessage());
        }
    }

    /**
     * Generate random likes for a post using the Laravel Love API methods
     * instead of direct database operations
     */
    protected function generateRandomLikesForPost(Post $post, $users, $likeType)
    {
        // Generate a random number of likes using the min-max range from options
        $min = (int)$this->option('min') ?: 50;
        $max = (int)$this->option('max') ?: 100;
        $likesCount = rand($min, $max);

        $this->info("Generating {$likesCount} likes for post {$post->id} '{$post->title}'");

        try {
            // Shuffle users to get a random selection
            $shuffledUsers = $users->shuffle();
            $actualCount = 0;
            $existingCount = 0;
            $typeName = 'Like'; // Use string name for reaction type

            // Use a batch approach for better performance
            $batchSize = 100;
            $batches = ceil($likesCount / $batchSize);

            for ($i = 0; $i < $batches; $i++) {
                $batchLikes = min($batchSize, $likesCount - $actualCount);

                if ($batchLikes <= 0) {
                    break;
                }

                $batchProcessed = 0;

                // Process users in this batch
                foreach ($shuffledUsers as $user) {
                    // Skip if we've already processed enough for this batch
                    if ($batchProcessed >= $batchLikes) {
                        break;
                    }

                    try {
                        // Use the reacter facade as recommended
                        $reacterFacade = $user->viaLoveReacter();

                        // Check if the user has already reacted to this post
                        if (!$reacterFacade->hasReactedTo($post, $typeName)) {
                            // Add reaction using Laravel Love's API
                            $reacterFacade->reactTo($post, $typeName);
                            $actualCount++;
                            $batchProcessed++;
                        } else {
                            $existingCount++;
                        }
                    } catch (\Exception $e) {
                        // Skip this user if there's an error
                        $this->warn("Error adding like for user {$user->id} to post {$post->id}: " . $e->getMessage());
                        continue;
                    }
                }

                // If we couldn't process any more in this batch, we've probably run out of users
                if ($batchProcessed === 0) {
                    $this->warn("Ran out of unique users after adding {$actualCount} likes");
                    break;
                }

                // Show progress for large batches
                if ($batches > 10 && ($i + 1) % 10 === 0) {
                    $this->info("Progress: " . number_format(($i + 1) / $batches * 100, 1) . "% complete");
                }
            }

            // Get the final count using Laravel Love's counting method
            $finalCount = $post->viaLoveReactant()->getReactionCounterOfType('Like')->count ?? 0;

            $this->info("Added {$actualCount} new likes to post {$post->id} (skipped {$existingCount} existing likes, total now: {$finalCount})");
        } catch (\Exception $e) {
            $this->error("Error generating likes for post {$post->id}: " . $e->getMessage());
        }
    }
}
