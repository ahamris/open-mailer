@extends('layouts.admin')
@section('title', 'Workflows')
@section('subtitle', 'Automate actions on incoming emails')

@section('actions')
<a href="/admin/workflows/create" class="btn btn--primary btn--sm">+ New Workflow</a>
@endsection

@section('content')
<div style="display:flex;flex-direction:column;gap:1rem;">
    @forelse($workflows as $workflow)
    <div class="card">
        <div class="card__body">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <form method="POST" action="/admin/workflows/{{ $workflow->id }}/toggle">
                        @csrf
                        <button type="submit" class="btn btn--ghost btn--sm" title="{{ $workflow->active ? 'Disable' : 'Enable' }}" style="padding:0;">
                            @if($workflow->active)
                                <span style="display:inline-block;width:2.5rem;height:1.25rem;border-radius:999px;background:var(--g500);position:relative;">
                                    <span style="position:absolute;right:.125rem;top:.125rem;width:1rem;height:1rem;border-radius:999px;background:white;"></span>
                                </span>
                            @else
                                <span style="display:inline-block;width:2.5rem;height:1.25rem;border-radius:999px;background:var(--n300);position:relative;">
                                    <span style="position:absolute;left:.125rem;top:.125rem;width:1rem;height:1rem;border-radius:999px;background:white;"></span>
                                </span>
                            @endif
                        </button>
                    </form>
                    <div>
                        <span class="font-semibold" style="color:var(--text-primary);">{{ $workflow->name }}</span>
                        @if($workflow->description)
                            <p class="text-sm text-muted">{{ $workflow->description }}</p>
                        @endif
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <span class="badge badge--neutral">{{ $workflow->times_triggered }}x triggered</span>
                    @if($workflow->last_triggered_at)
                        <span class="text-xs text-muted">Last: {{ $workflow->last_triggered_at->diffForHumans() }}</span>
                    @endif
                    <a href="/admin/workflows/{{ $workflow->id }}/logs" class="btn btn--ghost btn--sm">Logs</a>
                    <a href="/admin/workflows/{{ $workflow->id }}/edit" class="btn btn--ghost btn--sm">Edit</a>
                    <form method="POST" action="/admin/workflows/{{ $workflow->id }}" onsubmit="return confirm('Are you sure you want to delete this workflow?')">
                        @csrf @method('DELETE')
                        <button class="btn btn--ghost-danger btn--sm">Delete</button>
                    </form>
                </div>
            </div>

            {{-- Trigger & Action badges --}}
            <div style="display:flex;flex-wrap:wrap;gap:.375rem;margin-top:.75rem;align-items:center;">
                <span class="text-xs font-semibold" style="color:var(--text-tertiary);text-transform:uppercase;">IF:</span>
                @foreach($workflow->triggers as $trigger)
                    <span class="badge badge--info">{{ $trigger['field'] ?? '?' }} {{ $trigger['operator'] ?? '' }} "{{ Str::limit($trigger['value'] ?? '', 30) }}"</span>
                @endforeach
                <span class="text-xs font-semibold" style="color:var(--text-tertiary);text-transform:uppercase;margin-left:.5rem;">THEN:</span>
                @foreach($workflow->actions as $action)
                    <span class="badge badge--neutral">{{ $action['type'] ?? '?' }}</span>
                @endforeach
            </div>
        </div>
    </div>
    @empty
    <div class="card">
        <div class="card__body" style="text-align:center;padding:3rem 1rem;">
            <p class="text-muted" style="margin-bottom:.75rem;">No workflows yet</p>
            <a href="/admin/workflows/create" class="btn btn--primary btn--sm">Create your first workflow</a>
        </div>
    </div>
    @endforelse
</div>
@endsection
