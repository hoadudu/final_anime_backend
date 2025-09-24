<?php

namespace App\Console\Commands\ConvertDatabase;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Post;
use voku\helper\UTF8;

class ConvertTableHelper
{
    /**
     * Generate a unique slug from a base string
     */
    public static function generateUniqueSlug(string $baseSlug, string $modelClass = Post::class): string
    {
        // Clean and create slug
        $slug = Str::slug($baseSlug);

        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;

        while ($modelClass::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Map status from numeric code to string
     */
    public static function mapStatus(?string $statusCode): string
    {
        // Completed','Ongoing','Upcoming','Dropped'
        return match ($statusCode) {
            '1' => 'Completed',
            '2' => 'Ongoing',
            '3' => 'Upcoming',
            default => 'Dropped'
        };
    }

    /**
     * Map type from numeric code to string
     */
    public static function mapType(?string $typeCode): string
    {
        return match ($typeCode) {
            '1' => 'tv',
            '2' => 'movie',
            '3' => 'ova',
            '4' => 'ona',
            '5' => 'special',
            default => 'tv' // Default to TV series
        };
    }

    /**
     * Map rating system
     */
    public static function mapRating(?string $rating): ?string
    {
        // Assuming rating is a string like 'PG-13', 'R', etc.
        return $rating ? strtoupper($rating) : null;
    }

    /**
     * Parse date from various formats
     */
    public static function parseDate(?string $date): ?string
    {
        if (!$date) return null;

        try {
            // Try different date formats
            $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y/m/d'];

            foreach ($formats as $format) {
                $parsed = \DateTime::createFromFormat($format, $date);
                if ($parsed) {
                    return $parsed->format('Y-m-d');
                }
            }

            // If no format matches, try to parse as timestamp
            if (is_numeric($date)) {
                return date('Y-m-d', (int)$date);
            }
        } catch (\Exception $e) {
            // Log error but continue
        }

        return null;
    }

    /**
     * Parse datetime from various formats
     */
    public static function parseDateTime(?string $dateTime): ?string
    {
        if (!$dateTime) return null;

        try {
            // Try different datetime formats
            $formats = [
                'Y-m-d H:i:s',
                'd/m/Y H:i:s',
                'Y-m-d',
                'd/m/Y'
            ];

            foreach ($formats as $format) {
                $parsed = \DateTime::createFromFormat($format, $dateTime);
                if ($parsed) {
                    return $parsed->format('Y-m-d H:i:s');
                }
            }

            // If no format matches, try to parse as timestamp
            if (is_numeric($dateTime)) {
                return date('Y-m-d H:i:s', (int)$dateTime);
            }
        } catch (\Exception $e) {
            // Log error but continue
        }

        return null;
    }

    /**
     * Extract MAL ID from URL
     */
    public static function getMalIdFromUrl(?string $url): ?int
    {
        if (!$url) return null;

        // Example URL: https://myanimelist.net/anime/5114/Fullmetal_Alchemist__Brotherhood
        if (preg_match('/myanimelist\.net\/anime\/(\d+)/', $url, $matches)) {
            return (int)$matches[1];
        }

        return null;
    }

    /**
     * Clean and decode synopsis text
     */
    public static function cleanSynopsis(?string $text): ?string
    {
        if (!$text) return null;

        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Nếu sau khi decode lần 1 vẫn còn chứa entity → decode thêm lần 2
        if (preg_match('/&[a-zA-Z0-9#]+;/', $decoded)) {
            $decoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $decoded;
    }

    /**
     * Fix encoding issues in text
     */
    public static function fixEncoding(?string $text): ?string
    {
        if (!$text) return null;

        // Use UTF8 helper to fix encoding
        return UTF8::fix_simple_utf8($text);
    }

    /**
     * Generic data transformation helper
     * Can be extended for specific table conversions
     */
    public static function transformData(array $data, array $mappings): array
    {
        $transformed = [];

        foreach ($mappings as $newKey => $oldKey) {
            if (is_callable($oldKey)) {
                $transformed[$newKey] = $oldKey($data);
            } elseif (is_array($oldKey)) {
                // Handle nested mappings or transformations
                $transformed[$newKey] = self::transformData($data, $oldKey);
            } else {
                $transformed[$newKey] = $data[$oldKey] ?? null;
            }
        }

        return $transformed;
    }

    /**
     * Check if record already exists based on unique field
     */
    public static function recordExists(string $modelClass, string $field, $value): bool
    {
        return $modelClass::where($field, $value)->exists();
    }

    /**
     * Batch insert or update records
     */
    public static function batchUpsert(string $modelClass, array $records, array $uniqueBy, array $updateFields = []): void
    {
        $modelClass::upsert($records, $uniqueBy, $updateFields);
    }
    public static function checkCompletedFromArrayStringOfOldDb($arrString)
    {
        if (empty($arrString)) {
            return 'Ongoing';
        }

        foreach ($arrString as $value) {
            if (empty($value)) {
                continue;
            }

            $normalized = strtolower(trim($value));

            // 1. Trường hợp từ khóa "completed" hoặc "full"
            if (in_array($normalized, ['completed', 'full'], true)) {
                return 'Completed';
            }

            // 2. Trường hợp có ký tự "/" => tách 2 bên và so sánh
            if (strpos($normalized, '/') !== false) {
                [$left, $right] = array_map('trim', explode('/', $normalized, 2));

                if (!empty($left) && !empty($right)) {
                    // Nếu 2 bên giống hệt (text thường)
                    if ($left === $right) {
                        return 'Completed';
                    }

                    // Nếu 2 bên là số => so sánh số
                    if (is_numeric($left) && is_numeric($right) && intval($left) === intval($right)) {
                        return 'Completed';
                    }
                }
            }

            // 3. Trường hợp có khoảng trắng => ví dụ "01 BD / 01 BD"
            if (preg_match('/^(.+)\s*\/\s*(.+)$/', $normalized, $matches)) {
                $left  = trim($matches[1]);
                $right = trim($matches[2]);

                if ($left === $right) {
                    return 'Completed';
                }
            }
        }

        return 'Ongoing';
    }
}
