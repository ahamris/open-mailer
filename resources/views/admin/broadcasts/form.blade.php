@extends('layouts.admin')
@section('title', $broadcast ? 'Edit Broadcast' : 'New Broadcast')
@section('subtitle', $broadcast ? 'Modify broadcast details' : 'Create an email campaign')

@section('actions')
<a href="/admin/broadcasts" class="btn btn--ghost btn--sm">&larr; Back to Broadcasts</a>
@endsection

@section('content')
<form method="POST" action="{{ $broadcast ? '/admin/broadcasts/' . $broadcast->id : '/admin/broadcasts' }}" id="broadcast-form" style="max-width:56rem;">
    @csrf
    @if($broadcast) @method('PUT') @endif

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__header">
            <span class="card__header-title">Campaign Details</span>
        </div>
        <div class="card__body">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $broadcast->name ?? '') }}" required placeholder="e.g. March 2026 Newsletter">
                <p class="form-hint">Internal name — not shown to recipients.</p>
                @error('name')<p class="form-hint" style="color:var(--r500);">{{ $message }}</p>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">From address</label>
                    <input type="email" name="from_address" class="form-input" value="{{ old('from_address', $broadcast->from_address ?? '') }}" required placeholder="newsletter@yourdomain.com">
                    @error('from_address')<p class="form-hint" style="color:var(--r500);">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">From name</label>
                    <input type="text" name="from_name" class="form-input" value="{{ old('from_name', $broadcast->from_name ?? '') }}" placeholder="Your Company">
                    @error('from_name')<p class="form-hint" style="color:var(--r500);">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-input" value="{{ old('subject', $broadcast->subject ?? '') }}" required placeholder="e.g. Your monthly update">
                @error('subject')<p class="form-hint" style="color:var(--r500);">{{ $message }}</p>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Audience</label>
                    <select name="audience_id" class="form-select" required>
                        <option value="">Select audience...</option>
                        @foreach($audiences as $audience)
                            <option value="{{ $audience->id }}" {{ old('audience_id', $broadcast->audience_id ?? '') == $audience->id ? 'selected' : '' }}>
                                {{ $audience->name }} ({{ $audience->contacts_count }} contacts)
                            </option>
                        @endforeach
                    </select>
                    @error('audience_id')<p class="form-hint" style="color:var(--r500);">{{ $message }}</p>@enderror
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Load from template</label>
                    <select id="template-select" class="form-select">
                        <option value="">None — write from scratch</option>
                        @foreach($templates as $tpl)
                            <option value="{{ $tpl->id }}" data-subject="{{ e($tpl->subject) }}" data-html="{{ e($tpl->html_body) }}">
                                {{ $tpl->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="form-hint">Loads template content into the editor below.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__header" style="display:flex;align-items:center;justify-content:space-between;">
            <span class="card__header-title">Email Body</span>
            <div style="display:flex;gap:.25rem;flex-wrap:wrap;" id="shortcode-buttons">
                <button type="button" class="btn btn--ghost btn--sm" data-shortcode="{{contact.first_name}}" title="Insert first name">First Name</button>
                <button type="button" class="btn btn--ghost btn--sm" data-shortcode="{{contact.last_name}}" title="Insert last name">Last Name</button>
                <button type="button" class="btn btn--ghost btn--sm" data-shortcode="{{contact.email}}" title="Insert email">Email</button>
                <button type="button" class="btn btn--ghost btn--sm" data-shortcode="{{contact.full_name}}" title="Insert full name">Full Name</button>
                <button type="button" class="btn btn--ghost btn--sm" data-shortcode="{{date}}" title="Insert current date">Date</button>
                <button type="button" class="btn btn--ghost btn--sm" data-shortcode="{{company}}" title="Insert company name">Company</button>
            </div>
        </div>
        <div class="card__body" style="padding:0;">
            <div id="quill-editor" style="min-height:20rem;"></div>
            <input type="hidden" name="html_body" id="html-body-input">
        </div>
    </div>

    <div style="display:flex;gap:.5rem;">
        <button type="submit" class="btn btn--primary">{{ $broadcast ? 'Update Broadcast' : 'Create Broadcast' }}</button>
        <a href="/admin/broadcasts" class="btn btn--ghost">Cancel</a>
    </div>
</form>
@endsection

@section('scripts')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.snow.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ align: [] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'code-block'],
                ['link', 'image'],
                ['clean']
            ]
        },
        placeholder: 'Compose your broadcast email...'
    });

    // Load existing content
    const existingHtml = @json(old('html_body', $broadcast->html_body ?? ''));
    if (existingHtml) {
        quill.root.innerHTML = existingHtml;
    }

    // Template loader
    document.getElementById('template-select').addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (!option.value) return;

        if (quill.getText().trim().length > 0) {
            if (!confirm('This will replace the current editor content. Continue?')) {
                this.value = '';
                return;
            }
        }

        const html = option.dataset.html || '';
        const subject = option.dataset.subject || '';

        // Decode HTML entities
        const decoder = document.createElement('textarea');
        decoder.innerHTML = html;
        quill.root.innerHTML = decoder.value;

        if (subject) {
            const subjectInput = document.querySelector('input[name="subject"]');
            if (!subjectInput.value || confirm('Also load the template subject?')) {
                subjectInput.value = subject;
            }
        }
    });

    // Shortcode insertion
    document.querySelectorAll('#shortcode-buttons [data-shortcode]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const shortcode = this.dataset.shortcode;
            const range = quill.getSelection(true);
            quill.insertText(range.index, shortcode);
            quill.setSelection(range.index + shortcode.length);
        });
    });

    // Sync HTML to hidden input on submit
    document.getElementById('broadcast-form').addEventListener('submit', function() {
        document.getElementById('html-body-input').value = quill.root.innerHTML;
    });
});
</script>
@endsection
