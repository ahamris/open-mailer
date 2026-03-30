@extends('layouts.admin')
@section('title', 'Drip Campaigns')
@section('subtitle', 'Multi-step automated email sequences')

@section('actions')
<a href="/admin/drips/create" class="btn btn--primary btn--sm">+ Create campaign</a>
@endsection

@section('content')
<div class="card">
    <table class="tbl">
        <thead>
            <tr>
                <th>Name</th>
                <th>Audience</th>
                <th>Trigger</th>
                <th>Steps</th>
                <th>Enrolled</th>
                <th>Completed</th>
                <th>Active</th>
                <th style="width:1%"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($campaigns as $campaign)
            <tr>
                <td class="tbl__text-primary font-medium">{{ $campaign->name }}</td>
                <td class="tbl__truncate">{{ $campaign->audience?->name ?? '—' }}</td>
                <td>
                    @switch($campaign->trigger_type)
                        @case('on_subscribe')
                            <span class="badge badge--info">On Subscribe</span>
                            @break
                        @case('on_tag')
                            <span class="badge badge--warning">On Tag</span>
                            @break
                        @case('manual')
                            <span class="badge badge--neutral">Manual</span>
                            @break
                        @default
                            <span class="badge badge--neutral">{{ ucfirst($campaign->trigger_type ?? 'none') }}</span>
                    @endswitch
                </td>
                <td class="tbl__text-muted">{{ $campaign->steps->count() }}</td>
                <td class="tbl__text-muted">{{ $campaign->enrollments_count ?? 0 }}</td>
                <td class="tbl__text-muted">
                    {{ $campaign->enrollments->where('status', 'completed')->count() }}
                </td>
                <td>
                    <form method="POST" action="/admin/drips/{{ $campaign->id }}/toggle" style="display:inline;">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn--ghost btn--sm" title="{{ $campaign->is_active ? 'Deactivate' : 'Activate' }}">
                            @if($campaign->is_active)
                                <span class="badge badge--success"><span class="dot"></span>Active</span>
                            @else
                                <span class="badge badge--neutral">Paused</span>
                            @endif
                        </button>
                    </form>
                </td>
                <td class="nowrap">
                    <div style="display:flex;align-items:center;gap:.25rem;">
                        <a href="/admin/drips/{{ $campaign->id }}" class="btn btn--ghost btn--sm">View</a>
                        <a href="/admin/drips/{{ $campaign->id }}/edit" class="btn btn--ghost btn--sm">Edit</a>
                        <form method="POST" action="/admin/drips/{{ $campaign->id }}" onsubmit="return confirm('Are you sure you want to delete this drip campaign?')">
                            @csrf @method('DELETE')
                            <button class="btn btn--ghost-danger btn--sm">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="tbl__empty">No drip campaigns yet. <a href="/admin/drips/create" class="text-link">Create your first campaign</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
