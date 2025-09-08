<div class="space-y-6">
    {{-- Genres Section --}}
    @if($genres && $genres->count() > 0)
        <div class="border rounded-lg p-4 bg-green-50 dark:bg-green-900/20">
            <h3 class="text-lg font-semibold mb-3 text-green-800 dark:text-green-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Genres ({{ $genres->count() }})
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($genres as $genre)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                        {{ $genre->name }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Explicit Genres Section --}}
    @if($explicit_genres && $explicit_genres->count() > 0)
        <div class="border rounded-lg p-4 bg-red-50 dark:bg-red-900/20">
            <h3 class="text-lg font-semibold mb-3 text-red-800 dark:text-red-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                Explicit Genres ({{ $explicit_genres->count() }})
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($explicit_genres as $genre)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                        {{ $genre->name }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Themes Section --}}
    @if($themes && $themes->count() > 0)
        <div class="border rounded-lg p-4 bg-blue-50 dark:bg-blue-900/20">
            <h3 class="text-lg font-semibold mb-3 text-blue-800 dark:text-blue-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                </svg>
                Themes ({{ $themes->count() }})
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($themes as $theme)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                        {{ $theme->name }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Demographics Section --}}
    @if($demographics && $demographics->count() > 0)
        <div class="border rounded-lg p-4 bg-purple-50 dark:bg-purple-900/20">
            <h3 class="text-lg font-semibold mb-3 text-purple-800 dark:text-purple-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                </svg>
                Demographics ({{ $demographics->count() }})
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($demographics as $demographic)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100">
                        {{ $demographic->name }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- No Genres Message --}}
    @if((!$genres || $genres->count() === 0) && (!$explicit_genres || $explicit_genres->count() === 0) && (!$themes || $themes->count() === 0) && (!$demographics || $demographics->count() === 0))
        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No Genres Assigned</h3>
            <p class="text-gray-600 dark:text-gray-400">
                This post doesn't have any genres, themes, or demographics assigned yet. 
                Genres will be automatically imported when importing anime data from the API.
            </p>
        </div>
    @endif
</div>
