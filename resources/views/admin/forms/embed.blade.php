@extends('layouts.admin')
@section('title', 'Embed Form')
@section('subtitle', 'Copy the HTML below and paste it into your website')

@section('actions')
<a href="/admin/forms/{{ $form->id }}/edit" class="btn btn--secondary btn--sm">Edit form</a>
<a href="/admin/forms" class="btn btn--ghost btn--sm">Back to forms</a>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
    {{-- Embed code --}}
    <div class="card">
        <div class="card__header">
            <span class="card__header-title">Embed Code</span>
        </div>
        <div class="card__body">
            <p style="font-size:.8125rem;color:var(--text-secondary);margin-bottom:1rem;">Copy this HTML snippet and paste it into your website where you want the form to appear.</p>
            <div style="position:relative;">
                <pre style="background:var(--n50);border:1px solid var(--border);border-radius:.5rem;padding:1rem;font-size:.75rem;line-height:1.6;overflow-x:auto;margin:0;"><code id="embed-code">&lt;form method="POST" action="{{ url('/subscribe/' . $form->id) }}" style="max-width:400px;font-family:sans-serif;"&gt;
  &lt;div style="margin-bottom:12px;"&gt;
    &lt;label style="display:block;margin-bottom:4px;font-size:14px;font-weight:500;"&gt;Email *&lt;/label&gt;
    &lt;input type="email" name="email" required
      style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;"
      placeholder="you@example.com"&gt;
  &lt;/div&gt;
  &lt;div style="margin-bottom:12px;"&gt;
    &lt;label style="display:block;margin-bottom:4px;font-size:14px;font-weight:500;"&gt;First Name&lt;/label&gt;
    &lt;input type="text" name="first_name"
      style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;"
      placeholder="Jane"&gt;
  &lt;/div&gt;
  &lt;div style="margin-bottom:12px;"&gt;
    &lt;label style="display:block;margin-bottom:4px;font-size:14px;font-weight:500;"&gt;Last Name&lt;/label&gt;
    &lt;input type="text" name="last_name"
      style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;"
      placeholder="Doe"&gt;
  &lt;/div&gt;
  &lt;button type="submit"
    style="width:100%;padding:10px 16px;background:#2563eb;color:#fff;border:none;border-radius:6px;font-size:14px;font-weight:500;cursor:pointer;"&gt;
    Subscribe
  &lt;/button&gt;
&lt;/form&gt;</code></pre>
                <button type="button" class="btn btn--secondary btn--sm" onclick="copyEmbed()" style="position:absolute;top:.5rem;right:.5rem;" id="copy-btn">Copy</button>
            </div>
        </div>
    </div>

    {{-- Live preview --}}
    <div class="card">
        <div class="card__header">
            <span class="card__header-title">Live Preview</span>
        </div>
        <div class="card__body">
            <div style="background:var(--n0);border:1px solid var(--border);border-radius:.5rem;padding:1.5rem;">
                <form style="max-width:400px;font-family:sans-serif;" onsubmit="event.preventDefault();">
                    <div style="margin-bottom:12px;">
                        <label style="display:block;margin-bottom:4px;font-size:14px;font-weight:500;">Email *</label>
                        <input type="email" placeholder="you@example.com" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" disabled>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block;margin-bottom:4px;font-size:14px;font-weight:500;">First Name</label>
                        <input type="text" placeholder="Jane" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" disabled>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block;margin-bottom:4px;font-size:14px;font-weight:500;">Last Name</label>
                        <input type="text" placeholder="Doe" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" disabled>
                    </div>
                    <button type="submit" style="width:100%;padding:10px 16px;background:#2563eb;color:#fff;border:none;border-radius:6px;font-size:14px;font-weight:500;cursor:pointer;" disabled>Subscribe</button>
                </form>
            </div>
            <div style="margin-top:1rem;">
                <p style="font-size:.75rem;color:var(--text-tertiary);">
                    <strong>Form:</strong> {{ $form->name }}<br>
                    <strong>Audience:</strong> {{ $form->audience->name ?? '—' }}<br>
                    <strong>Double opt-in:</strong> {{ $form->double_opt_in ? 'Enabled' : 'Disabled' }}<br>
                    <strong>Endpoint:</strong> <code style="font-size:.6875rem;background:var(--n50);padding:.125rem .375rem;border-radius:.25rem;">POST {{ url('/subscribe/' . $form->id) }}</code>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyEmbed() {
    const code = document.getElementById('embed-code').textContent;
    navigator.clipboard.writeText(code).then(() => {
        const btn = document.getElementById('copy-btn');
        btn.textContent = 'Copied!';
        setTimeout(() => { btn.textContent = 'Copy'; }, 2000);
    });
}
</script>
@endsection
