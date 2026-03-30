@extends('layouts.admin')
@section('title', $campaign->name)
@section('subtitle', 'Drip campaign detail &amp; enrollment tracking')

@section('actions')
<a href="/admin/drips" class="btn btn--ghost btn--sm">&larr; Back to Drip Campaigns</a>
<a href="/admin/drips/{{ $campaign->id }}/edit" class="btn btn--secondary btn--sm">Edit Campaign</a>
@endsection

@section('content')
{{-- Campaign info --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card__header" style="display:flex;align-items:center;justify-content:space-between;">
        <span class="card__header-title">Campaign Overview</span>
        @if($campaign->is_active)
            <span class="badge badge--success"><span class="dot"></span>Active</span>
        @else
            <span class="badge badge--neutral">Paused</span>
        @endif
    </div>
    <div class="card__body">
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;">
            <div>
                <div style="font-size:.75rem;color:var(--text-tertiary);margin-bottom:.25rem;">Audience</div>
                <div style="font-weight:500;">{{ $campaign->audience?->name ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:.75rem;color:var(--text-tertiary);margin-bottom:.25rem;">Trigger</div>
                <div>
                    @switch($campaign->trigger_type)
                        @case('on_subscribe')
                            <span class="badge badge--info">On Subscribe</span>
                            @break
                        @case('on_tag')
                            <span class="badge badge--warning">On Tag</span>
                            @break
                        @case('manual')
                            <span class="badge badge--neutral">Manual</span>
                            @break
                        @default
                            <span class="badge badge--neutral">{{ ucfirst($campaign->trigger_type ?? 'none') }}</span>
                    @endswitch
                </div>
            </div>
            <div>
                <div style="font-size:.75rem;color:var(--text-tertiary);margin-bottom:.25rem;">From Address</div>
                <div style="font-weight:500;">{{ $campaign->from_address ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:.75rem;color:var(--text-tertiary);margin-bottom:.25rem;">Total Enrolled</div>
                <div style="font-weight:500;">{{ $campaign->enrollments->count() }}</div>
            </div>
        </div>
        @if($campaign->description)
            <div style="margin-top:1rem;color:var(--text-secondary);font-size:.875rem;">{{ $campaign->description }}</div>
        @endif
    </div>
</div>

{{-- Steps timeline --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card__header">
        <span class="card__header-title">Steps Timeline</span>
    </div>
    <div class="card__body">
        @if($campaign->steps->isEmpty())
            <p style="color:var(--text-tertiary);text-align:center;padding:2rem 0;">No steps configured. <a href="/admin/drips/{{ $campaign->id }}/edit" class="text-link">Edit this campaign</a> to add steps.</p>
        @else
            <div style="position:relative;padding-left:2rem;">
                {{-- Vertical line --}}
                <div style="position:absolute;left:.625rem;top:0;bottom:0;width:2px;background:var(--border);"></div>

                @foreach($campaign->steps->sortBy('order') as $i => $step)
                    @php
                        $stepEnrollments = $campaign->enrollments->where('current_step', $i);
                        $stepSent = $step->sent_count ?? 0;
                        $stepOpens = $step->open_count ?? 0;
                        $stepClicks = $step->click_count ?? 0;
                    @endphp
                    <div style="position:relative;padding-bottom:1.5rem;">
                        {{-- Timeline dot --}}
                        <div style="position:absolute;left:-1.625rem;top:.25rem;width:.75rem;height:.75rem;border-radius:50%;border:2px solid {{ $step->type === 'email' ? 'var(--b500)' : ($step->type === 'delay' ? 'var(--n400)' : 'var(--g500)') }};background:var(--n0);"></div>

                        <div style="background:var(--n50,#f9fafb);border:1px solid var(--border);border-radius:.5rem;padding:1rem;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
                                <div style="display:flex;align-items:center;gap:.5rem;">
                                    <span style="font-size:.75rem;font-weight:600;color:var(--text-tertiary);">STEP {{ $i + 1 }}</span>
                                    @if($step->type === 'email')
                                        <span class="badge badge--info">Email</span>
                                    @elseif($step->type === 'delay')
                                        <span class="badge badge--neutral">Delay</span>
                                    @elseif($step->type === 'condition')
                                        <span class="badge badge--warning">Condition</span>
                                    @endif
                                </div>
                                <span style="font-size:.75rem;color:var(--text-tertiary);">{{ $stepEnrollments->count() }} currently at this step</span>
                            </div>

                            @if($step->type === 'email')
                                <div style="font-weight:500;margin-bottom:.5rem;">{{ $step->subject ?? 'Untitled email' }}</div>
                                <div style="display:flex;gap:1.5rem;font-size:.8125rem;color:var(--text-secondary);">
                                    <span>Sent: <strong>{{ $stepSent }}</strong></span>
                                    <span>Opens: <strong>{{ $stepOpens }}</strong> {{ $stepSent > 0 ? '(' . round($stepOpens / $stepSent * 100) . '%)' : '' }}</span>
                                    <span>Clicks: <strong>{{ $stepClicks }}</strong> {{ $stepSent > 0 ? '(' . round($stepClicks / $stepSent * 100) . '%)' : '' }}</span>
                                </div>
                            @elseif($step->type === 'delay')
                                <div style="color:var(--text-secondary);font-size:.875rem;">
                                    Wait {{ $step->delay_days ?? 0 }} day(s) {{ ($step->delay_hours ?? 0) > 0 ? 'and ' . $step->delay_hours . ' hour(s)' : '' }}
                                </div>
                            @elseif($step->type === 'condition')
                                <div style="color:var(--text-secondary);font-size:.875rem;">
                                    If <strong>{{ $step->condition_field ?? '—' }}</strong> = <strong>{{ $step->condition_value ?? '—' }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Enrollments table --}}
<div class="card">
    <div class="card__header">
        <span class="card__header-title">Enrollments</span>
    </div>
    <table class="tbl">
        <thead>
            <tr>
                <th>Contact</th>
                <th>Current Step</th>
                <th>Status</th>
                <th>Next Action</th>
                <th>Enrolled At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($campaign->enrollments as $enrollment)
            <tr>
                <td class="tbl__text-primary font-medium">{{ $enrollment->contact->email ?? '—' }}</td>
                <td class="tbl__text-muted">
                    @if($enrollment->status === 'completed')
                        Done
                    @else
                        Step {{ ($enrollment->current_step ?? 0) + 1 }} of {{ $campaign->steps->count() }}
                    @endif
                </td>
                <td>
                    @switch($enrollment->status)
                        @case('active')
                            <span class="badge badge--success"><span class="dot"></span>Active</span>
                            @break
                        @case('completed')
                            <span class="badge badge--info">Completed</span>
                            @break
                        @case('paused')
                            <span class="badge badge--warning">Paused</span>
                            @break
                        @case('cancelled')
                            <span class="badge badge--danger">Cancelled</span>
                            @break
                        @default
                            <span class="badge badge--neutral">{{ ucfirst($enrollment->status) }}</span>
                    @endswitch
                </td>
                <td class="tbl__text-muted nowrap">{{ $enrollment->next_action_at ? $enrollment->next_action_at->format('M d, Y H:i') : '—' }}</td>
                <td class="tbl__text-muted nowrap">{{ $enrollment->created_at->format('M d, Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="tbl__empty">No contacts enrolled in this campaign yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
