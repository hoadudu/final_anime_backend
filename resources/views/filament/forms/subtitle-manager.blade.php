@php
    $uploadService = app(\App\Services\SubtitleUploadService::class);
    $directoryFiles = $post ? $uploadService->getDirectoryFiles($post) : [];
    $subtitleFiles = collect($directoryFiles)->filter(fn($file) => $file['is_subtitle']);
@endphp

<div x-data="subtitleManager" x-init="init()" class="w-full space-y-6">
    <!-- Header -->
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2M7 4h10M7 4l-2 16h14l-2-16M10 9v6M14 9v6"></path>
            </svg>
            Subtitle Management
        </h3>
        @if($post)
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                <span class="inline-flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0"></path>
                    </svg>
                    {{ $post->getSubtitleDirectory() }}
                </span>
            </p>
        @endif
    </div>

    <!-- Current Subtitles Section -->
    @if($subtitles && $subtitles->count() > 0)
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Active Subtitles
                    <span class="ml-2 px-2 py-1 text-xs bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200 rounded-full">
                        {{ $subtitles->count() }}
                    </span>
                </h4>
            </div>
            <div class="grid gap-3">
                @foreach($subtitles as $subtitle)
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-sm">
                                        {{ strtoupper($subtitle->language) }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ basename($subtitle->file_path ?? $subtitle->url) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ ucfirst($subtitle->type) }} • {{ ucfirst($subtitle->source) }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($subtitle->is_default)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Default
                                        </span>
                                    @endif
                                    @if($subtitle->is_active)
                                        <span class="w-2 h-2 bg-green-400 rounded-full" title="Active"></span>
                                    @else
                                        <span class="w-2 h-2 bg-gray-300 rounded-full" title="Inactive"></span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <button type="button" 
                                        @click="editSubtitle({{ $subtitle->id }})"
                                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800 transition-colors duration-150">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit
                                </button>
                                <button type="button" 
                                        @click="deleteSubtitle({{ $subtitle->id }})"
                                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800 transition-colors duration-150">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="text-center py-8 bg-gray-50 dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">No subtitles yet</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Upload subtitle files and scan the directory to create subtitle records.</p>
        </div>
    @endif

    <!-- File Manager Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
            <div class="flex items-center justify-between">
                <h4 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0"></path>
                    </svg>
                    File Manager
                    <span class="ml-2 px-2 py-1 text-xs bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-full">
                        {{ count($directoryFiles) }} files
                    </span>
                </h4>
                <div class="flex items-center space-x-3">
                    <button type="button" 
                            @click="refreshFiles()"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                    <button type="button" 
                            @click="showUpload = !showUpload"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <span x-text="showUpload ? 'Cancel Upload' : 'Upload Files'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Upload Section -->
        <div x-show="showUpload" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="p-6 bg-gradient-to-br from-indigo-50 via-blue-50 to-purple-50 dark:from-indigo-900/20 dark:via-blue-900/20 dark:to-purple-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg">
            
            <div class="space-y-4">
                <!-- Drag & Drop Area -->
                <div class="relative">
                    <input type="file" 
                           multiple 
                           accept=".srt,.vtt,.ass,.ssa,.txt"
                           @change="files = $event.target.files"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                           id="fileUpload">
                    
                    <label for="fileUpload" 
                           class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-indigo-300 dark:border-indigo-600 rounded-lg cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors duration-200"
                           :class="files.length > 0 ? 'bg-indigo-100 dark:bg-indigo-900/40 border-indigo-400' : 'bg-white dark:bg-gray-800'">
                        
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-8 h-8 mb-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="mb-2 text-sm text-indigo-600 dark:text-indigo-400">
                                <span class="font-semibold">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-xs text-indigo-500 dark:text-indigo-400">
                                SRT, VTT, ASS, SSA, TXT files
                            </p>
                        </div>
                    </label>
                </div>
                
                <!-- Selected Files -->
                <div x-show="files.length > 0" 
                     x-transition
                     class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span x-text="`${files.length} file(s) selected`"></span>
                    </h5>
                    <div class="text-xs text-gray-500 dark:text-gray-400" x-show="files.length > 0">
                        <template x-for="file in Array.from(files)" :key="file.name">
                            <div class="flex items-center justify-between py-1">
                                <span x-text="file.name"></span>
                                <span x-text="(file.size / 1024).toFixed(1) + ' KB'"></span>
                            </div>
                        </template>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-indigo-200 dark:border-indigo-800">
                    <button type="button" 
                            @click="showUpload = false; files = []; document.getElementById('fileUpload').value = ''"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel
                    </button>
                    <button type="button" 
                            @click="uploadFiles()" 
                            :disabled="files.length === 0 || uploading"
                            class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-150">
                        <svg x-show="!uploading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <svg x-show="uploading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="uploading ? 'Uploading...' : 'Upload Files'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Directory Files -->
        <div class="p-6">
            @forelse($directoryFiles as $index => $file)
                <div class="flex items-center justify-between p-4 {{ $loop->last ? '' : 'border-b border-gray-100 dark:border-gray-700' }} hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors duration-150 group">
                    <div class="flex items-center space-x-4 flex-1 min-w-0">
                        <!-- File Icon -->
                        <div class="flex-shrink-0">
                            @if($file['is_subtitle'])
                                <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="w-10 h-10 bg-gradient-to-br from-gray-400 to-gray-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <!-- File Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $file['name'] }}
                                </p>
                                @if($file['is_subtitle'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Subtitle
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center space-x-4 mt-1">
                                <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $file['size'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $file['modified'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                        <button type="button" 
                                @click="renameFile('{{ $file['name'] }}')"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800 transition-colors duration-150">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Rename
                        </button>
                        <button type="button" 
                                @click="deleteFile('{{ $file['name'] }}')"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800 transition-colors duration-150">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0"></path>
                    </svg>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">No files found</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                        Upload subtitle files using the button above to get started with subtitle management.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
    </div>

    <!-- Create Subtitles Action -->
    @if($subtitleFiles->count() > 0)
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-6 border border-green-200 dark:border-green-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                            Ready to Create Subtitles
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Found {{ $subtitleFiles->count() }} subtitle file(s) ready to be processed into subtitle records.
                        </p>
                    </div>
                </div>
                <button type="button" 
                        @click="scanAndCreateSubtitles()"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Create {{ $subtitleFiles->count() }} Subtitle(s)
                </button>
            </div>
        </div>
    @endif

    <!-- Alpine.js Script -->
    <script>
document.addEventListener('alpine:init', () => {
    Alpine.data('subtitleManager', () => ({
        showUpload: false,
        uploading: false,
        files: [],
        streamId: {{ $stream?->id ?? 'null' }},
        postId: {{ $post?->id ?? 'null' }},
        
        init() {
            // Initialization if needed
        },
        
        async uploadFiles() {
            if (this.files.length === 0 || !this.postId) return;
            
            this.uploading = true;
            const formData = new FormData();
            
            Array.from(this.files).forEach((file, index) => {
                formData.append(`files[${index}]`, file);
            });
            
            formData.append('_token', '{{ csrf_token() }}');
            
            try {
                const response = await fetch(`/admin/posts/${this.postId}/upload-subtitles`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.files = [];
                    this.showUpload = false;
                    window.location.reload();
                } else {
                    alert('Upload failed: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Upload failed: ' + error.message);
            } finally {
                this.uploading = false;
            }
        },
        
        async deleteFile(filename) {
            if (!confirm('Are you sure you want to delete this file?') || !this.postId) return;
            
            try {
                const response = await fetch(`/admin/posts/${this.postId}/delete-file`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ filename })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Delete failed: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Delete failed: ' + error.message);
            }
        },
        
        async renameFile(oldName) {
            const newName = prompt('New name:', oldName);
            if (!newName || newName === oldName || !this.postId) return;
            
            try {
                const response = await fetch(`/admin/posts/${this.postId}/rename-file`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ old_name: oldName, new_name: newName })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Rename failed: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Rename failed: ' + error.message);
            }
        },
        
        async deleteSubtitle(id) {
            if (!confirm('Are you sure you want to delete this subtitle?')) return;
            
            try {
                const response = await fetch(`/admin/stream-subtitles/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Delete failed');
                }
            } catch (error) {
                alert('Delete failed: ' + error.message);
            }
        },
        
        editSubtitle(id) {
            // Open in new tab for editing
            window.open(`/admin/stream-subtitles/${id}/edit`, '_blank');
        },
        
        async scanAndCreateSubtitles() {
            if (!confirm('This will scan the directory and create subtitle records for this stream. Continue?') || !this.streamId) return;
            
            try {
                const response = await fetch(`/admin/streams/${this.streamId}/scan-and-create`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(`Created ${data.created} subtitle records`);
                    window.location.reload();
                } else {
                    alert('Scan failed: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Scan failed: ' + error.message);
            }
        },
        
        refreshFiles() {
            window.location.reload();
        }
    }));
});
    </script>
</div>
