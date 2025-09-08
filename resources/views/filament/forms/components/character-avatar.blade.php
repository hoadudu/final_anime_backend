@php
    $record = $getRecord();
    $imageUrl = null;

    // Try to get image from different sources
    if ($record && $record->images) {
        if (isset($record->images['jpg']['image_url'])) {
            $imageUrl = $record->images['jpg']['image_url'];
        } elseif (isset($record->images['webp']['image_url'])) {
            $imageUrl = $record->images['webp']['image_url'];
        }
    }

    // If no record image, try to get from form state
    if (!$imageUrl) {
        $state = $getState();
        if (isset($state['images']['jpg']['image_url'])) {
            $imageUrl = $state['images']['jpg']['image_url'];
        } elseif (isset($state['images']['webp']['image_url'])) {
            $imageUrl = $state['images']['webp']['image_url'];
        }
    }
@endphp

@if($imageUrl)
<div class="flex items-center space-x-4">
    <div class="relative">
        <img
            src="{{ $imageUrl }}"
            alt="Character Avatar"
            class="w-20 h-20 rounded-full object-cover border-2 border-gray-200 shadow-sm"
            onerror="this.style.display='none'"
        >
        <div class="absolute inset-0 rounded-full bg-gray-100 flex items-center justify-center" style="display: none;">
            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
            </svg>
        </div>
    </div>
    <div class="text-sm text-gray-600">
        <p class="font-medium">Character Avatar</p>
        <p>Preview of the selected image</p>
    </div>
</div>
@else
<div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
    <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center">
        <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
        </svg>
    </div>
    <div class="text-sm text-gray-500">
        <p class="font-medium">No Avatar Available</p>
        <p>Add an image URL above to see the preview</p>
    </div>
</div>
@endif
