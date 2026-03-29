@extends('layouts.admin')
@section('title', 'Templates')
@section('subtitle', 'Reusable email templates for broadcasts')

@section('actions')
<a href="/admin/templates/create" class="btn btn--primary btn--sm">+ Create template</a>
@endsection

@section('content')
<div class="card">
    <table class="tbl">
        <thead>
            <tr>
                <th>Name</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Updated</th>
                <th style="width:1%"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($templates as $template)
            <tr>
                <td class="tbl__text-primary">
                    <a href="/admin/templates/{{ $template->id }}/edit" class="text-link font-medium">{{ $template->name }}</a>
                </td>
                <td class="tbl__truncate">{{ $template->subject }}</td>
                <td>
                    @if($template->published)
                        <span class="badge badge--success"><span class="dot"></span>Published</span>
                    @else
                        <span class="badge badge--neutral">Draft</span>
                    @endif
                </td>
                <td class="tbl__text-muted nowrap">{{ $template->updated_at->format('M d, Y') }}</td>
                <td class="nowrap">
                    <div style="display:flex;align-items:center;gap:.25rem;">
                        <a href="/admin/templates/{{ $template->id }}/edit" class="btn btn--ghost btn--sm">Edit</a>
                        <form method="POST" action="/admin/templates/{{ $template->id }}" onsubmit="return confirm('Are you sure you want to delete this template?')">
                            @csrf @method('DELETE')
                            <button class="btn btn--ghost-danger btn--sm">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="tbl__empty">No templates yet. <a href="/admin/templates/create" class="text-link">Create your first template</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
