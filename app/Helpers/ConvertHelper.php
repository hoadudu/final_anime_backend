<?php

namespace App\Helpers;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;

class ConvertHelper
{
    /**
     * Chuyển đổi chuỗi broadcast từ API sang timestamp
     * 
     * @param array|null $broadcast Mảng thông tin broadcast từ API
     * @return string|null Timestamp hoặc null nếu không có dữ liệu hợp lệ
     */
    public static function broadcastToTimestamp($broadcast)
    {
        // Kiểm tra nếu có dữ liệu broadcast
        if (empty($broadcast) || empty($broadcast['day']) || empty($broadcast['time'])) {
            return null;
        }

        // Lấy thông tin từ broadcast
        $day = $broadcast['day'] ?? null;
        $time = $broadcast['time'] ?? null;
        $timezone = $broadcast['timezone'] ?? 'Asia/Tokyo';

        // Nếu thiếu thông tin cần thiết
        if (empty($day) || empty($time)) {
            return null;
        }

        try {
            // Xác định ngày của tuần
            $dayMap = [
                'Mondays' => 1,
                'Tuesdays' => 2,
                'Wednesdays' => 3,
                'Thursdays' => 4,
                'Fridays' => 5,
                'Saturdays' => 6,
                'Sundays' => 0,
            ];
            
            $dayOfWeek = $dayMap[$day] ?? null;
            
            if ($dayOfWeek === null) {
                return null;
            }
            
            // Tạo đối tượng Carbon cho ngày hiện tại
            $date = Carbon::now($timezone);
            
            // Điều chỉnh ngày đến ngày trong tuần cần tìm
            $date->startOfWeek()->addDays($dayOfWeek);
            
            // Thiết lập thời gian
            list($hour, $minute) = explode(':', $time);
            $date->setHour((int)$hour)->setMinute((int)$minute)->setSecond(0);
            
            return $date->toDateTimeString();
        } catch (\Exception $e) {
            // Log lỗi nếu cần
            return null;
        }
    }

    /**
     * Phân tích chuỗi broadcast từ API
     * Ví dụ: "Sundays at 01:58 (JST)"
     * 
     * @param string|null $broadcastString
     * @return array|null
     */
    public static function parseBroadcastString($broadcastString) 
    {
        if (empty($broadcastString)) {
            return null;
        }
        
        try {
            // Pattern để bắt "Sundays at 01:58 (JST)"
            preg_match('/^(\w+)s at (\d{2}:\d{2}) \((\w+)\)$/', $broadcastString, $matches);
            
            if (count($matches) < 4) {
                return null;
            }
            
            $day = $matches[1] . 's'; // "Sunday" + "s"
            $time = $matches[2]; // "01:58"
            $timezoneCode = $matches[3]; // "JST"
            
            // Map mã timezone sang tên timezone
            $timezoneMap = [
                'JST' => 'Asia/Tokyo',
                'EST' => 'America/New_York',
                'PST' => 'America/Los_Angeles',
                'UTC' => 'UTC',
                'GMT' => 'GMT',
            ];
            
            $timezone = $timezoneMap[$timezoneCode] ?? 'UTC';
            
            return [
                'day' => $day,
                'time' => $time,
                'timezone' => $timezone,
                'string' => $broadcastString
            ];
        } catch (\Exception $e) {
            // Log lỗi nếu cần
            return null;
        }
    }

    /**
     * Convert từ broadcast string trực tiếp sang timestamp
     * 
     * @param string|null $broadcastString
     * @return string|null
     */
    public static function broadcastStringToTimestamp($broadcastString)
    {
        $broadcast = self::parseBroadcastString($broadcastString);
        return $broadcast ? self::broadcastToTimestamp($broadcast) : null;
    }
}
