<?php

namespace App\Console\Commands\Update;

use App\Models\Post;
use App\Models\AnimeGroup;
use App\Models\AnimeGroupPost;
use Illuminate\Console\Command;

class UpdateGroupAnimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-group-animes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update anime groups from film_tag';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $posts = Post::where('film_tag', '<>', '')->select('id', 'film_tag')->get();

        foreach ($posts as $post) {
            $this->processFilmTag($post);
        }

        $this->info('Done processing all posts.');
    }

    private function processFilmTag(Post $post)
    {
        $filmTag = $post->film_tag;
        if (empty($filmTag)) {
            return;
        }

        $parts = explode(',', $filmTag);
        $group = $this->createGroupFromFirstPart($parts);

        if (!$group) {
            $this->warn("Could not create group for post {$post->id}");
            return;
        }

        foreach ($parts as $index => $part) {
            try {
                $part = trim($part);
                if (strpos($part, '=') === false) {
                    continue;
                }

                list($note, $filmId) = explode('=', $part, 2);
                $note = trim($note);
                $filmId = trim($filmId);

                if (!is_numeric($filmId)) {
                    continue;
                }

                $targetPost = Post::where('film_id', $filmId)->first();
                if (!$targetPost) {
                    $this->warn("Post with film_id {$filmId} not found for part '{$part}'");
                    continue;
                }

                AnimeGroupPost::updateOrCreate(
                    [
                        'group_id' => $group->id,
                        'post_id' => $targetPost->id,
                    ],
                    [
                        'position' => $index + 1,
                        'note' => $note,
                    ]
                );
            } catch (\Exception $e) {
                $this->error("Error processing part '{$part}' for post {$post->id}: " . $e->getMessage());
                continue;
            }
        }
    }

    private function createGroupFromFirstPart($parts)
    {
        foreach ($parts as $part) {
            $part = trim($part);
            if (strpos($part, '=') === false) {
                continue;
            }

            list($note, $filmId) = explode('=', $part, 2);
            $filmId = trim($filmId);

            if (!is_numeric($filmId)) {
                continue;
            }

            $groupPost = Post::where('film_id', $filmId)->first();
            if (!$groupPost) {
                continue;
            }

            $groupName = $groupPost->getDisplayTitleAttribute();
            if (!$groupName) {
                $groupName = 'No Title';
            }

            $group = AnimeGroup::firstOrCreate(
                ['name' => $groupName],
                ['description' => '']
            );
            return $group;
        }
        return null;
    }
}
