@extends('layouts.admin')
@section('title', 'Contacts')
@section('subtitle', 'Manage your subscribers and audiences')

@section('actions')
<button class="btn btn--secondary btn--sm" onclick="document.getElementById('audience-dialog').showModal()">+ Create audience</button>
<a href="/admin/contacts/import" class="btn btn--secondary btn--sm">Import CSV</a>
<a href="/admin/contacts/create" class="btn btn--primary btn--sm">+ Add contact</a>
@endsection

@section('content')
<div style="display:flex;gap:1.5rem;">
    {{-- Audience sidebar --}}
    <div style="width:14rem;flex-shrink:0;">
        <div class="card">
            <div class="card__header">
                <span class="card__header-title">Audiences</span>
            </div>
            <div class="card__body" style="padding:0;">
                <a href="/admin/contacts" style="display:flex;align-items:center;justify-content:space-between;padding:.625rem 1rem;text-decoration:none;font-size:.8125rem;font-weight:500;{{ !request('audience') ? 'background:var(--b50);color:var(--b500);border-left:2px solid var(--b500);' : 'color:var(--text-secondary);' }}">
                    <span>All contacts</span>
                    <span class="text-xs" style="color:var(--text-tertiary);">{{ $contacts->total() }}</span>
                </a>
                @foreach($audiences as $audience)
                <a href="/admin/contacts?audience={{ $audience->id }}" style="display:flex;align-items:center;justify-content:space-between;padding:.625rem 1rem;text-decoration:none;font-size:.8125rem;font-weight:500;border-top:1px solid var(--border);{{ request('audience') == $audience->id ? 'background:var(--b50);color:var(--b500);border-left:2px solid var(--b500);' : 'color:var(--text-secondary);' }}">
                    <span>{{ $audience->name }}</span>
                    <span class="text-xs" style="color:var(--text-tertiary);">{{ $audience->contacts_count }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Contact list --}}
    <div style="flex:1;min-width:0;">
        {{-- Tag filter badges --}}
        @if(isset($tags) && $tags->count())
        <div style="display:flex;flex-wrap:wrap;gap:.375rem;margin-bottom:1rem;">
            <a href="{{ request()->fullUrlWithQuery(['tag' => null]) }}" style="text-decoration:none;">
                <span class="badge {{ !request('tag') ? 'badge--info' : 'badge--neutral' }}" style="cursor:pointer;">All</span>
            </a>
            @foreach($tags as $tag)
                <a href="{{ request()->fullUrlWithQuery(['tag' => $tag->id]) }}" style="text-decoration:none;">
                    <span class="badge {{ request('tag') == $tag->id ? 'badge--info' : 'badge--neutral' }}" style="cursor:pointer;">{{ $tag->name }}</span>
                </a>
            @endforeach
        </div>
        @endif

        {{-- Search bar --}}
        <form method="GET" action="/admin/contacts" style="margin-bottom:1rem;">
            @if(request('audience'))<input type="hidden" name="audience" value="{{ request('audience') }}">@endif
            @if(request('tag'))<input type="hidden" name="tag" value="{{ request('tag') }}">@endif
            <div style="position:relative;">
                <svg style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);width:1rem;height:1rem;color:var(--text-tertiary);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="Search contacts by email or name..." style="padding-left:2.5rem;">
            </div>
        </form>

        <div class="card">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Audiences</th>
                        <th>Tags</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th style="width:1%"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $contact)
                    <tr>
                        <td class="tbl__text-primary">
                            <a href="/admin/contacts/{{ $contact->id }}/edit" class="text-link">{{ $contact->email }}</a>
                        </td>
                        <td class="tbl__truncate">{{ $contact->first_name }} {{ $contact->last_name }}</td>
                        <td>
                            <div style="display:flex;gap:.25rem;flex-wrap:wrap;">
                                @foreach($contact->audiences as $audience)
                                    <span class="badge badge--info">{{ $audience->name }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <div style="display:flex;gap:.25rem;flex-wrap:wrap;">
                                @if($contact->tags && $contact->tags->count())
                                    @foreach($contact->tags as $tag)
                                        <span class="badge badge--neutral">{{ $tag->name }}</span>
                                    @endforeach
                                @else
                                    <span class="tbl__text-muted">—</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($contact->unsubscribed_at)
                                <span class="badge badge--danger"><span class="dot"></span>Unsubscribed</span>
                            @else
                                <span class="badge badge--success"><span class="dot"></span>Subscribed</span>
                            @endif
                        </td>
                        <td class="tbl__text-muted nowrap">{{ $contact->created_at->format('M d, Y') }}</td>
                        <td class="nowrap">
                            <div style="display:flex;align-items:center;gap:.25rem;">
                                <a href="/admin/contacts/{{ $contact->id }}/edit" class="btn btn--ghost btn--sm">Edit</a>
                                <form method="POST" action="/admin/contacts/{{ $contact->id }}" onsubmit="return confirm('Are you sure you want to delete this contact?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn--ghost-danger btn--sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="tbl__empty">No contacts found. <a href="/admin/contacts/create" class="text-link">Add your first contact</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contacts->hasPages())
        <div style="margin-top:1rem;">{{ $contacts->appends(request()->query())->links() }}</div>
        @endif
    </div>
</div>

{{-- Create audience dialog --}}
<dialog id="audience-dialog">
    <form method="POST" action="/admin/audiences">
        @csrf
        <div class="dialog__header">
            <div class="dialog__title">Create Audience</div>
        </div>
        <div class="dialog__body">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-input" required placeholder="e.g. Newsletter subscribers">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-input" placeholder="Optional description">
            </div>
        </div>
        <div class="dialog__footer">
            <button type="button" class="btn btn--secondary" onclick="document.getElementById('audience-dialog').close()">Cancel</button>
            <button type="submit" class="btn btn--primary">Create Audience</button>
        </div>
    </form>
</dialog>
@endsection
