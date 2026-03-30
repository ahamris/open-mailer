@extends('layouts.admin')
@section('title', $campaign ? 'Edit Drip Campaign' : 'New Drip Campaign')
@section('subtitle', $campaign ? 'Modify campaign steps and settings' : 'Build a multi-step automated sequence')

@section('actions')
<a href="/admin/drips" class="btn btn--ghost btn--sm">&larr; Back to Drip Campaigns</a>
@endsection

@section('content')
<form method="POST" action="{{ $campaign ? '/admin/drips/' . $campaign->id : '/admin/drips' }}" id="drip-form" style="max-width:56rem;">
    @csrf
    @if($campaign) @method('PUT') @endif
    <input type="hidden" name="steps" id="steps-json">

    {{-- Campaign details --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__header">
            <span class="card__header-title">Campaign Details</span>
        </div>
        <div class="card__body">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $campaign->name ?? '') }}" required placeholder="e.g. Welcome Series">
                <p class="form-hint">Internal name — not shown to contacts.</p>
                @error('name')<p class="form-hint" style="color:var(--r500);">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea" rows="2" placeholder="Optional description for internal reference">{{ old('description', $campaign->description ?? '') }}</textarea>
                @error('description')<p class="form-hint" style="color:var(--r500);">{{ $message }}</p>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Audience</label>
                    <select name="audience_id" class="form-select" required>
                        <option value="">Select audience...</option>
                        @foreach($audiences as $audience)
                            <option value="{{ $audience->id }}" {{ old('audience_id', $campaign->audience_id ?? '') == $audience->id ? 'selected' : '' }}>
                                {{ $audience->name }} ({{ $audience->contacts_count }} contacts)
                            </option>
                        @endforeach
                    </select>
                    @error('audience_id')<p class="form-hint" style="color:var(--r500);">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Trigger</label>
                    <select name="trigger_type" class="form-select" required>
                        <option value="on_subscribe" {{ old('trigger_type', $campaign->trigger_type ?? '') === 'on_subscribe' ? 'selected' : '' }}>On Subscribe</option>
                        <option value="on_tag" {{ old('trigger_type', $campaign->trigger_type ?? '') === 'on_tag' ? 'selected' : '' }}>On Tag Added</option>
                        <option value="manual" {{ old('trigger_type', $campaign->trigger_type ?? '') === 'manual' ? 'selected' : '' }}>Manual Enrollment</option>
                    </select>
                    @error('trigger_type')<p class="form-hint" style="color:var(--r500);">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">From address</label>
                <input type="email" name="from_address" class="form-input" value="{{ old('from_address', $campaign->from_address ?? '') }}" required placeholder="noreply@yourdomain.com">
                @error('from_address')<p class="form-hint" style="color:var(--r500);">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Step builder --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__header" style="display:flex;align-items:center;justify-content:space-between;">
            <span class="card__header-title">Steps</span>
            <span id="step-count" style="font-size:.8125rem;color:var(--text-tertiary);">0 steps</span>
        </div>
        <div class="card__body">
            <div id="steps-container" style="position:relative;padding-left:2rem;min-height:2rem;">
                {{-- Vertical timeline line --}}
                <div id="timeline-line" style="position:absolute;left:.625rem;top:0;bottom:0;width:2px;background:var(--border);display:none;"></div>

                {{-- Steps are inserted here by JS --}}
            </div>

            <div style="margin-top:1rem;display:flex;align-items:center;gap:.5rem;">
                <div style="position:relative;display:inline-block;" id="add-step-wrapper">
                    <button type="button" class="btn btn--secondary btn--sm" id="add-step-btn">+ Add step</button>
                    <div id="add-step-menu" style="display:none;position:absolute;top:100%;left:0;margin-top:.25rem;background:var(--n0);border:1px solid var(--border);border-radius:.375rem;box-shadow:var(--shadow-sm);z-index:10;min-width:10rem;">
                        <button type="button" class="btn btn--ghost btn--sm" style="width:100%;justify-content:flex-start;border-radius:0;" onclick="addStep('email')">
                            <svg style="width:.875rem;height:.875rem;margin-right:.375rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Email Step
                        </button>
                        <button type="button" class="btn btn--ghost btn--sm" style="width:100%;justify-content:flex-start;border-radius:0;" onclick="addStep('delay')">
                            <svg style="width:.875rem;height:.875rem;margin-right:.375rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Delay Step
                        </button>
                        <button type="button" class="btn btn--ghost btn--sm" style="width:100%;justify-content:flex-start;border-radius:0;" onclick="addStep('condition')">
                            <svg style="width:.875rem;height:.875rem;margin-right:.375rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Condition Step
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:.5rem;">
        <button type="submit" class="btn btn--primary">{{ $campaign ? 'Update Campaign' : 'Create Campaign' }}</button>
        <a href="/admin/drips" class="btn btn--ghost">Cancel</a>
    </div>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('steps-container');
    const timelineLine = document.getElementById('timeline-line');
    const stepCountEl = document.getElementById('step-count');
    const addBtn = document.getElementById('add-step-btn');
    const addMenu = document.getElementById('add-step-menu');
    let steps = [];

    // Templates dropdown data
    const templates = @json($templates->map(fn($t) => ['id' => $t->id, 'name' => $t->name]));

    // Toggle add-step dropdown
    addBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        addMenu.style.display = addMenu.style.display === 'none' ? 'block' : 'none';
    });
    document.addEventListener('click', function() { addMenu.style.display = 'none'; });

    // Load existing steps
    const existing = @json($campaign?->steps ?? []);
    if (existing.length) {
        existing.forEach(function(s) {
            addStep(s.type, s);
        });
    }

    window.addStep = function(type, data) {
        data = data || {};
        const index = steps.length;
        steps.push({ type: type });

        const stepEl = document.createElement('div');
        stepEl.className = 'drip-step';
        stepEl.dataset.index = index;
        stepEl.style.cssText = 'position:relative;padding-bottom:1rem;';

        // Color per type
        const dotColor = type === 'email' ? 'var(--b500)' : (type === 'delay' ? 'var(--n400)' : 'var(--g500)');

        let inner = '';
        inner += '<div style="position:absolute;left:-1.625rem;top:.75rem;width:.75rem;height:.75rem;border-radius:50%;border:2px solid ' + dotColor + ';background:var(--n0);z-index:1;"></div>';
        inner += '<div style="background:var(--n50,#f9fafb);border:1px solid var(--border);border-radius:.5rem;padding:1rem;">';

        // Header
        inner += '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;">';
        inner += '<div style="display:flex;align-items:center;gap:.5rem;">';
        inner += '<span style="font-size:.75rem;font-weight:600;color:var(--text-tertiary);">STEP ' + (index + 1) + '</span>';
        if (type === 'email') inner += '<span class="badge badge--info">Email</span>';
        else if (type === 'delay') inner += '<span class="badge badge--neutral">Delay</span>';
        else if (type === 'condition') inner += '<span class="badge badge--warning">Condition</span>';
        inner += '</div>';
        inner += '<button type="button" class="btn btn--ghost-danger btn--sm" onclick="removeStep(' + index + ')" title="Remove step">&times;</button>';
        inner += '</div>';

        // Fields per type
        if (type === 'email') {
            inner += '<div class="form-group" style="margin-bottom:.75rem;">';
            inner += '<label class="form-label">Subject</label>';
            inner += '<input type="text" class="form-input step-field" data-field="subject" value="' + escAttr(data.subject || '') + '" placeholder="Email subject line">';
            inner += '</div>';
            inner += '<div class="form-group" style="margin-bottom:0;">';
            inner += '<label class="form-label">Template</label>';
            inner += '<select class="form-select step-field" data-field="template_id">';
            inner += '<option value="">No template (plain text)</option>';
            templates.forEach(function(t) {
                inner += '<option value="' + t.id + '"' + (data.template_id == t.id ? ' selected' : '') + '>' + escHtml(t.name) + '</option>';
            });
            inner += '</select>';
            inner += '</div>';
        } else if (type === 'delay') {
            inner += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">';
            inner += '<div class="form-group" style="margin-bottom:0;">';
            inner += '<label class="form-label">Days</label>';
            inner += '<input type="number" class="form-input step-field" data-field="delay_days" value="' + (data.delay_days ?? 1) + '" min="0">';
            inner += '</div>';
            inner += '<div class="form-group" style="margin-bottom:0;">';
            inner += '<label class="form-label">Hours</label>';
            inner += '<input type="number" class="form-input step-field" data-field="delay_hours" value="' + (data.delay_hours ?? 0) + '" min="0" max="23">';
            inner += '</div>';
            inner += '</div>';
        } else if (type === 'condition') {
            inner += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">';
            inner += '<div class="form-group" style="margin-bottom:0;">';
            inner += '<label class="form-label">Condition</label>';
            inner += '<select class="form-select step-field" data-field="condition_field">';
            inner += '<option value="opened_previous"' + (data.condition_field === 'opened_previous' ? ' selected' : '') + '>Opened Previous Email</option>';
            inner += '<option value="clicked_previous"' + (data.condition_field === 'clicked_previous' ? ' selected' : '') + '>Clicked Previous Email</option>';
            inner += '<option value="has_tag"' + (data.condition_field === 'has_tag' ? ' selected' : '') + '>Has Tag</option>';
            inner += '</select>';
            inner += '</div>';
            inner += '<div class="form-group" style="margin-bottom:0;">';
            inner += '<label class="form-label">Value</label>';
            inner += '<input type="text" class="form-input step-field" data-field="condition_value" value="' + escAttr(data.condition_value || '') + '" placeholder="e.g. tag name">';
            inner += '</div>';
            inner += '</div>';
        }

        inner += '</div>';
        stepEl.innerHTML = inner;
        container.appendChild(stepEl);
        updateUI();
        addMenu.style.display = 'none';
    };

    window.removeStep = function(index) {
        if (!confirm('Remove this step?')) return;
        const el = container.querySelector('.drip-step[data-index="' + index + '"]');
        if (el) el.remove();
        steps[index] = null; // Mark as removed
        reindexSteps();
        updateUI();
    };

    function reindexSteps() {
        // Compact the steps array and re-number the DOM
        const stepEls = container.querySelectorAll('.drip-step');
        steps = [];
        stepEls.forEach(function(el, i) {
            el.dataset.index = i;
            // Update step number label
            const label = el.querySelector('span[style*="font-weight:600"]');
            if (label) label.textContent = 'STEP ' + (i + 1);
            // Update remove button
            const rmBtn = el.querySelector('.btn--ghost-danger');
            if (rmBtn) rmBtn.setAttribute('onclick', 'removeStep(' + i + ')');
            steps.push({});
        });
    }

    function updateUI() {
        const count = container.querySelectorAll('.drip-step').length;
        stepCountEl.textContent = count + ' step' + (count !== 1 ? 's' : '');
        timelineLine.style.display = count > 0 ? 'block' : 'none';
    }

    function serializeSteps() {
        const result = [];
        container.querySelectorAll('.drip-step').forEach(function(el, order) {
            const fields = el.querySelectorAll('.step-field');
            const stepData = { order: order };

            // Determine type from badge
            if (el.querySelector('.badge--info')) stepData.type = 'email';
            else if (el.querySelector('.badge--neutral')) stepData.type = 'delay';
            else if (el.querySelector('.badge--warning')) stepData.type = 'condition';

            fields.forEach(function(f) {
                stepData[f.dataset.field] = f.value;
            });
            result.push(stepData);
        });
        return JSON.stringify(result);
    }

    // On submit
    document.getElementById('drip-form').addEventListener('submit', function() {
        document.getElementById('steps-json').value = serializeSteps();
    });

    function escAttr(str) { return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    function escHtml(str) { return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
});
</script>
@endsection
