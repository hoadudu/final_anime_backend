<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Subtitle Manager - {{ $post->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-gray-50">
    <div class="min-h-full">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2M7 4h10M7 4l-2 16h14l-2-16M10 9v6M14 9v6"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-2xl font-bold text-gray-900">Subtitle Manager</h1>
                            <p class="text-sm text-gray-500">{{ $post->title }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            {{ $files->count() }} files
                        </span>
                        <a href="/admin/posts/{{ $post->id }}/edit" class="text-indigo-600 hover:text-indigo-500">
                            ← Back to Post
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div x-data="subtitleManager" class="space-y-6">
                
                <!-- Directory Info -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Directory Information</h3>
                        <div class="mt-2 max-w-xl text-sm text-gray-500">
                            <p>Files are stored in: <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $directory }}</code></p>
                        </div>
                    </div>
                </div>

                <!-- Upload Area -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Upload Subtitle Files</h3>
                        
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-400 transition-colors duration-150"
                             :class="{ 'border-indigo-400 bg-indigo-50': dragover }"
                             @drop.prevent="handleDrop($event)"
                             @dragover.prevent="dragover = true"
                             @dragleave="dragover = false">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Upload files</span>
                                        <input id="file-upload" @change="handleFileSelect($event)" name="file-upload" type="file" class="sr-only" multiple accept=".srt,.vtt,.ass,.ssa,.txt">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">SRT, VTT, ASS, SSA, TXT up to 10MB each</p>
                            </div>
                        </div>

                        <!-- Selected Files -->
                        <div x-show="files.length > 0" class="mt-4">
                            <h4 class="text-sm font-medium text-gray-900">Selected Files:</h4>
                            <ul class="mt-2 divide-y divide-gray-200">
                                <template x-for="(file, index) in files" :key="index">
                                    <li class="py-2 flex justify-between items-center">
                                        <span class="text-sm text-gray-900" x-text="file.name"></span>
                                        <button @click="removeFile(index)" class="text-red-600 hover:text-red-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                            <div class="mt-4 flex justify-end">
                                <button @click="uploadFiles()" 
                                        :disabled="uploading"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                    <svg x-show="!uploading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <svg x-show="uploading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                    </svg>
                                    <span x-text="uploading ? 'Uploading...' : 'Upload Files'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File List -->
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Directory Files</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ $files->count() }} files found ({{ $subtitleFiles->count() }} subtitles)</p>
                        </div>
                        <button @click="refreshPage()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                    </div>
                    @if($files->count() > 0)
                        <ul class="divide-y divide-gray-200">
                            @foreach($files as $file)
                                <li class="px-4 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                @if($file['is_subtitle'])
                                                    <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                                                        <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    </div>
                                                @else
                                                    <div class="h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center">
                                                        <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4 flex-1 min-w-0">
                                                <div class="flex items-center space-x-2">
                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $file['name'] }}</p>
                                                    @if($file['is_subtitle'])
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Subtitle</span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-gray-500">{{ $file['size'] }} • Modified {{ $file['modified'] }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button @click="renameFile('{{ $file['name'] }}')" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                                                Rename
                                            </button>
                                            <button @click="deleteFile('{{ $file['name'] }}')" class="text-red-600 hover:text-red-500 text-sm font-medium">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2M7 4h10M7 4l-2 16h14l-2-16"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No files</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by uploading subtitle files.</p>
                        </div>
                    @endif
                </div>

                @if($subtitleFiles->count() > 0)
                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Quick Actions</h3>
                            <div class="mt-4">
                                <button @click="scanAndCreateSubtitles()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    Scan Directory & Create {{ $subtitleFiles->count() }} Subtitle Records
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('subtitleManager', () => ({
                files: [],
                uploading: false,
                dragover: false,
                postId: {{ $post->id }},

                handleFileSelect(event) {
                    this.files = Array.from(event.target.files);
                },

                handleDrop(event) {
                    this.dragover = false;
                    this.files = Array.from(event.dataTransfer.files);
                },

                removeFile(index) {
                    this.files.splice(index, 1);
                },

                async uploadFiles() {
                    if (this.files.length === 0) return;
                    
                    this.uploading = true;
                    const formData = new FormData();
                    
                    this.files.forEach((file, index) => {
                        formData.append(`files[${index}]`, file);
                    });
                    
                    try {
                        const response = await fetch(`/admin/posts/${this.postId}/upload-subtitles`, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            alert(`Uploaded ${data.uploaded.length} files successfully!`);
                            window.location.reload();
                        } else {
                            alert('Upload failed: ' + data.message);
                        }
                    } catch (error) {
                        alert('Upload failed: ' + error.message);
                    } finally {
                        this.uploading = false;
                    }
                },

                async deleteFile(filename) {
                    if (!confirm(`Are you sure you want to delete "${filename}"?`)) return;
                    
                    try {
                        const response = await fetch(`/admin/posts/${this.postId}/delete-file`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ filename })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert('Delete failed: ' + data.message);
                        }
                    } catch (error) {
                        alert('Delete failed: ' + error.message);
                    }
                },

                async renameFile(oldName) {
                    const newName = prompt('New name:', oldName);
                    if (!newName || newName === oldName) return;
                    
                    try {
                        const response = await fetch(`/admin/posts/${this.postId}/rename-file`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ old_name: oldName, new_name: newName })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert('Rename failed: ' + data.message);
                        }
                    } catch (error) {
                        alert('Rename failed: ' + error.message);
                    }
                },

                async scanAndCreateSubtitles() {
                    if (!confirm('This will scan the directory and create subtitle records for all streams in this post. Continue?')) return;
                    
                    try {
                        const response = await fetch(`/admin/posts/${this.postId}/scan-subtitles`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            alert(`Created ${data.created} subtitle records!`);
                            window.location.reload();
                        } else {
                            alert('Scan failed: ' + data.message);
                        }
                    } catch (error) {
                        alert('Scan failed: ' + error.message);
                    }
                },

                refreshPage() {
                    window.location.reload();
                }
            }));
        });
    </script>
</body>
</html>
