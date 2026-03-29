@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Dashboard</h2>
    <div class="badge badge-primary badge-lg">CLOM v0.1</div>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat bg-base-100 rounded-box shadow">
        <div class="stat-title">Verzonden vandaag</div>
        <div class="stat-value text-primary">{{ $sentToday }}</div>
        <div class="stat-desc">Outbound emails</div>
    </div>
    <div class="stat bg-base-100 rounded-box shadow">
        <div class="stat-title">Ontvangen vandaag</div>
        <div class="stat-value text-secondary">{{ $receivedToday }}</div>
        <div class="stat-desc">Inbound emails</div>
    </div>
    <div class="stat bg-base-100 rounded-box shadow">
        <div class="stat-title">Mislukt</div>
        <div class="stat-value text-error">{{ $failedToday }}</div>
        <div class="stat-desc">Bounced / failed</div>
    </div>
    <div class="stat bg-base-100 rounded-box shadow">
        <div class="stat-title">Actieve domeinen</div>
        <div class="stat-value">{{ $activeDomains }}</div>
        <div class="stat-desc">Geverifieerd</div>
    </div>
</div>

<!-- Recent emails -->
<div class="bg-base-100 rounded-box shadow">
    <div class="p-4 border-b border-base-300">
        <h3 class="font-semibold text-lg">Recente emails</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Richting</th>
                    <th>Van</th>
                    <th>Aan</th>
                    <th>Onderwerp</th>
                    <th>Datum</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentEmails as $email)
                <tr class="hover">
                    <td>
                        @switch($email->status)
                            @case('sent') @case('delivered')
                                <span class="badge badge-success badge-sm">{{ $email->status }}</span>
                                @break
                            @case('queued') @case('sending')
                                <span class="badge badge-warning badge-sm">{{ $email->status }}</span>
                                @break
                            @case('failed') @case('bounced')
                                <span class="badge badge-error badge-sm">{{ $email->status }}</span>
                                @break
                            @default
                                <span class="badge badge-ghost badge-sm">{{ $email->status }}</span>
                        @endswitch
                    </td>
                    <td>
                        <span class="badge badge-outline badge-sm">{{ $email->direction === 'inbound' ? 'IN' : 'OUT' }}</span>
                    </td>
                    <td class="max-w-40 truncate">{{ $email->from_address }}</td>
                    <td class="max-w-40 truncate">{{ is_array($email->to_addresses) ? implode(', ', $email->to_addresses) : $email->to_addresses }}</td>
                    <td class="max-w-60 truncate">{{ $email->subject }}</td>
                    <td class="text-sm text-base-content/60">{{ $email->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-base-content/40 py-8">Nog geen emails</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
