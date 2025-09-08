@if($imageUrl)
<div class="group relative w-full">
    <div class="flex items-center justify-center w-full h-32 border-2 border-gray-200 rounded-xl bg-gray-50 dark:bg-gray-800 dark:border-gray-600 overflow-hidden transition-all duration-200 hover:border-primary-500 hover:shadow-lg">
        <img 
            src="{{ $imageUrl }}" 
            alt="Preview" 
            class="max-h-full max-w-full object-contain rounded-lg transition-transform duration-200 group-hover:scale-105"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
        />
        <!-- <div class="hidden flex-col items-center justify-center text-gray-400 text-sm p-4">
            <svg class="w-12 h-12 mb-2 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-center">❌ Không thể tải ảnh</span>
            <span class="text-xs text-gray-500 mt-1">Kiểm tra lại URL</span>
        </div> -->
    </div>
    
    <!-- Badge hiển thị kích thước ảnh (nếu có thể) -->
    <!-- <div class="absolute top-2 right-2 bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200">
        🖼️ Ảnh
    </div> -->
</div>
@else
<div class="flex items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 dark:bg-gray-800 dark:border-gray-600 transition-all duration-200 hover:border-primary-400">
    <div class="flex flex-col items-center justify-center text-gray-400 text-sm p-4">
        <svg class="w-12 h-12 mb-2 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-center font-medium">📷 Chưa có ảnh</span>
        <span class="text-xs text-gray-500 mt-1">Nhập URL để xem trước</span>
    </div>
</div>
@endif
