@extends('layouts.admin')
@section('title', 'Mail - ' . ucfirst($folder))

@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold">Mail</h2>
    <a href="/admin/mail/compose" class="btn btn-primary btn-sm">+ Nieuwe mail</a>
</div>

<div class="flex gap-4">
    <!-- Folder sidebar -->
    <div class="w-48 shrink-0">
        <ul class="menu bg-base-100 rounded-box shadow w-full">
            <li><a href="/admin/mail?folder=inbox" class="{{ $folder === 'inbox' ? 'active' : '' }}">Inbox @if($unreadCount > 0)<span class="badge badge-sm badge-primary">{{ $unreadCount }}</span>@endif</a></li>
            <li><a href="/admin/mail?folder=sent" class="{{ $folder === 'sent' ? 'active' : '' }}">Verzonden</a></li>
            <li><a href="/admin/mail?folder=starred" class="{{ $folder === 'starred' ? 'active' : '' }}">Met ster</a></li>
            <li><a href="/admin/mail?folder=drafts" class="{{ $folder === 'drafts' ? 'active' : '' }}">Concepten</a></li>
            <li><a href="/admin/mail?folder=trash" class="{{ $folder === 'trash' ? 'active' : '' }}">Prullenbak</a></li>
        </ul>
    </div>

    <!-- Email list -->
    <div class="flex-1">
        <!-- Search -->
        <form method="GET" action="/admin/mail" class="mb-4">
            <input type="hidden" name="folder" value="{{ $folder }}">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Zoek in e-mails..." class="input input-bordered w-full" />
        </form>

        <div class="bg-base-100 rounded-box shadow">
            @forelse($emails as $email)
            <a href="/admin/mail/{{ $email->id }}" class="flex items-center gap-3 p-3 border-b border-base-200 hover:bg-base-200 transition-colors {{ !$email->is_read && $email->direction === 'inbound' ? 'font-semibold bg-primary/5' : '' }}">
                <!-- Star -->
                <form method="POST" action="/admin/mail/{{ $email->id }}/star" onclick="event.stopPropagation()">
                    @csrf
                    <button class="btn btn-ghost btn-xs p-0">
                        @if($email->is_starred)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-warning fill-warning" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        @endif
                    </button>
                </form>

                <!-- Direction badge -->
                <span class="badge badge-{{ $email->direction === 'inbound' ? 'info' : 'ghost' }} badge-xs w-8">{{ $email->direction === 'inbound' ? 'IN' : 'UIT' }}</span>

                <!-- Unread dot -->
                @if(!$email->is_read && $email->direction === 'inbound')
                    <span class="w-2 h-2 rounded-full bg-primary shrink-0"></span>
                @else
                    <span class="w-2 shrink-0"></span>
                @endif

                <!-- Sender -->
                <span class="w-48 truncate text-sm">{{ $email->direction === 'inbound' ? ($email->from_name ?? $email->from_address) : implode(', ', $email->to_addresses ?? []) }}</span>

                <!-- Subject + preview -->
                <span class="flex-1 truncate text-sm">
                    {{ $email->subject }}
                    <span class="text-base-content/40 font-normal"> — {{ Str::limit(strip_tags($email->html_body ?? $email->text_body ?? ''), 80) }}</span>
                </span>

                <!-- Auth badges -->
                @if($email->spf_result)
                    <span class="badge badge-{{ $email->spf_result === 'pass' ? 'success' : 'error' }} badge-xs">SPF</span>
                @endif

                <!-- Date -->
                <span class="text-xs text-base-content/50 w-20 text-right shrink-0">
                    {{ $email->created_at->isToday() ? $email->created_at->format('H:i') : $email->created_at->format('d M') }}
                </span>
            </a>
            @empty
            <div class="p-12 text-center text-base-content/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                Geen e-mails in {{ $folder }}
            </div>
            @endforelse
        </div>

        @if($emails->hasPages())
        <div class="mt-4">{{ $emails->links() }}</div>
        @endif
    </div>
</div>
@endsection
