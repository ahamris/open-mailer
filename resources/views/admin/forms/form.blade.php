@extends('layouts.admin')
@section('title', $form ? 'Edit Form' : 'Create Form')
@section('subtitle', $form ? 'Update subscription form settings' : 'Set up a new subscription form')

@section('content')
<div style="max-width:40rem;">
    <form method="POST" action="{{ $form ? '/admin/forms/' . $form->id : '/admin/forms' }}">
        @csrf
        @if($form) @method('PUT') @endif

        <div class="card">
            <div class="card__header">
                <span class="card__header-title">Form Settings</span>
            </div>
            <div class="card__body">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-input" required value="{{ old('name', $form->name ?? '') }}" placeholder="e.g. Newsletter signup">
                    @error('name')<div class="form-hint" style="color:var(--r500);">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Audience</label>
                    <select name="audience_id" class="form-select" required>
                        <option value="">Select an audience</option>
                        @foreach($audiences as $audience)
                            <option value="{{ $audience->id }}" {{ old('audience_id', $form->audience_id ?? '') == $audience->id ? 'selected' : '' }}>{{ $audience->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-hint">New subscribers will be added to this audience.</div>
                    @error('audience_id')<div class="form-hint" style="color:var(--r500);">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                        <input type="hidden" name="double_opt_in" value="0">
                        <input type="checkbox" name="double_opt_in" value="1" {{ old('double_opt_in', $form->double_opt_in ?? false) ? 'checked' : '' }}>
                        <span class="form-label" style="margin:0;">Enable double opt-in</span>
                    </label>
                    <div class="form-hint">Send a confirmation email before adding subscribers.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Redirect URL <span style="color:var(--text-tertiary);font-weight:400;">(optional)</span></label>
                    <input type="url" name="redirect_url" class="form-input" value="{{ old('redirect_url', $form->redirect_url ?? '') }}" placeholder="https://example.com/thank-you">
                    <div class="form-hint">Redirect subscribers here after signup. Leave empty for a default success page.</div>
                    @error('redirect_url')<div class="form-hint" style="color:var(--r500);">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Confirmation Email Subject <span style="color:var(--text-tertiary);font-weight:400;">(optional)</span></label>
                    <input type="text" name="confirmation_subject" class="form-input" value="{{ old('confirmation_subject', $form->confirmation_subject ?? '') }}" placeholder="Please confirm your subscription">
                    <div class="form-hint">Subject line for the double opt-in confirmation email.</div>
                    @error('confirmation_subject')<div class="form-hint" style="color:var(--r500);">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:1.5rem;">
            <a href="/admin/forms" class="btn btn--secondary">Cancel</a>
            <button type="submit" class="btn btn--primary">{{ $form ? 'Update Form' : 'Create Form' }}</button>
        </div>
    </form>
</div>
@endsection
