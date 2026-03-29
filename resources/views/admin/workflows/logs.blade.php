@extends('layouts.admin')
@section('title', 'Logs: ' . $workflow->name)

@section('content')
<div class="mb-4">
    <a href="/admin/workflows" class="btn btn-ghost btn-sm">&larr; Terug</a>
</div>

<h2 class="text-2xl font-bold mb-4">Logs: {{ $workflow->name }}</h2>

<div class="bg-base-100 rounded-box shadow overflow-x-auto">
    <table class="table">
        <thead>
            <tr><th>Datum</th><th>Email</th><th>Actie</th><th>Status</th><th>Resultaat</th></tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td class="text-sm">{{ $log->created_at->format('d M H:i:s') }}</td>
                <td class="text-sm"><a href="/admin/mail/{{ $log->email_id }}" class="link link-primary">{{ $log->email?->subject ?? $log->email_id }}</a></td>
                <td><span class="badge badge-ghost badge-sm">{{ $log->action }}</span></td>
                <td><span class="badge badge-{{ $log->status === 'success' ? 'success' : 'error' }} badge-sm">{{ $log->status }}</span></td>
                <td class="text-sm max-w-xs truncate">{{ $log->result }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-base-content/40 py-8">Nog geen logs</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($logs->hasPages())<div class="mt-4">{{ $logs->links() }}</div>@endif
@endsection
