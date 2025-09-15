@php
    $hasPost = $hasPost ?? false;
    $files = $files ?? [];
    $directory = $directory ?? null;
    $post = $post ?? null;
    $recordId = $recordId ?? null;
@endphp

<div class="space-y-4">
    @if(!$hasPost)
        <div class="p-4 bg-gray-50 rounded-lg text-center text-gray-500">
            <p class="text-sm">No post associated with this stream</p>
        </div>
    @else
        <!-- Directory Info -->
        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
            <div>
                <h4 class="text-sm font-medium text-blue-900">Subtitle Directory</h4>
                <p class="text-xs text-blue-700 font-mono">{{ $directory }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <button
                    type="button"
                    onclick="navigator.clipboard.writeText('{{ $directory }}')"
                    class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors"
                    title="Copy directory path"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
                <a
                    href="/admin/posts/{{ $post->id }}/edit"
                    class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors"
                    title="Manage in Post"
                    target="_blank"
                >
                    Manage
                </a>
            </div>
        </div>

        <!-- Files Table -->
        @if(empty($files))
            <div class="p-4 bg-yellow-50 rounded-lg text-center">
                <svg class="mx-auto h-8 w-8 text-yellow-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-sm text-yellow-800">No subtitle files found</p>
                <p class="text-xs text-yellow-600 mt-1">Upload subtitle files in the Post management page</p>
            </div>
        @else
            <div class="overflow-hidden border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                File Name
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Size
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Modified
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($files as $file)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900">{{ $file['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                    {{ $file['size'] }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($file['extension'] === 'srt') bg-blue-100 text-blue-800
                                        @elseif($file['extension'] === 'vtt') bg-green-100 text-green-800
                                        @elseif($file['extension'] === 'ass') bg-purple-100 text-purple-800
                                        @elseif($file['extension'] === 'ssa') bg-orange-100 text-orange-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ strtoupper($file['extension']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                    {{ $file['modified'] }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button
                                            type="button"
                                            onclick="copyToClipboard('{{ $file['name'] }}')"
                                            class="text-blue-600 hover:text-blue-900 text-xs"
                                            title="Copy filename"
                                        >
                                            Copy
                                        </button>
                                        <button
                                            type="button"
                                            onclick="assignToStream('{{ $recordId }}', '{{ $file['name'] }}')"
                                            class="text-green-600 hover:text-green-900 text-xs"
                                            title="Assign to this stream"
                                        >
                                            Assign
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
                <span>Total: {{ count($files) }} subtitle files</span>
                <button
                    type="button"
                    onclick="scanAndAssignSubtitles('{{ $recordId }}')"
                    class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors"
                >
                    Auto Assign All
                </button>
            </div>
        @endif
    @endif
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
        toast.textContent = 'Filename copied to clipboard';
        document.body.appendChild(toast);
        setTimeout(() => document.body.removeChild(toast), 2000);
    });
}

function assignToStream(streamId, filename) {
    // Make AJAX call to assign the subtitle to the stream
    fetch(`/admin/streams/${streamId}/assign-subtitle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ filename: filename })
    })
    .then(response => response.json())
    .then(data => {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-4 py-2 rounded shadow-lg z-50 ${
            data.success ? 'bg-green-500' : 'bg-red-500'
        } text-white`;
        toast.textContent = data.message;
        document.body.appendChild(toast);
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 3000);
        
        if (data.success) {
            // Optionally refresh the page or update UI
            setTimeout(() => window.location.reload(), 1000);
        }
    })
    .catch(error => {
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg z-50';
        toast.textContent = 'Error assigning subtitle';
        document.body.appendChild(toast);
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 3000);
    });
}

function scanAndAssignSubtitles(streamId) {
    // Show loading toast
    const loadingToast = document.createElement('div');
    loadingToast.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded shadow-lg z-50';
    loadingToast.textContent = 'Auto-assigning all subtitles...';
    document.body.appendChild(loadingToast);
    
    // Make AJAX call to auto-assign all subtitles
    fetch(`/admin/streams/${streamId}/scan-and-assign-all`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        // Remove loading toast
        if (document.body.contains(loadingToast)) {
            document.body.removeChild(loadingToast);
        }
        
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-4 py-2 rounded shadow-lg z-50 ${
            data.success ? 'bg-green-500' : 'bg-red-500'
        } text-white`;
        toast.textContent = data.message;
        document.body.appendChild(toast);
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 3000);
        
        if (data.success) {
            // Refresh the page to show updated subtitles
            setTimeout(() => window.location.reload(), 1000);
        }
    })
    .catch(error => {
        // Remove loading toast
        if (document.body.contains(loadingToast)) {
            document.body.removeChild(loadingToast);
        }
        
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg z-50';
        toast.textContent = 'Error auto-assigning subtitles';
        document.body.appendChild(toast);
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 3000);
    });
}
</script>
