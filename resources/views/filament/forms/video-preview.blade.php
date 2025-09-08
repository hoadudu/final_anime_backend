@php
    $isYouTube = $videoUrl && (str_contains($videoUrl, 'youtube.com') || str_contains($videoUrl, 'youtu.be'));
    $isVimeo = $videoUrl && str_contains($videoUrl, 'vimeo.com');
    $videoId = '';
    $thumbnailUrl = '';
    
    if ($isYouTube) {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $videoUrl, $matches);
        $videoId = $matches[1] ?? '';
        $thumbnailUrl = $videoId ? "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg" : '';
    } elseif ($isVimeo) {
        preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $matches);
        $videoId = $matches[1] ?? '';
        // For Vimeo, we'd need API call to get thumbnail, so we'll use a placeholder
    }
@endphp

@if($videoUrl)
<div class="group relative w-full">
    <div class="flex items-center justify-center w-full h-40 border-2 border-gray-200 rounded-xl bg-gray-50 dark:bg-gray-800 dark:border-gray-600 overflow-hidden transition-all duration-200 hover:border-primary-500 hover:shadow-lg">
        @if($isYouTube && $thumbnailUrl)
            <div class="relative w-full h-full">
                <img 
                    src="{{ $thumbnailUrl }}" 
                    alt="Video thumbnail" 
                    class="w-full h-full object-cover rounded-lg transition-transform duration-200 group-hover:scale-105"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                />
                <!-- Play button overlay -->
                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 group-hover:bg-opacity-50 transition-all duration-200">
                    <div class="bg-red-600 rounded-full p-3 group-hover:scale-110 transition-transform duration-200 shadow-lg">
                        <svg class="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                </div>
                <!-- YouTube badge -->
                <div class="absolute bottom-2 right-2 bg-red-600 text-white text-xs px-2 py-1 rounded-full font-medium">
                    📺 YouTube
                </div>
            </div>
        @elseif($isVimeo)
            <div class="relative w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-400 to-purple-600">
                <div class="text-center text-white">
                    <svg class="w-16 h-16 mx-auto mb-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.977 6.416c-.105 2.338-1.739 5.543-4.894 9.609-3.268 4.247-6.026 6.37-8.29 6.37-1.409 0-2.578-1.294-3.553-3.881L5.322 11.4C4.603 8.816 3.834 7.522 3.01 7.522c-.179 0-.806.378-1.881 1.132L0 7.197c1.185-1.044 2.351-2.084 3.501-3.128C5.08 2.701 6.266 1.984 7.055 1.91c1.867-.18 3.016 1.1 3.447 3.838.465 2.953.789 4.789.971 5.507.539 2.45 1.131 3.674 1.776 3.674.502 0 1.256-.796 2.265-2.385 1.004-1.589 1.54-2.797 1.612-3.628.144-1.371-.395-2.061-1.614-2.061-.574 0-1.167.121-1.777.391 1.186-3.868 3.434-5.757 6.762-5.637 2.473.06 3.628 1.664 3.493 4.797l-.013.01z"/>
                    </svg>
                    <p class="font-medium">Vimeo Video</p>
                    <p class="text-xs opacity-75">ID: {{ $videoId }}</p>
                </div>
            </div>
        @else
            <!-- For direct video URLs or other platforms -->
            <div class="relative w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-400 to-gray-600">
                <div class="text-center text-white">
                    <svg class="w-16 h-16 mx-auto mb-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17 10.5V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3.5l4 4v-11l-4 4z"/>
                    </svg>
                    <p class="font-medium">Video File</p>
                    <p class="text-xs opacity-75">{{ parse_url($videoUrl, PHP_URL_HOST) ?: 'Direct URL' }}</p>
                </div>
            </div>
        @endif
        
        <!-- Error fallback -->
        <div class="hidden flex-col items-center justify-center text-gray-400 text-sm p-4">
            <svg class="w-12 h-12 mb-2 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17 10.5V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3.5l4 4v-11l-4 4z"/>
            </svg>
            <span class="text-center">❌ Không thể tải video</span>
            <span class="text-xs text-gray-500 mt-1">Kiểm tra lại URL</span>
        </div>
    </div>
    
    <!-- Video info overlay -->
    <div class="absolute top-2 left-2 bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200">
        🎬 {{ $isYouTube ? 'YouTube' : ($isVimeo ? 'Vimeo' : 'Video') }}
    </div>
</div>
@else
<div class="flex items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 dark:bg-gray-800 dark:border-gray-600 transition-all duration-200 hover:border-primary-400">
    <div class="flex flex-col items-center justify-center text-gray-400 text-sm p-4">
        <svg class="w-12 h-12 mb-2 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17 10.5V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3.5l4 4v-11l-4 4z"/>
        </svg>
        <span class="text-center font-medium">🎬 Chưa có video</span>
        <span class="text-xs text-gray-500 mt-1">Nhập URL để xem trước</span>
    </div>
</div>
@endif
