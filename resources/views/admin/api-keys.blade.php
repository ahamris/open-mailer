@extends('layouts.admin')
@section('title', 'API Keys')
@section('subtitle', 'Manage authentication keys for the CLOM API')
@section('actions')
    <button class="btn-green" onclick="document.getElementById('create-key').showModal()">+ New key</button>
@endsection

@section('content')
@if(session('new_key'))
<div class="mb-4 px-4 py-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-sm">
    <p class="font-medium">Copy your API key — it won't be shown again!</p>
    <code class="mt-1 block bg-white px-3 py-2 rounded border border-amber-200 font-mono text-sm select-all">{{ session('new_key') }}</code>
</div>
@endif

<div class="card">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100">
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Name</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Key</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Permission</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Last used</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Created</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($apiKeys as $key)
            <tr class="border-b border-gray-50 hover:bg-gray-50">
                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $key->name }}</td>
                <td class="px-5 py-3"><code class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ $key->key_prefix }}...</code></td>
                <td class="px-5 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $key->permission === 'full_access' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600' }}">{{ str_replace('_', ' ', ucfirst($key->permission)) }}</span>
                </td>
                <td class="px-5 py-3 text-sm text-gray-400">{{ $key->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                <td class="px-5 py-3 text-sm text-gray-400">{{ $key->created_at->format('M d, Y') }}</td>
                <td class="px-5 py-3 text-right">
                    <form method="POST" action="/admin/api-keys/{{ $key->id }}" onsubmit="return confirm('Delete this API key?')">
                        @csrf @method('DELETE')
                        <button class="text-red-400 hover:text-red-600 text-sm">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No API keys yet. Create one to get started.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<dialog id="create-key" class="rounded-xl shadow-xl border-0 p-0 max-w-md w-full backdrop:bg-black/30">
    <form method="POST" action="/admin/api-keys" class="p-6">
        @csrf
        <h3 class="text-lg font-semibold text-gray-900 mb-4">New API Key</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="e.g. Production" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Permission</label>
                <select name="permission" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="full_access">Full access</option>
                    <option value="sending_access">Sending only</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <button type="button" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800" onclick="document.getElementById('create-key').close()">Cancel</button>
            <button type="submit" class="btn-green">Create key</button>
        </div>
    </form>
</dialog>
@endsection
