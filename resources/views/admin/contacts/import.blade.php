@extends('layouts.admin')
@section('title', 'Import Contacts')
@section('subtitle', 'Upload a CSV or Excel file to import contacts in bulk')

@section('actions')
<a href="/admin/contacts" class="btn btn--ghost btn--sm">Back to contacts</a>
@endsection

@section('content')
<div style="max-width:40rem;">
    <form method="POST" action="/admin/contacts/import" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card__header">
                <span class="card__header-title">Upload File</span>
            </div>
            <div class="card__body">
                <div class="form-group">
                    <label class="form-label">File</label>
                    <input type="file" name="file" class="form-input" accept=".csv,.txt,.xlsx" required>
                    <div class="form-hint">Accepted formats: CSV, TXT, XLSX (max 10MB)</div>
                    @error('file')<div class="form-hint" style="color:var(--r500);">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Audience <span style="color:var(--text-tertiary);font-weight:400;">(optional)</span></label>
                    <select name="audience_id" class="form-select">
                        <option value="">No audience</option>
                        @foreach($audiences as $audience)
                            <option value="{{ $audience->id }}" {{ old('audience_id') == $audience->id ? 'selected' : '' }}>{{ $audience->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-hint">Optionally assign all imported contacts to an audience.</div>
                    @error('audience_id')<div class="form-hint" style="color:var(--r500);">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:1.5rem;">
            <div class="card__header">
                <span class="card__header-title">Expected Format</span>
            </div>
            <div class="card__body">
                <p style="font-size:.8125rem;color:var(--text-secondary);margin-bottom:.75rem;">Your file should contain the following columns. Only <strong>email</strong> is required.</p>
                <pre style="background:var(--n50);border:1px solid var(--border);border-radius:.5rem;padding:1rem;font-size:.75rem;line-height:1.6;overflow-x:auto;margin:0;"><code>email,first_name,last_name
john@example.com,John,Doe
jane@example.com,Jane,Smith
info@company.com,,</code></pre>
                <div style="margin-top:.75rem;">
                    <p style="font-size:.75rem;color:var(--text-tertiary);">
                        Duplicate emails will be skipped. Existing contacts will not be overwritten.
                    </p>
                </div>
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:1.5rem;">
            <a href="/admin/contacts" class="btn btn--secondary">Cancel</a>
            <button type="submit" class="btn btn--primary">Import Contacts</button>
        </div>
    </form>
</div>
@endsection
