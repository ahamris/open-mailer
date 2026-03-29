@extends('layouts.admin')
@section('title', 'Email Logs')
@section('subtitle', 'All sent and received emails')

@section('actions')
<div class="flex gap-1 bg-gray-100 rounded-lg p-0.5">
    <a href="/admin/emails?direction=all" class="px-3 py-1.5 text-sm rounded-md {{ request('direction', 'all') === 'all' ? 'bg-white shadow-sm font-medium text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">All</a>
    <a href="/admin/emails?direction=outbound" class="px-3 py-1.5 text-sm rounded-md {{ request('direction') === 'outbound' ? 'bg-white shadow-sm font-medium text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">Outbound</a>
    <a href="/admin/emails?direction=inbound" class="px-3 py-1.5 text-sm rounded-md {{ request('direction') === 'inbound' ? 'bg-white shadow-sm font-medium text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">Inbound</a>
</div>
@endsection

@section('content')
<div class="card">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100">
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Status</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Dir</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">From</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">To</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Subject</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Auth</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($emails as $email)
            <tr class="border-b border-gray-50 hover:bg-gray-50 cursor-pointer" onclick="document.getElementById('modal-{{ $email->id }}').showModal()">
                <td class="px-5 py-3">
                    @php $c = match($email->status) { 'sent','delivered','received' => 'emerald', 'queued','sending' => 'amber', 'failed','bounced' => 'red', default => 'gray' }; @endphp
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-50 text-{{ $c }}-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-{{ $c }}-500"></span>{{ ucfirst($email->status) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-xs text-gray-400">{{ $email->direction === 'inbound' ? 'IN' : 'OUT' }}</td>
                <td class="px-5 py-3 text-sm text-gray-700 max-w-40 truncate">{{ $email->from_address }}</td>
                <td class="px-5 py-3 text-sm text-gray-700 max-w-40 truncate">{{ is_array($email->to_addresses) ? implode(', ', $email->to_addresses) : $email->to_addresses }}</td>
                <td class="px-5 py-3 text-sm text-gray-900 max-w-60 truncate">{{ $email->subject }}</td>
                <td class="px-5 py-3">
                    @if($email->spf_result)<span class="text-xs {{ $email->spf_result === 'pass' ? 'text-emerald-600' : 'text-red-500' }}">SPF</span>@endif
                    @if($email->dkim_result)<span class="text-xs {{ $email->dkim_result === 'pass' ? 'text-emerald-600' : 'text-red-500' }} ml-1">DKIM</span>@endif
                </td>
                <td class="px-5 py-3 text-sm text-gray-400">{{ $email->created_at->format('M d, H:i') }}</td>
            </tr>

            <dialog id="modal-{{ $email->id }}" class="rounded-xl shadow-xl border-0 p-0 max-w-2xl w-full backdrop:bg-black/30">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $email->subject }}</h3>
                    <div class="grid grid-cols-2 gap-2 mt-3 text-sm text-gray-600">
                        <div><strong>From:</strong> {{ $email->from_name }} &lt;{{ $email->from_address }}&gt;</div>
                        <div><strong>To:</strong> {{ is_array($email->to_addresses) ? implode(', ', $email->to_addresses) : $email->to_addresses }}</div>
                        <div><strong>Status:</strong> {{ $email->status }}</div>
                        <div><strong>Message-ID:</strong> <span class="text-xs">{{ $email->message_id }}</span></div>
                    </div>
                    <hr class="my-4 border-gray-100">
                    @if($email->html_body)
                        <iframe srcdoc="{{ e($email->html_body) }}" sandbox="" class="w-full min-h-64 border border-gray-200 rounded-lg bg-white"></iframe>
                    @else
                        <pre class="whitespace-pre-wrap text-sm text-gray-700 bg-gray-50 p-4 rounded-lg">{{ $email->text_body }}</pre>
                    @endif
                    <div class="flex justify-end mt-4">
                        <button class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg" onclick="document.getElementById('modal-{{ $email->id }}').close()">Close</button>
                    </div>
                </div>
            </dialog>
            @empty
            <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">No emails yet</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($emails->hasPages())
<div class="mt-4">{{ $emails->links() }}</div>
@endif
@endsection
