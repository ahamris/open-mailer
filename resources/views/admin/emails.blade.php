@extends('layouts.admin')
@section('title', 'Emails')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Emails</h2>
    <div class="join">
        <a href="/admin/emails?direction=all" class="btn btn-sm join-item {{ request('direction', 'all') === 'all' ? 'btn-active' : '' }}">Alle</a>
        <a href="/admin/emails?direction=outbound" class="btn btn-sm join-item {{ request('direction') === 'outbound' ? 'btn-active' : '' }}">Outbound</a>
        <a href="/admin/emails?direction=inbound" class="btn btn-sm join-item {{ request('direction') === 'inbound' ? 'btn-active' : '' }}">Inbound</a>
    </div>
</div>

<div class="bg-base-100 rounded-box shadow">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Richting</th>
                    <th>Van</th>
                    <th>Aan</th>
                    <th>Onderwerp</th>
                    <th>Auth</th>
                    <th>Datum</th>
                </tr>
            </thead>
            <tbody>
                @forelse($emails as $email)
                <tr class="hover cursor-pointer" onclick="document.getElementById('modal-{{ $email->id }}').showModal()">
                    <td>
                        @switch($email->status)
                            @case('sent') @case('delivered') @case('received')
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
                    <td><span class="badge badge-outline badge-sm">{{ $email->direction === 'inbound' ? 'IN' : 'OUT' }}</span></td>
                    <td class="max-w-40 truncate">{{ $email->from_address }}</td>
                    <td class="max-w-40 truncate">{{ is_array($email->to_addresses) ? implode(', ', $email->to_addresses) : $email->to_addresses }}</td>
                    <td class="max-w-60 truncate">{{ $email->subject }}</td>
                    <td>
                        @if($email->spf_result)
                            <span class="badge badge-{{ $email->spf_result === 'pass' ? 'success' : 'error' }} badge-xs">SPF</span>
                        @endif
                        @if($email->dkim_result)
                            <span class="badge badge-{{ $email->dkim_result === 'pass' ? 'success' : 'error' }} badge-xs">DKIM</span>
                        @endif
                    </td>
                    <td class="text-sm text-base-content/60">{{ $email->created_at->format('d M H:i') }}</td>
                </tr>

                <dialog id="modal-{{ $email->id }}" class="modal">
                    <div class="modal-box max-w-3xl">
                        <h3 class="text-lg font-bold">{{ $email->subject }}</h3>
                        <div class="grid grid-cols-2 gap-2 mt-4 text-sm">
                            <div><strong>Van:</strong> {{ $email->from_name }} &lt;{{ $email->from_address }}&gt;</div>
                            <div><strong>Aan:</strong> {{ is_array($email->to_addresses) ? implode(', ', $email->to_addresses) : $email->to_addresses }}</div>
                            <div><strong>Status:</strong> {{ $email->status }}</div>
                            <div><strong>Message-ID:</strong> {{ $email->message_id }}</div>
                        </div>
                        <div class="divider"></div>
                        <div class="prose max-w-none">
                            @if($email->html_body)
                                <iframe srcdoc="{{ e($email->html_body) }}" sandbox="" class="w-full min-h-64 border-0 bg-white rounded"></iframe>
                            @else
                                <pre class="whitespace-pre-wrap">{{ $email->text_body }}</pre>
                            @endif
                        </div>
                        <div class="modal-action">
                            <form method="dialog"><button class="btn">Sluiten</button></form>
                        </div>
                    </div>
                    <form method="dialog" class="modal-backdrop"><button>close</button></form>
                </dialog>
                @empty
                <tr><td colspan="7" class="text-center text-base-content/40 py-8">Nog geen emails</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($emails->hasPages())
    <div class="p-4 border-t border-base-300">
        {{ $emails->links() }}
    </div>
    @endif
</div>
@endsection
