<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Log Details - Laravel API Logger</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('api-logs.index') }}" class="text-blue-600 hover:text-blue-900">
                            ← Back to Logs
                        </a>
                        <h1 class="text-3xl font-bold text-gray-900">API Log Details</h1>
                    </div>
                    <div class="flex space-x-3">
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full 
                            {{ $apiLog->method === 'GET' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $apiLog->method === 'POST' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $apiLog->method === 'PUT' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $apiLog->method === 'DELETE' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $apiLog->method }}
                        </span>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full 
                            {{ $apiLog->status_class === 'success' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $apiLog->status_class === 'warning' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $apiLog->status_class === 'error' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $apiLog->response_status }}
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">URL</dt>
                            <dd class="mt-1 text-sm text-gray-900 break-all">{{ $apiLog->url }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $apiLog->ip_address }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                            <dd class="mt-1 text-sm text-gray-900 break-all">{{ $apiLog->user_agent }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Response Time</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $apiLog->formatted_response_time }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User ID</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $apiLog->user_id ?? 'Guest' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Session ID</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $apiLog->session_id ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Correlation ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $apiLog->correlation_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $apiLog->created_at->format('Y-m-d H:i:s') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Request Details -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Request Details</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-700 mb-2">Headers</h4>
                        <div class="bg-gray-50 rounded-md p-4 overflow-x-auto">
                            <pre class="text-sm text-gray-800"><code>{{ json_encode($apiLog->request_headers, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                    
                    @if($apiLog->request_body)
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-2">Body</h4>
                        <div class="bg-gray-50 rounded-md p-4 overflow-x-auto">
                            @if($apiLog->request_body_array)
                                <pre class="text-sm text-gray-800"><code>{{ json_encode($apiLog->request_body_array, JSON_PRETTY_PRINT) }}</code></pre>
                            @else
                                <pre class="text-sm text-gray-800"><code>{{ $apiLog->request_body }}</code></pre>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Response Details -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Response Details</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-700 mb-2">Headers</h4>
                        <div class="bg-gray-50 rounded-md p-4 overflow-x-auto">
                            <pre class="text-sm text-gray-800"><code>{{ json_encode($apiLog->response_headers, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                    
                    @if($apiLog->response_body)
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-2">Body</h4>
                        <div class="bg-gray-50 rounded-md p-4 overflow-x-auto">
                            @if($apiLog->response_body_array)
                                <pre class="text-sm text-gray-800"><code>{{ json_encode($apiLog->response_body_array, JSON_PRETTY_PRINT) }}</code></pre>
                            @else
                                <pre class="text-sm text-gray-800"><code>{{ $apiLog->response_body }}</code></pre>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Metadata -->
            @if($apiLog->metadata)
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Metadata</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="bg-gray-50 rounded-md p-4 overflow-x-auto">
                        <pre class="text-sm text-gray-800"><code>{{ json_encode($apiLog->metadata, JSON_PRETTY_PRINT) }}</code></pre>
                    </div>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Actions</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="flex space-x-3">
                        <button onclick="copyToClipboard('{{ $apiLog->correlation_id }}')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Copy Correlation ID
                        </button>
                        <button onclick="copyToClipboard('{{ $apiLog->url }}')" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            Copy URL
                        </button>
                        <form method="POST" action="{{ route('api-logs.destroy', $apiLog) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this log?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                                Delete Log
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Copied to clipboard: ' + text);
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
</body>
</html> 