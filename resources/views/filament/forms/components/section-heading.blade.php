<div class="px-4 py-2 mb-2">
    <h3 class="text-lg font-medium">
        {{ $heading ?? 'Section heading' }}
    </h3>
    @if(isset($description))
    <p class="text-sm text-gray-500">
        {{ $description }}
    </p>
    @endif
</div>
