@extends('layouts.admin')
@section('title', 'Logs: ' . $workflow->name)
@section('subtitle', 'Execution history for this workflow')

@section('actions')
<a href="/admin/workflows" class="btn btn--ghost btn--sm">&larr; Back to Workflows</a>
@endsection

@section('content')
<div class="card">
    <div class="card__body" style="padding:0;">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Email</th>
                    <th>Action</th>
                    <th>Status</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="nowrap text-sm">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                    <td>
                        <a href="/admin/mail/{{ $log->email_id }}" class="text-link">
                            {{ $log->email?->subject ?? $log->email_id }}
                        </a>
                    </td>
                    <td><span class="badge badge--neutral">{{ $log->action }}</span></td>
                    <td>
                        @if($log->status === 'success')
                            <span class="badge badge--success"><span class="dot"></span> Success</span>
                        @else
                            <span class="badge badge--danger"><span class="dot"></span> Failed</span>
                        @endif
                    </td>
                    <td class="tbl__truncate text-sm">{{ $log->result }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="tbl__empty">No logs yet for this workflow</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($logs->hasPages())
<div style="margin-top:1rem;">{{ $logs->links() }}</div>
@endif
@endsection
