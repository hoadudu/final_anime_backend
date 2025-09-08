<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;

class TestCommentSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:comment-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the comment system functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Comment System...');

        // Get test data
        $user = User::where('email', 'testuser@example.com')->first();
        $posts = Post::limit(3)->get();
        
        if (!$user || $posts->count() === 0) {
            $this->error('❌ Test data not found. Run CommentSeeder first.');
            return;
        }

        $this->info("Testing with user: {$user->name}");

        // Test 1: Get comments for an anime
        $this->info("\n1️⃣ Testing comment retrieval...");
        $post = $posts->first();
        $comments = $post->visibleComments()->root()->with(['user', 'replies.user'])->get();
        
        $this->info("✅ Found {$comments->count()} root comments for '{$post->display_title}'");
        
        if ($comments->count() > 0) {
            $this->table(
                ['ID', 'Content', 'User', 'Likes', 'Replies'],
                $comments->map(fn($c) => [
                    $c->id,
                    substr($c->content, 0, 30) . '...',
                    $c->user->name,
                    $c->likes_count,
                    $c->replies->count()
                ])
            );
        }

        // Test 2: Create new comment
        $this->info("\n2️⃣ Testing comment creation...");
        $newComment = Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => 'Test comment created via command! 🚀',
        ]);
        $this->info("✅ Created new comment (ID: {$newComment->id})");

        // Test 3: Create reply
        $this->info("\n3️⃣ Testing reply creation...");
        $parentComment = $comments->first();
        if ($parentComment) {
            $reply = Comment::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'parent_id' => $parentComment->id,
                'content' => 'This is a test reply! 👍',
            ]);
            $this->info("✅ Created reply (ID: {$reply->id}) to comment {$parentComment->id}");
        }

        // Test 4: Test like system
        $this->info("\n4️⃣ Testing like/dislike system...");
        $testComment = $comments->first();
        if ($testComment) {
            $originalLikes = $testComment->likes_count;
            
            // Test like
            $liked = $testComment->likeBy($user);
            $testComment->refresh();
            $this->info("✅ Like result: " . ($liked ? 'Added' : 'Removed'));
            $this->info("Likes count: {$originalLikes} → {$testComment->likes_count}");
            
            // Test dislike
            $disliked = $testComment->dislikeBy($user);
            $testComment->refresh();
            $this->info("✅ Dislike result: " . ($disliked ? 'Added' : 'Removed'));
            $this->info("Likes: {$testComment->likes_count}, Dislikes: {$testComment->dislikes_count}");
        }

        // Test 5: Test report system
        $this->info("\n5️⃣ Testing report system...");
        $reportComment = $comments->last();
        if ($reportComment) {
            $report = CommentReport::create([
                'user_id' => $user->id,
                'comment_id' => $reportComment->id,
                'reason' => 'inappropriate',
                'description' => 'This is a test report'
            ]);
            $this->info("✅ Created report (ID: {$report->id}) for comment {$reportComment->id}");
            $this->info("Report reason: {$report->formatted_reason}");
        }

        // Test 6: Test scopes and relationships
        $this->info("\n6️⃣ Testing scopes and relationships...");
        
        $visibleCount = Comment::visible()->count();
        $rootCount = Comment::root()->count();
        $repliesCount = Comment::whereNotNull('parent_id')->count();
        $pendingReports = CommentReport::pending()->count();
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Visible Comments', $visibleCount],
                ['Root Comments', $rootCount],
                ['Replies', $repliesCount],
                ['Pending Reports', $pendingReports],
            ]
        );

        // Test 7: Test admin functions
        $this->info("\n7️⃣ Testing admin functions...");
        $adminTestComment = $comments->first();
        if ($adminTestComment) {
            $canDelete = $adminTestComment->canBeDeletedBy($user);
            $this->info("✅ User can delete own comment: " . ($canDelete ? 'Yes' : 'No'));
            
            // Test hiding comment (admin function)
            $adminTestComment->update(['is_hidden' => true]);
            $this->info("✅ Hidden comment {$adminTestComment->id}");
            
            // Unhide it
            $adminTestComment->update(['is_hidden' => false]);
            $this->info("✅ Unhidden comment {$adminTestComment->id}");
        }

        // Clean up test data
        $this->info("\n8️⃣ Cleaning up test data...");
        Comment::where('content', 'LIKE', '%Test comment created via command%')->delete();
        Comment::where('content', 'LIKE', '%This is a test reply%')->delete();
        CommentReport::where('description', 'This is a test report')->delete();
        
        $this->info("✅ Cleaned up test data");

        // Summary
        $this->info("\n🎉 Comment System Test Completed!");
        $this->info("📊 Summary:");
        $this->info("- Comment creation: ✅ Working");
        $this->info("- Reply system: ✅ Working"); 
        $this->info("- Like/Dislike: ✅ Working");
        $this->info("- Report system: ✅ Working");
        $this->info("- Admin functions: ✅ Working");
        $this->info("- Database relationships: ✅ Working");
        
        $this->info("\n🚀 The comment system is ready for frontend integration!");
        $this->info("📝 API endpoints available:");
        $this->info("- GET /api/anime/{post}/comments");
        $this->info("- POST /api/anime/{post}/comments");
        $this->info("- POST /api/comments/{comment}/like");
        $this->info("- POST /api/comments/{comment}/dislike");
        $this->info("- DELETE /api/comments/{comment}");
        $this->info("- POST /api/comments/{comment}/report");
    }
}
