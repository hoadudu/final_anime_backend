<?php

//ISO 639-1 language codes array
namespace App\Helpers;
use \App\Models\Post;

class PostHelper
{
    /**
     * Get the poster image URL for a given post.
     * If no poster image is found, return a default placeholder URL.
     *
     * @param  \App\Models\Post  $post
     * @return string
     */
    public static function getPosterImageUrl($post)
    {
        $posterImage = $post->images->where('image_type', 'poster')->first();
        return $posterImage ? $posterImage->image_url : 'https://cdn.noitatnemucod.net/thumbnail/300x400/100/default.jpg';
    }

    /**
     * Get the status label for a given post.
     *
     * @param  \App\Models\Post  $post
     * @return string
     */
    public static function getStatusLabel($post,$lang='en')
    {
        // Assuming 'status' is a field in the posts table
        $en_status = ['Completed','Ongoing','Upcoming','Dropped'];
        $vi_status = ['Hoàn thành','Đang phát sóng','Sắp phát hành','Đã hủy'];
        $status = $post->status ?? 'Unknown';

        // Translate status if a translation exists
        if ($lang === 'vi') {
            $status = $vi_status[array_search($status, $en_status)] ?? $status;
        }

        return $status;
    }
    
    public function getLocalizedTitle($post, $lang = 'en')
    {
        // If language is Vietnamese, try to find Vietnamese title
        if ($lang === 'vi' && $post->relationLoaded('titles')) {
            $vietnameseTitle = $post->titles->first(function ($title) {
                return $title->language === 'vi';
            });

            if ($vietnameseTitle) {
                return $vietnameseTitle->title;
            }
        }

        // Fallback to default title or display title
        return $post->title ?? $post->getDisplayTitleAttribute() ?? 'Unknown Title';
    }
    public function cleanHtmlTags($text)
    {
        return strip_tags($text);
    }
    
    public function formatTitles($titles): array
    {
        return $titles->map(function($title) {
            $lang = trim((string) ($title->language ?? ''));
            $name = $lang === '' ? null : LanguageCodeHelper::getName($lang);
            
            return [
                'language' => $name ?: 'Synonyms',
                'title' => $title->title,
            ];
        })->toArray();
    }
}