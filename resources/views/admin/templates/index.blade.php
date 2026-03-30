@extends('layouts.admin')
@section('title', 'Templates')
@section('subtitle', 'Reusable email designs for campaigns and transactional emails')

@section('actions')
<a href="/admin/templates/create" class="btn btn--success">+ New Template</a>
@endsection

@section('content')
<div class="card">
    <table class="tbl">
        <thead>
            <tr>
                <th>Name</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Last Modified</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($templates as $template)
            <tr>
                <td>
                    <a href="/admin/templates/{{ $template->id }}/edit" class="text-link font-medium">{{ $template->name }}</a>
                    <div class="text-xs text-muted">{{ $template->slug }}</div>
                </td>
                <td class="tbl__text-muted tbl__truncate">{{ $template->subject ?? '—' }}</td>
                <td>
                    @if($template->published)
                        <span class="badge badge--success"><span class="dot"></span>Published</span>
                    @else
                        <span class="badge badge--neutral"><span class="dot"></span>Draft</span>
                    @endif
                </td>
                <td class="tbl__text-muted nowrap">{{ $template->updated_at->diffForHumans() }}</td>
                <td style="text-align:right;">
                    <div style="display:flex;gap:.25rem;justify-content:flex-end;">
                        <a href="/admin/templates/{{ $template->id }}/edit" class="btn btn--ghost btn--sm">Code</a>
                        <a href="/admin/templates/{{ $template->id }}/builder" class="btn btn--secondary btn--sm">Builder</a>
                        <form method="POST" action="/admin/templates/{{ $template->id }}" onsubmit="return confirm('Delete this template?')" style="margin:0;">
                            @csrf @method('DELETE')
                            <button class="btn btn--ghost-danger btn--sm">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="tbl__empty">No templates yet. Create one to get started.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
