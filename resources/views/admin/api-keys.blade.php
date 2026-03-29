@extends('layouts.admin')
@section('title', 'API Keys')
@section('subtitle', 'Manage authentication keys for the CLOM API')
@section('actions')
    <button class="btn btn--success" onclick="document.getElementById('create-key').showModal()">+ New key</button>
@endsection

@section('content')
@if(session('new_key'))
<div class="alert alert--warning">
    <strong>Copy your API key — it won't be shown again!</strong><br>
    <code style="display:block;margin-top:.5rem;padding:.5rem .75rem;background:var(--container-bg);border:1px solid var(--border);border-radius:.375rem;font-size:.8125rem;user-select:all;">{{ session('new_key') }}</code>
</div>
@endif

<div class="card">
    <table class="tbl">
        <thead>
            <tr>
                <th>Name</th>
                <th>Key</th>
                <th>Permission</th>
                <th>Last used</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($apiKeys as $key)
            <tr>
                <td class="tbl__text-primary">{{ $key->name }}</td>
                <td><code style="font-size:.75rem;color:var(--text-tertiary);background:var(--n100);padding:.125rem .375rem;border-radius:.25rem;">{{ $key->key_prefix }}...</code></td>
                <td><span class="badge badge--info">{{ str_replace('_', ' ', ucfirst($key->permission)) }}</span></td>
                <td class="tbl__text-muted">{{ $key->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                <td class="tbl__text-muted">{{ $key->created_at->format('M d, Y') }}</td>
                <td style="text-align:right;">
                    <form method="POST" action="/admin/api-keys/{{ $key->id }}" onsubmit="return confirm('Delete this API key?')">
                        @csrf @method('DELETE')
                        <button class="btn btn--ghost-danger btn--sm">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="tbl__empty">No API keys yet. Create one to get started.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<dialog id="create-key">
    <form method="POST" action="/admin/api-keys">
        @csrf
        <div class="dialog__header"><div class="dialog__title">New API Key</div></div>
        <div class="dialog__body">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-input" placeholder="e.g. Production" required>
            </div>
            <div class="form-group">
                <label class="form-label">Permission</label>
                <select name="permission" class="form-select">
                    <option value="full_access">Full access</option>
                    <option value="sending_access">Sending only</option>
                </select>
            </div>
        </div>
        <div class="dialog__footer">
            <button type="button" class="btn btn--secondary" onclick="document.getElementById('create-key').close()">Cancel</button>
            <button type="submit" class="btn btn--success">Create key</button>
        </div>
    </form>
</dialog>
@endsection
