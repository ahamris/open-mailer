@extends('layouts.admin')
@section('title', 'Overview')
@section('subtitle', 'Welcome back!')

@section('content')
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;">
    <div class="stat-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div class="stat-card__label">Sent today</div>
                <div class="stat-card__value">{{ $sentToday }}</div>
                <div class="stat-card__footer">Outbound emails</div>
            </div>
            <div class="stat-card__icon stat-card__icon--green">
                <svg style="width:1.25rem;height:1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div class="stat-card__label">Received today</div>
                <div class="stat-card__value">{{ $receivedToday }}</div>
                <div class="stat-card__footer">Inbound emails</div>
            </div>
            <div class="stat-card__icon stat-card__icon--blue">
                <svg style="width:1.25rem;height:1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172"/></svg>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div class="stat-card__label">Failed</div>
                <div class="stat-card__value">{{ $failedToday }}</div>
                <div class="stat-card__footer">Bounced / failed</div>
            </div>
            <div class="stat-card__icon stat-card__icon--red">
                <svg style="width:1.25rem;height:1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div class="stat-card__label">Active domains</div>
                <div class="stat-card__value">{{ $activeDomains }}</div>
                <div class="stat-card__footer">Verified</div>
            </div>
            <div class="stat-card__icon stat-card__icon--purple">
                <svg style="width:1.25rem;height:1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card__header">
        <span class="card__header-title">Recent emails</span>
        <a href="/admin/emails" class="text-link text-sm">View all &rarr;</a>
    </div>
    <table class="tbl">
        <thead>
            <tr>
                <th>Status</th>
                <th>Direction</th>
                <th>From</th>
                <th>To</th>
                <th>Subject</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentEmails as $email)
            <tr>
                <td>
                    @switch($email->status)
                        @case('sent') @case('delivered') @case('received')
                            <span class="badge badge--success"><span class="dot"></span>{{ ucfirst($email->status) }}</span>
                            @break
                        @case('queued') @case('sending')
                            <span class="badge badge--warning"><span class="dot"></span>{{ ucfirst($email->status) }}</span>
                            @break
                        @case('failed') @case('bounced')
                            <span class="badge badge--danger"><span class="dot"></span>{{ ucfirst($email->status) }}</span>
                            @break
                        @default
                            <span class="badge badge--neutral"><span class="dot"></span>{{ ucfirst($email->status) }}</span>
                    @endswitch
                </td>
                <td class="tbl__text-muted">{{ $email->direction === 'inbound' ? 'In' : 'Out' }}</td>
                <td class="tbl__truncate">{{ $email->from_address }}</td>
                <td class="tbl__truncate">{{ is_array($email->to_addresses) ? implode(', ', $email->to_addresses) : $email->to_addresses }}</td>
                <td class="tbl__text-primary tbl__truncate">{{ $email->subject }}</td>
                <td class="tbl__text-muted nowrap">{{ $email->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="tbl__empty">No emails yet</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
