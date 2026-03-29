@extends('layouts.admin')
@section('title', 'Broadcasts')
@section('subtitle', 'Send email campaigns to your audiences')

@section('actions')
<a href="/admin/broadcasts/create" class="btn btn--primary btn--sm">+ Create broadcast</a>
@endsection

@section('content')
<div class="card">
    <table class="tbl">
        <thead>
            <tr>
                <th>Name</th>
                <th>Audience</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Recipients</th>
                <th>Sent</th>
                <th style="width:1%"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($broadcasts as $broadcast)
            <tr>
                <td class="tbl__text-primary font-medium">{{ $broadcast->name }}</td>
                <td class="tbl__truncate">{{ $broadcast->audience?->name ?? '—' }}</td>
                <td class="tbl__truncate">{{ $broadcast->subject }}</td>
                <td>
                    @switch($broadcast->status)
                        @case('draft')
                            <span class="badge badge--neutral">Draft</span>
                            @break
                        @case('queued')
                            <span class="badge badge--warning"><span class="dot"></span>Queued</span>
                            @break
                        @case('sending')
                            <span class="badge badge--info"><span class="dot"></span>Sending</span>
                            @break
                        @case('sent')
                            <span class="badge badge--success"><span class="dot"></span>Sent</span>
                            @break
                        @case('failed')
                            <span class="badge badge--danger"><span class="dot"></span>Failed</span>
                            @break
                        @default
                            <span class="badge badge--neutral">{{ ucfirst($broadcast->status) }}</span>
                    @endswitch
                </td>
                <td class="tbl__text-muted nowrap">
                    @if($broadcast->status === 'sent' || $broadcast->status === 'sending')
                        {{ $broadcast->sent_count ?? 0 }} / {{ $broadcast->total_count ?? 0 }}
                    @else
                        {{ $broadcast->audience?->contacts_count ?? '—' }}
                    @endif
                </td>
                <td class="tbl__text-muted nowrap">
                    {{ $broadcast->sent_at ? $broadcast->sent_at->format('M d, Y H:i') : '—' }}
                </td>
                <td class="nowrap">
                    <div style="display:flex;align-items:center;gap:.25rem;">
                        @if($broadcast->status === 'draft')
                            <a href="/admin/broadcasts/{{ $broadcast->id }}/edit" class="btn btn--ghost btn--sm">Edit</a>
                            <form method="POST" action="/admin/broadcasts/{{ $broadcast->id }}/send" onsubmit="return confirm('Send this broadcast to all contacts in the audience? This cannot be undone.')">
                                @csrf
                                <button class="btn btn--success btn--sm">Send</button>
                            </form>
                        @endif
                        <form method="POST" action="/admin/broadcasts/{{ $broadcast->id }}" onsubmit="return confirm('Are you sure you want to delete this broadcast?')">
                            @csrf @method('DELETE')
                            <button class="btn btn--ghost-danger btn--sm">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="tbl__empty">No broadcasts yet. <a href="/admin/broadcasts/create" class="text-link">Create your first broadcast</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
