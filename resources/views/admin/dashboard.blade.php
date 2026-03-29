@extends('layouts.admin')
@section('title', 'Overview')
@section('subtitle', 'Welcome back!')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Sent today</p>
                <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $sentToday }}</p>
            </div>
            <div class="icon-box icon-box-green">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">Outbound emails</p>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Received today</p>
                <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $receivedToday }}</p>
            </div>
            <div class="icon-box icon-box-blue">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172"/></svg>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">Inbound emails</p>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Failed</p>
                <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $failedToday }}</p>
            </div>
            <div class="icon-box icon-box-red">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">Bounced / failed</p>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active domains</p>
                <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $activeDomains }}</p>
            </div>
            <div class="icon-box icon-box-purple">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3"/></svg>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">Verified</p>
    </div>
</div>

<div class="card">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900">Recent emails</h2>
        <a href="/admin/emails" class="text-sm text-blue-500 hover:text-blue-600">View all &rarr;</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100">
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Status</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Direction</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">From</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">To</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Subject</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentEmails as $email)
            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3">
                    @switch($email->status)
                        @case('sent') @case('delivered') @case('received')
                            <span class="badge-status badge-success"><span class="dot"></span>{{ ucfirst($email->status) }}</span>
                            @break
                        @case('queued') @case('sending')
                            <span class="badge-status badge-warning"><span class="dot"></span>{{ ucfirst($email->status) }}</span>
                            @break
                        @case('failed') @case('bounced')
                            <span class="badge-status badge-error"><span class="dot"></span>{{ ucfirst($email->status) }}</span>
                            @break
                        @default
                            <span class="badge-status badge-neutral"><span class="dot"></span>{{ ucfirst($email->status) }}</span>
                    @endswitch
                </td>
                <td class="px-5 py-3 text-sm text-gray-500">{{ $email->direction === 'inbound' ? 'In' : 'Out' }}</td>
                <td class="px-5 py-3 text-sm text-gray-700 max-w-40 truncate">{{ $email->from_address }}</td>
                <td class="px-5 py-3 text-sm text-gray-700 max-w-40 truncate">{{ is_array($email->to_addresses) ? implode(', ', $email->to_addresses) : $email->to_addresses }}</td>
                <td class="px-5 py-3 text-sm text-gray-900 max-w-60 truncate font-medium">{{ $email->subject }}</td>
                <td class="px-5 py-3 text-sm text-gray-400 whitespace-nowrap">{{ $email->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No emails yet</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
