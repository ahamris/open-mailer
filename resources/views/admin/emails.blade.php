@extends('layouts.admin')
@section('title', 'Email Logs')
@section('subtitle', 'All sent and received emails')

@section('actions')
<div style="display:flex;gap:2px;background:var(--n100);padding:2px;border-radius:.5rem;">
    <a href="/admin/emails?direction=all" style="padding:.375rem .75rem;font-size:.8125rem;border-radius:.375rem;text-decoration:none;{{ request('direction', 'all') === 'all' ? 'background:var(--container-bg);box-shadow:var(--shadow-sm);font-weight:500;color:var(--text-primary);' : 'color:var(--text-tertiary);' }}">All</a>
    <a href="/admin/emails?direction=outbound" style="padding:.375rem .75rem;font-size:.8125rem;border-radius:.375rem;text-decoration:none;{{ request('direction') === 'outbound' ? 'background:var(--container-bg);box-shadow:var(--shadow-sm);font-weight:500;color:var(--text-primary);' : 'color:var(--text-tertiary);' }}">Outbound</a>
    <a href="/admin/emails?direction=inbound" style="padding:.375rem .75rem;font-size:.8125rem;border-radius:.375rem;text-decoration:none;{{ request('direction') === 'inbound' ? 'background:var(--container-bg);box-shadow:var(--shadow-sm);font-weight:500;color:var(--text-primary);' : 'color:var(--text-tertiary);' }}">Inbound</a>
</div>
@endsection

@section('content')
<div class="card">
    <table class="tbl">
        <thead>
            <tr>
                <th>Status</th>
                <th>Dir</th>
                <th>From</th>
                <th>To</th>
                <th>Subject</th>
                <th>Auth</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($emails as $email)
            <tr style="cursor:pointer;" onclick="document.getElementById('modal-{{ $email->id }}').showModal()">
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
                <td class="tbl__text-muted">{{ $email->direction === 'inbound' ? 'IN' : 'OUT' }}</td>
                <td class="tbl__truncate">{{ $email->from_address }}</td>
                <td class="tbl__truncate">{{ is_array($email->to_addresses) ? implode(', ', $email->to_addresses) : $email->to_addresses }}</td>
                <td class="tbl__text-primary tbl__truncate">{{ $email->subject }}</td>
                <td>
                    @if($email->spf_result === 'pass')<span class="text-xs" style="color:var(--g500);">SPF</span>@elseif($email->spf_result)<span class="text-xs" style="color:var(--r400);">SPF</span>@endif
                    @if($email->dkim_result === 'pass')<span class="text-xs" style="color:var(--g500);">DKIM</span>@elseif($email->dkim_result)<span class="text-xs" style="color:var(--r400);">DKIM</span>@endif
                </td>
                <td class="tbl__text-muted nowrap">{{ $email->created_at->format('M d, H:i') }}</td>
            </tr>

            <dialog id="modal-{{ $email->id }}">
                <div class="dialog__header"><div class="dialog__title">{{ $email->subject }}</div></div>
                <div class="dialog__body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;font-size:.8125rem;color:var(--text-secondary);">
                        <div><strong>From:</strong> {{ $email->from_name }} &lt;{{ $email->from_address }}&gt;</div>
                        <div><strong>To:</strong> {{ is_array($email->to_addresses) ? implode(', ', $email->to_addresses) : $email->to_addresses }}</div>
                        <div><strong>Status:</strong> {{ $email->status }}</div>
                        <div><strong>Message-ID:</strong> <span class="text-xs">{{ $email->message_id }}</span></div>
                    </div>
                    <hr style="border:none;border-top:1px solid var(--border);margin:1rem 0;">
                    @if($email->html_body)
                        <iframe srcdoc="{{ e($email->html_body) }}" sandbox="" style="width:100%;min-height:16rem;border:1px solid var(--border);border-radius:.5rem;background:white;"></iframe>
                    @else
                        <pre style="white-space:pre-wrap;font-size:.8125rem;color:var(--text-secondary);background:var(--n50);padding:1rem;border-radius:.5rem;">{{ $email->text_body }}</pre>
                    @endif
                </div>
                <div class="dialog__footer">
                    <button class="btn btn--secondary" onclick="document.getElementById('modal-{{ $email->id }}').close()">Close</button>
                </div>
            </dialog>
            @empty
            <tr><td colspan="7" class="tbl__empty">No emails yet</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($emails->hasPages())
<div style="margin-top:1rem;">{{ $emails->links() }}</div>
@endif
@endsection
