@extends('layouts.admin')
@section('title', 'Suppression List')
@section('subtitle', 'Emails that will never receive messages')

@section('actions')
<button class="btn btn--primary btn--sm" onclick="document.getElementById('suppression-dialog').showModal()">+ Add to list</button>
@endsection

@section('content')
<div class="card">
    <table class="tbl">
        <thead>
            <tr>
                <th>Email</th>
                <th>Reason</th>
                <th>Note</th>
                <th>Date</th>
                <th style="width:1%"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($suppressions as $suppression)
            <tr>
                <td class="tbl__text-primary">{{ $suppression->email }}</td>
                <td>
                    @switch($suppression->reason)
                        @case('manual')
                            <span class="badge badge--neutral">Manual</span>
                            @break
                        @case('bounce')
                            <span class="badge badge--warning">Bounce</span>
                            @break
                        @case('complaint')
                            <span class="badge badge--danger">Complaint</span>
                            @break
                        @case('unsubscribe')
                            <span class="badge badge--info">Unsubscribe</span>
                            @break
                        @default
                            <span class="badge badge--neutral">{{ $suppression->reason }}</span>
                    @endswitch
                </td>
                <td class="tbl__text-muted">{{ $suppression->note ?? '—' }}</td>
                <td class="tbl__text-muted nowrap">{{ $suppression->created_at->format('M d, Y') }}</td>
                <td class="nowrap">
                    <form method="POST" action="/admin/suppressions/{{ $suppression->id }}" onsubmit="return confirm('Remove this email from the suppression list?')">
                        @csrf @method('DELETE')
                        <button class="btn btn--ghost-danger btn--sm">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="tbl__empty">No suppressed emails. Addresses added here will be excluded from all sends.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($suppressions->hasPages())
<div style="margin-top:1rem;">{{ $suppressions->links() }}</div>
@endif

{{-- Add suppression dialog --}}
<dialog id="suppression-dialog">
    <form method="POST" action="/admin/suppressions">
        @csrf
        <div class="dialog__header">
            <div class="dialog__title">Add to Suppression List</div>
        </div>
        <div class="dialog__body">
            <div class="form-group">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-input" required placeholder="user@example.com">
            </div>
            <div class="form-group">
                <label class="form-label">Reason</label>
                <select name="reason" class="form-select" required>
                    <option value="manual">Manual</option>
                    <option value="bounce">Bounce</option>
                    <option value="complaint">Complaint</option>
                    <option value="unsubscribe">Unsubscribe</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Note <span style="color:var(--text-tertiary);font-weight:400;">(optional)</span></label>
                <input type="text" name="note" class="form-input" placeholder="Reason for suppression">
            </div>
        </div>
        <div class="dialog__footer">
            <button type="button" class="btn btn--secondary" onclick="document.getElementById('suppression-dialog').close()">Cancel</button>
            <button type="submit" class="btn btn--primary">Add to List</button>
        </div>
    </form>
</dialog>
@endsection
