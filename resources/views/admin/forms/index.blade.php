@extends('layouts.admin')
@section('title', 'Subscription Forms')

@section('actions')
<a href="/admin/forms/create" class="btn btn--primary btn--sm">+ Create form</a>
@endsection

@section('content')
<div class="card">
    <table class="tbl">
        <thead>
            <tr>
                <th>Name</th>
                <th>Audience</th>
                <th>Double Opt-in</th>
                <th>Submissions</th>
                <th>Status</th>
                <th style="width:1%"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($forms as $form)
            <tr>
                <td class="tbl__text-primary">
                    <a href="/admin/forms/{{ $form->id }}/edit" class="text-link">{{ $form->name }}</a>
                </td>
                <td>
                    @if($form->audience)
                        <span class="badge badge--info">{{ $form->audience->name }}</span>
                    @else
                        <span class="tbl__text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($form->double_opt_in)
                        <span class="badge badge--success">Enabled</span>
                    @else
                        <span class="badge badge--neutral">Disabled</span>
                    @endif
                </td>
                <td class="tbl__text-muted">{{ $form->submissions_count ?? 0 }}</td>
                <td>
                    @if($form->is_active)
                        <span class="badge badge--success"><span class="dot"></span>Active</span>
                    @else
                        <span class="badge badge--neutral"><span class="dot"></span>Inactive</span>
                    @endif
                </td>
                <td class="nowrap">
                    <div style="display:flex;align-items:center;gap:.25rem;">
                        <a href="/admin/forms/{{ $form->id }}/embed" class="btn btn--ghost btn--sm">Embed</a>
                        <a href="/admin/forms/{{ $form->id }}/edit" class="btn btn--ghost btn--sm">Edit</a>
                        <form method="POST" action="/admin/forms/{{ $form->id }}" onsubmit="return confirm('Delete this subscription form?')">
                            @csrf @method('DELETE')
                            <button class="btn btn--ghost-danger btn--sm">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="tbl__empty">No subscription forms yet. <a href="/admin/forms/create" class="text-link">Create your first form</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
