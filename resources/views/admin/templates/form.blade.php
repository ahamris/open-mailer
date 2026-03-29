@extends('layouts.admin')
@section('title', $template ? 'Edit Template' : 'New Template')
@section('subtitle', $template ? 'Modify template content' : 'Create a reusable email template')

@section('actions')
<a href="/admin/templates" class="btn btn--ghost btn--sm">&larr; Back to Templates</a>
@endsection

@section('content')
<form method="POST" action="{{ $template ? '/admin/templates/' . $template->id : '/admin/templates' }}" id="template-form" style="max-width:56rem;">
    @csrf
    @if($template) @method('PUT') @endif

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__header">
            <span class="card__header-title">Template Details</span>
        </div>
        <div class="card__body">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $template->name ?? '') }}" required placeholder="e.g. Monthly Newsletter">
                @error('name')<p class="form-hint" style="color:var(--r500);">{{ $message }}</p>@enderror
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-input" value="{{ old('subject', $template->subject ?? '') }}" required placeholder="e.g. Your monthly update from @{{company}}">
                @error('subject')<p class="form-hint" style="color:var(--r500);">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__header" style="display:flex;align-items:center;justify-content:space-between;">
            <span class="card__header-title">HTML Body</span>
            <div style="display:flex;gap:.25rem;flex-wrap:wrap;" id="shortcode-buttons">
                <button type="button" class="btn btn--ghost btn--sm" data-shortcode="@{{contact.first_name}}" title="Insert first name">First Name</button>
                <button type="button" class="btn btn--ghost btn--sm" data-shortcode="@{{contact.last_name}}" title="Insert last name">Last Name</button>
                <button type="button" class="btn btn--ghost btn--sm" data-shortcode="@{{contact.email}}" title="Insert email">Email</button>
                <button type="button" class="btn btn--ghost btn--sm" data-shortcode="@{{contact.full_name}}" title="Insert full name">Full Name</button>
                <button type="button" class="btn btn--ghost btn--sm" data-shortcode="@{{date}}" title="Insert current date">Date</button>
                <button type="button" class="btn btn--ghost btn--sm" data-shortcode="@{{company}}" title="Insert company name">Company</button>
            </div>
        </div>
        <div class="card__body" style="padding:0;">
            <div id="quill-editor" style="min-height:20rem;"></div>
            <input type="hidden" name="html_body" id="html-body-input">
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__header">
            <span class="card__header-title">Plain Text Body</span>
            <span class="text-xs" style="color:var(--text-tertiary);">Auto-generated from HTML — edit if needed</span>
        </div>
        <div class="card__body">
            <textarea name="text_body" id="text-body-input" class="form-textarea" rows="6" placeholder="Plain text version of the email...">{{ old('text_body', $template->text_body ?? '') }}</textarea>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__body" style="display:flex;align-items:center;gap:.75rem;">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                <input type="hidden" name="published" value="0">
                <input type="checkbox" name="published" value="1" {{ old('published', $template->published ?? false) ? 'checked' : '' }}>
                <span class="font-medium">Published</span>
            </label>
            <span class="text-sm" style="color:var(--text-tertiary);">Published templates can be selected when creating broadcasts</span>
        </div>
    </div>

    <div style="display:flex;gap:.5rem;">
        <button type="submit" class="btn btn--primary">{{ $template ? 'Update Template' : 'Create Template' }}</button>
        <a href="/admin/templates" class="btn btn--ghost">Cancel</a>
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
        placeholder: 'Compose your email template...'
    });

    // Load existing content
    const existingHtml = @json(old('html_body', $template->html_body ?? ''));
    if (existingHtml) {
        quill.root.innerHTML = existingHtml;
    }

    // Auto-generate plain text from HTML
    quill.on('text-change', function() {
        const textBody = document.getElementById('text-body-input');
        textBody.value = quill.getText().trim();
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
    document.getElementById('template-form').addEventListener('submit', function() {
        document.getElementById('html-body-input').value = quill.root.innerHTML;
    });
});
</script>
@endsection
