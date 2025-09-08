<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get test user and some posts
        $user = User::where('email', 'testuser@example.com')->first();
        $posts = Post::limit(3)->get();
        
        if (!$user || $posts->count() === 0) {
            $this->command->warn('Please run UserAnimeListSeeder first and ensure you have anime posts.');
            return;
        }
        
        // Sample comments in Vietnamese for anime
        $sampleComments = [
            'Anime này hay quá! Tôi rất thích cách họ phát triển nhân vật.',
            'Tập này cực kỳ hấp dẫn, không thể ngừng xem được!',
            'Animation quality rất tốt, đáng xem!',
            'Cốt truyện khá thú vị, mong chờ tập tiếp theo.',
            'Nhạc phim hay lắm, OST rất đỉnh!',
            'Voice acting cũng rất ổn, diễn viên lồng tiếng giỏi.',
            'Fight scene trong tập này quá đỉnh!',
            'Cảm động quá, suýt khóc rồi 😭',
            'Plot twist bất ngờ thật, không ngờ được!',
            'Art style đẹp tuyệt vời, màu sắc rất bắt mắt.',
        ];
        
        $sampleReplies = [
            'Mình cũng nghĩ vậy!',
            'Đồng ý luôn!',
            'Chuẩn không cần chỉnh!',
            'Tôi thấy bình thường thôi...',
            'Có thể xem được, không quá xuất sắc.',
            'Hay đấy, thanks for sharing!',
            'Spoiler warning nhé các bạn!',
            'Tập sau sẽ còn hay hơn nữa!',
        ];
        
        foreach ($posts as $post) {
            $this->command->info("Creating comments for: {$post->display_title}");
            
            // Create 3-5 root comments per post
            $rootCommentCount = rand(3, 5);
            
            for ($i = 0; $i < $rootCommentCount; $i++) {
                $comment = Comment::create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                    'parent_id' => null,
                    'content' => $sampleComments[array_rand($sampleComments)],
                    'likes_count' => rand(0, 15),
                    'dislikes_count' => rand(0, 3),
                ]);
                
                // Add some likes for the comment (just one per user to avoid unique constraint)
                if ($comment->likes_count > 0) {
                    CommentLike::create([
                        'user_id' => $user->id,
                        'comment_id' => $comment->id,
                        'type' => 'like'
                    ]);
                }
                
                // Create 1-3 replies for some comments
                if (rand(1, 3) <= 2) { // 66% chance of having replies
                    $replyCount = rand(1, 3);
                    
                    for ($k = 0; $k < $replyCount; $k++) {
                        $reply = Comment::create([
                            'user_id' => $user->id,
                            'post_id' => $post->id,
                            'parent_id' => $comment->id,
                            'content' => $sampleReplies[array_rand($sampleReplies)],
                            'likes_count' => rand(0, 5),
                            'dislikes_count' => rand(0, 1),
                        ]);
                    }
                }
            }
        }
        
        $totalComments = Comment::count();
        $this->command->info("✅ Created {$totalComments} comments successfully!");
        $this->command->info("You can now test the comment API endpoints.");
    }
}
