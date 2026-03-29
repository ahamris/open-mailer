@extends('layouts.admin')
@section('title', $contact ? 'Edit Contact' : 'New Contact')
@section('subtitle', $contact ? 'Update contact information' : 'Add a new subscriber')

@section('actions')
<a href="/admin/contacts" class="btn btn--ghost btn--sm">&larr; Back to Contacts</a>
@endsection

@section('content')
<form method="POST" action="{{ $contact ? '/admin/contacts/' . $contact->id : '/admin/contacts' }}" style="max-width:36rem;">
    @csrf
    @if($contact) @method('PUT') @endif

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__header">
            <span class="card__header-title">Contact Details</span>
        </div>
        <div class="card__body">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $contact->email ?? '') }}" required placeholder="subscriber@example.com">
                @error('email')<p class="form-hint" style="color:var(--r500);">{{ $message }}</p>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">First name</label>
                    <input type="text" name="first_name" class="form-input" value="{{ old('first_name', $contact->first_name ?? '') }}" placeholder="John">
                    @error('first_name')<p class="form-hint" style="color:var(--r500);">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Last name</label>
                    <input type="text" name="last_name" class="form-input" value="{{ old('last_name', $contact->last_name ?? '') }}" placeholder="Doe">
                    @error('last_name')<p class="form-hint" style="color:var(--r500);">{{ $message }}</p>@enderror
                </div>
            </div>

            @if($contact && $contact->unsubscribed_at)
            <div class="alert" style="background:var(--r100);color:var(--r600);border:1px solid var(--r200);border-radius:.5rem;padding:.75rem 1rem;font-size:.8125rem;">
                This contact unsubscribed on {{ $contact->unsubscribed_at->format('M d, Y \a\t H:i') }}
            </div>
            @endif
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__header">
            <span class="card__header-title">Audiences</span>
        </div>
        <div class="card__body">
            @forelse($audiences as $audience)
            <label style="display:flex;align-items:center;gap:.5rem;padding:.375rem 0;cursor:pointer;">
                <input type="checkbox" name="audiences[]" value="{{ $audience->id }}"
                    {{ (collect(old('audiences', $contact ? $contact->audiences->pluck('id')->toArray() : []))->contains($audience->id)) ? 'checked' : '' }}>
                <span class="font-medium">{{ $audience->name }}</span>
                @if($audience->description)
                    <span class="text-xs" style="color:var(--text-tertiary);">— {{ $audience->description }}</span>
                @endif
            </label>
            @empty
            <p class="text-sm" style="color:var(--text-tertiary);">No audiences created yet. <a href="/admin/contacts" class="text-link">Create one from the contacts page.</a></p>
            @endforelse
        </div>
    </div>

    <div style="display:flex;gap:.5rem;">
        <button type="submit" class="btn btn--primary">{{ $contact ? 'Update Contact' : 'Create Contact' }}</button>
        <a href="/admin/contacts" class="btn btn--ghost">Cancel</a>
    </div>
</form>
@endsection
