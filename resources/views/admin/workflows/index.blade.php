@extends('layouts.admin')
@section('title', 'Workflows')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold">Workflows</h2>
        <p class="text-sm text-base-content/60 mt-1">Automatiseer acties op inkomende e-mails</p>
    </div>
    <a href="/admin/workflows/create" class="btn btn--primary btn--sm">+ New workflow</a>
</div>

<div class="grid gap-4">
    @forelse($workflows as $workflow)
    <div class="bg-base-100 rounded-box shadow p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <form method="POST" action="/admin/workflows/{{ $workflow->id }}/toggle">@csrf
                    <input type="checkbox" class="toggle toggle-primary toggle-sm" {{ $workflow->active ? 'checked' : '' }} onchange="this.form.submit()">
                </form>
                <div>
                    <h3 class="font-semibold">{{ $workflow->name }}</h3>
                    @if($workflow->description)<p class="text-sm text-base-content/60">{{ $workflow->description }}</p>@endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-ghost badge-sm">{{ $workflow->times_triggered }}x uitgevoerd</span>
                @if($workflow->last_triggered_at)
                    <span class="text-xs text-base-content/50">Laatst: {{ $workflow->last_triggered_at->diffForHumans() }}</span>
                @endif
                <a href="/admin/workflows/{{ $workflow->id }}/logs" class="btn btn--ghost btn-xs">Logs</a>
                <a href="/admin/workflows/{{ $workflow->id }}/edit" class="btn btn--ghost btn-xs">Edit</a>
                <form method="POST" action="/admin/workflows/{{ $workflow->id }}" onsubmit="return confirm('Delete workflow?')">
                    @csrf @method('DELETE')
                    <button class="btn btn--ghost btn-xs text-error">Delete</button>
                </form>
            </div>
        </div>

        <!-- Triggers -->
        <div class="mt-3 flex flex-wrap gap-2">
            <span class="text-xs font-semibold text-base-content/50 uppercase">Als:</span>
            @foreach($workflow->triggers as $trigger)
                <span class="badge badge-outline badge-sm">{{ $trigger['field'] ?? '?' }} {{ $trigger['operator'] ?? '' }} "{{ Str::limit($trigger['value'] ?? '', 30) }}"</span>
            @endforeach
            <span class="text-xs font-semibold text-base-content/50 uppercase ml-2">Dan:</span>
            @foreach($workflow->actions as $action)
                <span class="badge badge-primary badge-sm">{{ $action['type'] ?? '?' }}</span>
            @endforeach
        </div>
    </div>
    @empty
    <div class="text-center py-12 text-base-content/40">
        <p class="mb-2">No workflows yet</p>
        <a href="/admin/workflows/create" class="btn btn--primary btn--sm">Create your first workflow</a>
    </div>
    @endforelse
</div>
@endsection
