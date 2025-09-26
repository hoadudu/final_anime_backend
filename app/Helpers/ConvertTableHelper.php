<?php

namespace App\Helpers;

class ConvertTableHelper
{
    /**
     * Check if anime is completed based on director and time strings from old database
     *
     * @param array $strings Array containing film_director and film_time
     * @return string Status: 'Completed' or 'Ongoing'
     */
    public static function checkCompletedFromArrayStringOfOldDb(array $strings): string
    {
        // Filter out null/empty values
        $filteredStrings = array_filter($strings, function($value) {
            return !is_null($value) && trim($value) !== '';
        });

        // If no valid strings, assume ongoing
        if (empty($filteredStrings)) {
            return 'Ongoing';
        }

        // Convert to lowercase and check for completion indicators
        $combinedText = strtolower(implode(' ', $filteredStrings));

        // Check for completion keywords
        $completionKeywords = [
            'completed',
            'finished',
            'ended',
            'complete',
            'hoàn thành',
            'kết thúc',
            'đã hoàn thành'
        ];

        foreach ($completionKeywords as $keyword) {
            if (strpos($combinedText, $keyword) !== false) {
                return 'Completed';
            }
        }

        // Check for ongoing keywords
        $ongoingKeywords = [
            'ongoing',
            'continuing',
            'updating',
            'airing',
            'đang chiếu',
            'đang phát sóng',
            'đang cập nhật'
        ];

        foreach ($ongoingKeywords as $keyword) {
            if (strpos($combinedText, $keyword) !== false) {
                return 'Ongoing';
            }
        }

        // Default to Ongoing if no clear indicators
        return 'Ongoing';
    }
}
