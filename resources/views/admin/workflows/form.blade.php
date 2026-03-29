@extends('layouts.admin')
@section('title', $workflow ? 'Edit Workflow' : 'New Workflow')
@section('subtitle', $workflow ? 'Modify triggers and actions' : 'Create an automated email workflow')

@section('actions')
<a href="/admin/workflows" class="btn btn--ghost btn--sm">&larr; Back to Workflows</a>
@endsection

@section('content')
<form method="POST" action="{{ $workflow ? '/admin/workflows/' . $workflow->id : '/admin/workflows' }}" id="workflow-form" style="max-width:52rem;">
    @csrf
    @if($workflow) @method('PUT') @endif

    {{-- Basic Info --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__header">
            <span class="card__header-title">Basic Information</span>
        </div>
        <div class="card__body">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-input" value="{{ $workflow->name ?? '' }}" required placeholder="e.g. Auto-reply to support emails">
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-input" value="{{ $workflow->description ?? '' }}" placeholder="Optional description of what this workflow does">
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Priority (higher = runs first)</label>
                <input type="number" name="priority" class="form-input" style="width:8rem;" value="{{ $workflow->priority ?? 0 }}" min="0" max="100">
            </div>
        </div>
    </div>

    {{-- Triggers --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__header">
            <span class="card__header-title">Triggers (IF...)</span>
        </div>
        <div class="card__body">
            <p class="text-sm text-muted" style="margin-bottom:.75rem;">All conditions must match (AND logic)</p>
            <div id="triggers-container"></div>
            <button type="button" class="btn btn--ghost btn--sm" onclick="addTrigger()" style="margin-top:.5rem;">+ Add Trigger</button>
            <textarea name="triggers" id="triggers-json" style="display:none;"></textarea>
        </div>
    </div>

    {{-- Actions --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__header">
            <span class="card__header-title">Actions (THEN...)</span>
        </div>
        <div class="card__body">
            <p class="text-sm text-muted" style="margin-bottom:.75rem;">Actions are executed sequentially</p>
            <div id="actions-container"></div>
            <button type="button" class="btn btn--ghost btn--sm" onclick="addAction()" style="margin-top:.5rem;">+ Add Action</button>
            <textarea name="actions" id="actions-json" style="display:none;"></textarea>
        </div>
    </div>

    {{-- Submit --}}
    <div style="display:flex;gap:.5rem;">
        <button type="submit" class="btn btn--primary">Save Workflow</button>
        <a href="/admin/workflows" class="btn btn--ghost">Cancel</a>
    </div>
</form>
@endsection

@section('scripts')
<script>
const existingTriggers = @json($workflow->triggers ?? []);
const existingActions = @json($workflow->actions ?? []);

const triggerFields = [
    {value: 'from', label: 'From (sender)'},
    {value: 'to', label: 'To (recipient)'},
    {value: 'subject', label: 'Subject'},
    {value: 'body', label: 'Body'},
    {value: 'has_attachment', label: 'Has attachment'},
    {value: 'spf', label: 'SPF result'},
    {value: 'dkim', label: 'DKIM result'},
];

const operators = [
    {value: 'contains', label: 'contains'},
    {value: 'equals', label: 'equals'},
    {value: 'starts_with', label: 'starts with'},
    {value: 'ends_with', label: 'ends with'},
    {value: 'regex', label: 'regex match'},
    {value: 'is_true', label: 'is true'},
    {value: 'is_false', label: 'is false'},
];

const actionTypes = [
    {value: 'auto_reply', label: 'Auto Reply', fields: ['template']},
    {value: 'ai_reply', label: 'AI Reply', fields: ['instructions', 'auto_send']},
    {value: 'forward', label: 'Forward', fields: ['to']},
    {value: 'label', label: 'Label / Folder', fields: ['folder']},
    {value: 'move', label: 'Move to Folder', fields: ['folder']},
    {value: 'mark_read', label: 'Mark as Read', fields: []},
    {value: 'star', label: 'Star', fields: []},
    {value: 'webhook', label: 'Webhook', fields: ['url']},
];

let triggerCount = 0;
let actionCount = 0;

function addTrigger(data = {}) {
    const i = triggerCount++;
    const container = document.getElementById('triggers-container');
    const div = document.createElement('div');
    div.style.cssText = 'display:flex;gap:.5rem;margin-bottom:.5rem;align-items:center;';
    div.innerHTML = `
        <select data-trigger="${i}" data-key="field" class="form-select" style="width:10rem;">
            ${triggerFields.map(f => `<option value="${f.value}" ${data.field === f.value ? 'selected' : ''}>${f.label}</option>`).join('')}
        </select>
        <select data-trigger="${i}" data-key="operator" class="form-select" style="width:9rem;">
            ${operators.map(o => `<option value="${o.value}" ${data.operator === o.value ? 'selected' : ''}>${o.label}</option>`).join('')}
        </select>
        <input data-trigger="${i}" data-key="value" class="form-input" style="flex:1;" value="${data.value || ''}" placeholder="Value">
        <button type="button" class="btn btn--ghost-danger btn--sm" onclick="this.parentElement.remove()">X</button>
    `;
    container.appendChild(div);
}

function addAction(data = {}) {
    const i = actionCount++;
    const container = document.getElementById('actions-container');
    const div = document.createElement('div');
    div.style.cssText = 'display:flex;gap:.5rem;margin-bottom:.5rem;align-items:start;flex-wrap:wrap;';
    div.id = `action-${i}`;
    div.innerHTML = `
        <select data-action="${i}" data-key="type" class="form-select" style="width:11rem;" onchange="updateActionFields(${i}, this.value)">
            ${actionTypes.map(a => `<option value="${a.value}" ${data.type === a.value ? 'selected' : ''}>${a.label}</option>`).join('')}
        </select>
        <div id="action-fields-${i}" style="flex:1;display:flex;gap:.5rem;flex-wrap:wrap;"></div>
        <button type="button" class="btn btn--ghost-danger btn--sm" onclick="this.parentElement.remove()">X</button>
    `;
    container.appendChild(div);
    updateActionFields(i, data.type || 'auto_reply', data);
}

function updateActionFields(i, type, data = {}) {
    const container = document.getElementById(`action-fields-${i}`);
    const actionDef = actionTypes.find(a => a.value === type);
    container.innerHTML = '';
    (actionDef?.fields || []).forEach(field => {
        if (field === 'auto_send') {
            container.innerHTML += `<label style="display:flex;align-items:center;gap:.375rem;font-size:.8125rem;"><input type="checkbox" data-action="${i}" data-key="auto_send" ${data.auto_send ? 'checked' : ''}> Auto send</label>`;
        } else {
            const placeholder = {template: 'HTML template', instructions: 'AI instructions', to: 'Email address', folder: 'Folder name', url: 'Webhook URL'}[field] || field;
            container.innerHTML += `<input data-action="${i}" data-key="${field}" class="form-input" style="flex:1;min-width:10rem;" value="${data[field] || ''}" placeholder="${placeholder}">`;
        }
    });
}

function serializeForm() {
    const triggers = [];
    document.querySelectorAll('[data-trigger]').forEach(el => {
        const idx = el.dataset.trigger;
        if (!triggers[idx]) triggers[idx] = {};
        triggers[idx][el.dataset.key] = el.value;
    });
    document.getElementById('triggers-json').value = JSON.stringify(triggers.filter(Boolean));

    const actions = [];
    document.querySelectorAll('[data-action]').forEach(el => {
        const idx = el.dataset.action;
        if (!actions[idx]) actions[idx] = {};
        if (el.type === 'checkbox') {
            actions[idx][el.dataset.key] = el.checked;
        } else {
            actions[idx][el.dataset.key] = el.value;
        }
    });
    document.getElementById('actions-json').value = JSON.stringify(actions.filter(Boolean));
}

document.getElementById('workflow-form').addEventListener('submit', function(e) {
    serializeForm();
});

// Initialize existing data
existingTriggers.forEach(t => addTrigger(t));
existingActions.forEach(a => addAction(a));
if (existingTriggers.length === 0) addTrigger();
if (existingActions.length === 0) addAction();
</script>
@endsection
