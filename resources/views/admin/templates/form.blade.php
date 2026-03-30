@extends('layouts.admin')
@section('title', $template ? 'Edit Template' : 'New Template')
@section('subtitle', 'Create a reusable email template')

@section('actions')
<a href="/admin/templates" class="text-link text-sm">&larr; Back to Templates</a>
@if($template)
    <a href="/admin/templates/{{ $template->id }}/builder" class="btn btn--secondary btn--sm">Drag & Drop Builder</a>
@endif
@endsection

@section('content')
<form method="POST" action="{{ $template ? '/admin/templates/' . $template->id : '/admin/templates' }}" id="template-form">
    @csrf
    @if($template) @method('PUT') @endif
    <input type="hidden" name="html_body" id="html_body_input">
    <input type="hidden" name="text_body" id="text_body_input">

    {{-- Top: Template Details --}}
    <div class="card" style="margin-bottom:1rem;">
        <div class="card__body" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group" style="margin:0;">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $template->name ?? '') }}" required placeholder="e.g. Monthly Newsletter">
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-input" value="{{ old('subject', $template->subject ?? '') }}" placeholder="e.g. Your monthly update from @{{company}}">
            </div>
        </div>
    </div>

    {{-- Main: Split Editor + Preview --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
        {{-- Left: Code Editor --}}
        <div class="card" style="display:flex;flex-direction:column;">
            <div class="card__header" style="display:flex;align-items:center;justify-content:space-between;">
                <span class="card__header-title">HTML Editor</span>
                <div style="display:flex;gap:.25rem;">
                    <button type="button" class="btn btn--ghost btn--sm" onclick="insertShortcode('@{{contact.first_name}}')" title="First Name">First Name</button>
                    <button type="button" class="btn btn--ghost btn--sm" onclick="insertShortcode('@{{contact.last_name}}')" title="Last Name">Last Name</button>
                    <button type="button" class="btn btn--ghost btn--sm" onclick="insertShortcode('@{{contact.email}}')" title="Email">Email</button>
                    <button type="button" class="btn btn--ghost btn--sm" onclick="insertShortcode('@{{contact.full_name}}')" title="Full Name">Full Name</button>
                    <button type="button" class="btn btn--ghost btn--sm" onclick="insertShortcode('@{{date}}')" title="Date">Date</button>
                    <button type="button" class="btn btn--ghost btn--sm" onclick="insertShortcode('@{{company}}')" title="Company">Company</button>
                </div>
            </div>
            <div style="flex:1;position:relative;">
                <textarea id="code-editor" style="width:100%;height:60vh;border:none;resize:none;font-family:'Courier New',Courier,monospace;font-size:13px;line-height:1.5;padding:1rem;outline:none;background:var(--n50);color:var(--text-primary);tab-size:2;" spellcheck="false">{{ old('html_body', $template->html_body ?? '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, sans-serif; margin: 0; padding: 0; background: #f9f9f9; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
        .header { background: #0073e6; color: #ffffff; padding: 24px; text-align: center; }
        .content { padding: 32px 24px; }
        .footer { padding: 16px 24px; text-align: center; font-size: 12px; color: #79716b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;font-size:22px;">Your Newsletter Title</h1>
        </div>
        <div class="content">
            <p>Hi {{contact.first_name}},</p>
            <p>Your email content goes here.</p>
            <p><a href="#" style="display:inline-block;background:#0073e6;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;">Call to Action</a></p>
        </div>
        <div class="footer">
            <p>&copy; {{company}} &middot; <a href="{{unsubscribe_url}}">Unsubscribe</a></p>
        </div>
    </div>
</body>
</html>') }}</textarea>
            </div>
        </div>

        {{-- Right: Live Preview --}}
        <div class="card" style="display:flex;flex-direction:column;">
            <div class="card__header" style="display:flex;align-items:center;justify-content:space-between;">
                <span class="card__header-title">Preview</span>
                <div style="display:flex;gap:.25rem;">
                    <button type="button" class="btn btn--ghost btn--sm" onclick="setPreviewWidth('100%')" title="Desktop">Desktop</button>
                    <button type="button" class="btn btn--ghost btn--sm" onclick="setPreviewWidth('375px')" title="Mobile">Mobile</button>
                </div>
            </div>
            <div style="flex:1;background:var(--n100);padding:1rem;display:flex;justify-content:center;overflow:auto;">
                <iframe id="preview-frame" style="width:100%;height:60vh;border:1px solid var(--border);border-radius:.375rem;background:#fff;transition:width .3s;" sandbox=""></iframe>
            </div>
        </div>
    </div>

    {{-- Bottom: Plain text + options --}}
    <div class="card" style="margin-bottom:1rem;">
        <div class="card__header">
            <span class="card__header-title">Plain Text Body</span>
            <span class="text-sm text-muted">Auto-generated from HTML — edit if needed</span>
        </div>
        <div class="card__body" style="padding:0;">
            <textarea id="text-editor" class="form-textarea" style="border:none;border-radius:0;min-height:8rem;resize:vertical;" placeholder="Plain text version of the email...">{{ old('text_body', $template->text_body ?? '') }}</textarea>
        </div>
    </div>

    <div style="display:flex;align-items:center;gap:1rem;">
        <button type="submit" class="btn btn--success" onclick="prepareSubmit()">{{ $template ? 'Update Template' : 'Create Template' }}</button>
        <a href="/admin/templates" class="btn btn--ghost">Cancel</a>
        @if($template)
            <span style="margin-left:auto;">
                <a href="/admin/templates/{{ $template->id }}/builder" class="btn btn--primary btn--sm">Open Drag & Drop Builder</a>
            </span>
        @endif
    </div>
</form>
@endsection

@section('scripts')
<script>
const codeEditor = document.getElementById('code-editor');
const previewFrame = document.getElementById('preview-frame');
let debounceTimer;

// Live preview update
function updatePreview() {
    const html = codeEditor.value;
    previewFrame.srcdoc = html;
}

// Debounced preview
codeEditor.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(updatePreview, 300);
});

// Initial preview
updatePreview();

// Insert shortcode at cursor
function insertShortcode(code) {
    const start = codeEditor.selectionStart;
    const end = codeEditor.selectionEnd;
    const text = codeEditor.value;
    codeEditor.value = text.substring(0, start) + code + text.substring(end);
    codeEditor.selectionStart = codeEditor.selectionEnd = start + code.length;
    codeEditor.focus();
    updatePreview();
}

// Mobile/Desktop preview
function setPreviewWidth(w) {
    previewFrame.style.width = w;
}

// Tab key support in editor
codeEditor.addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
        e.preventDefault();
        const start = this.selectionStart;
        this.value = this.value.substring(0, start) + '  ' + this.value.substring(this.selectionEnd);
        this.selectionStart = this.selectionEnd = start + 2;
    }
});

// Prepare form for submit
function prepareSubmit() {
    document.getElementById('html_body_input').value = codeEditor.value;
    document.getElementById('text_body_input').value = document.getElementById('text-editor').value;
}

// Auto-generate plain text from HTML on blur
codeEditor.addEventListener('blur', function() {
    const textEditor = document.getElementById('text-editor');
    if (!textEditor.value.trim()) {
        const tmp = document.createElement('div');
        tmp.innerHTML = codeEditor.value;
        textEditor.value = tmp.textContent.replace(/\s+/g, ' ').trim();
    }
});
</script>
@endsection
