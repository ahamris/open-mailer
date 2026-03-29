@extends('layouts.admin')
@section('title', 'Mail - ' . ucfirst($folder))
@section('actions')
    <a href="/admin/mail/compose" class="btn btn--success">+ New email</a>
@endsection

@section('content')
<div style="display:flex;gap:1.5rem;">
    <!-- Folder sidebar -->
    <div style="width:12rem;flex-shrink:0;">
        <nav class="card" style="padding:.5rem;">
            @php $folders = ['inbox'=>'Inbox','sent'=>'Sent','starred'=>'Starred','drafts'=>'Drafts','trash'=>'Trash']; @endphp
            @foreach($folders as $key => $label)
                <a href="/admin/mail?folder={{ $key }}" style="display:block;padding:.5rem .75rem;border-radius:.375rem;font-size:.875rem;text-decoration:none;font-weight:{{ $folder === $key ? '500' : '400' }};color:{{ $folder === $key ? 'var(--b600)' : 'var(--text-secondary)' }};background:{{ $folder === $key ? 'var(--b100)' : 'transparent' }};">
                    {{ $label }}
                    @if($key === 'inbox' && $unreadCount > 0)
                        <span class="sidenav__item-badge" style="float:right;">{{ $unreadCount }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>

    <!-- Email list -->
    <div style="flex:1;min-width:0;">
        <form method="GET" action="/admin/mail" style="margin-bottom:1rem;">
            <input type="hidden" name="folder" value="{{ $folder }}">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search emails..." class="form-input">
        </form>

        <div class="card">
            @forelse($emails as $email)
            <a href="/admin/mail/{{ $email->id }}" style="display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-bottom:1px solid rgba(12,10,9,0.05);text-decoration:none;color:inherit;transition:background .1s;{{ !$email->is_read && $email->direction === 'inbound' ? 'font-weight:600;background:var(--b50);' : '' }}" onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background='{{ !$email->is_read && $email->direction === 'inbound' ? 'var(--b50)' : '' }}'">
                <!-- Star -->
                <form method="POST" action="/admin/mail/{{ $email->id }}/star" onclick="event.stopPropagation();event.preventDefault();this.submit();" style="margin:0;">
                    @csrf
                    <button style="background:none;border:none;cursor:pointer;padding:0;color:{{ $email->is_starred ? 'var(--y400)' : 'var(--n300)' }};">
                        <svg style="width:1rem;height:1rem;" fill="{{ $email->is_starred ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </button>
                </form>

                <!-- Direction -->
                <span style="font-size:.6875rem;color:var(--text-tertiary);width:1.5rem;">{{ $email->direction === 'inbound' ? 'IN' : 'OUT' }}</span>

                <!-- Unread dot -->
                @if(!$email->is_read && $email->direction === 'inbound')
                    <span style="width:.5rem;height:.5rem;border-radius:999px;background:var(--b500);flex-shrink:0;"></span>
                @else
                    <span style="width:.5rem;flex-shrink:0;"></span>
                @endif

                <!-- Sender -->
                <span class="truncate" style="width:12rem;font-size:.875rem;">{{ $email->direction === 'inbound' ? ($email->from_name ?? $email->from_address) : implode(', ', $email->to_addresses ?? []) }}</span>

                <!-- Subject + preview -->
                <span class="truncate" style="flex:1;font-size:.875rem;">
                    {{ $email->subject }}
                    <span style="color:var(--n400);font-weight:400;"> — {{ Str::limit(strip_tags($email->html_body ?? $email->text_body ?? ''), 60) }}</span>
                </span>

                <!-- Date -->
                <span class="nowrap" style="font-size:.8125rem;color:var(--text-tertiary);width:5rem;text-align:right;">
                    {{ $email->created_at->isToday() ? $email->created_at->format('H:i') : $email->created_at->format('M d') }}
                </span>
            </a>
            @empty
            <div style="padding:4rem 1rem;text-align:center;color:var(--n400);">
                <svg style="width:3rem;height:3rem;margin:0 auto .75rem;opacity:.4;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                No emails in {{ $folder }}
            </div>
            @endforelse
        </div>

        @if($emails->hasPages())
        <div style="margin-top:1rem;">{{ $emails->links() }}</div>
        @endif
    </div>
</div>
@endsection
