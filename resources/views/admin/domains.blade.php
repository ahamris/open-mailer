@extends('layouts.admin')
@section('title', 'Domains')
@section('subtitle', 'Manage sending domains and DNS verification')
@section('actions')
    <button class="btn-green" onclick="document.getElementById('add-domain').showModal()">+ Add domain</button>
@endsection

@section('content')
<div class="space-y-4">
    @forelse($domains as $domain)
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $domain->name }}</h3>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $domain->status === 'verified' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $domain->status === 'verified' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    {{ ucfirst($domain->status) }}
                </span>
            </div>
            <div class="flex gap-2">
                <form method="POST" action="/admin/domains/{{ $domain->id }}/verify">@csrf
                    <button class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Verify DNS</button>
                </form>
                <form method="POST" action="/admin/domains/{{ $domain->id }}" onsubmit="return confirm('Delete domain?')">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1.5 text-sm text-red-500 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">Delete</button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-3">
            @foreach(['spf' => 'SPF', 'dkim' => 'DKIM', 'dmarc' => 'DMARC', 'mx' => 'MX'] as $field => $label)
            <div class="flex items-center gap-2 text-sm">
                @if($domain->{$field . '_valid'})
                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-gray-700">{{ $label }}</span>
                @else
                    <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-gray-400">{{ $label }}</span>
                @endif
            </div>
            @endforeach
        </div>

        @if($domain->dns_records)
        <details class="mt-4">
            <summary class="text-sm text-blue-500 hover:text-blue-600 cursor-pointer">DNS records to configure</summary>
            <table class="w-full mt-2 text-sm">
                <thead><tr class="text-left text-xs text-gray-500 uppercase"><th class="py-1">Type</th><th class="py-1">Host</th><th class="py-1">Value</th></tr></thead>
                <tbody>
                    @foreach($domain->dns_records as $record)
                    <tr class="border-t border-gray-50">
                        <td class="py-2"><code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">{{ $record['type'] }}</code></td>
                        <td class="py-2 text-xs text-gray-600 break-all">{{ $record['host'] }}</td>
                        <td class="py-2 text-xs text-gray-600 break-all font-mono">{{ $record['value'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </details>
        @endif
    </div>
    @empty
    <div class="text-center py-16 text-gray-400">
        <p class="mb-3">No domains configured yet</p>
        <button class="btn-green" onclick="document.getElementById('add-domain').showModal()">Add your first domain</button>
    </div>
    @endforelse
</div>

<dialog id="add-domain" class="rounded-xl shadow-xl border-0 p-0 max-w-md w-full backdrop:bg-black/30">
    <form method="POST" action="/admin/domains" class="p-6">
        @csrf
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Domain</h3>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Domain name</label>
            <input type="text" name="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="e.g. example.com" required>
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <button type="button" class="px-4 py-2 text-sm text-gray-600" onclick="document.getElementById('add-domain').close()">Cancel</button>
            <button type="submit" class="btn-green">Add domain</button>
        </div>
    </form>
</dialog>
@endsection
