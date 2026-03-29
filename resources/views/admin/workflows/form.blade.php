@extends('layouts.admin')
@section('title', $workflow ? 'Edit workflow' : 'New workflow')

@section('content')
<div class="mb-4">
    <a href="/admin/workflows" class="btn btn--ghost btn--sm">&larr; Terug</a>
</div>

<form method="POST" action="{{ $workflow ? '/admin/workflows/' . $workflow->id : '/admin/workflows' }}" class="max-w-4xl">
    @csrf
    @if($workflow) @method('PUT') @endif

    <div class="bg-base-100 rounded-box shadow p-5 space-y-4">
        <h2 class="card__header-title">{{ $workflow ? 'Edit workflow' : 'New workflow' }}</h2>

        <fieldset class="fieldset">
            <label class="fieldset-label">Naam</label>
            <input type="text" name="name" class="input input-bordered w-full" value="{{ $workflow->name ?? '' }}" required>
        </fieldset>

        <fieldset class="fieldset">
            <label class="fieldset-label">Beschrijving</label>
            <input type="text" name="description" class="input input-bordered w-full" value="{{ $workflow->description ?? '' }}">
        </fieldset>

        <fieldset class="fieldset">
            <label class="fieldset-label">Prioriteit (hoger = eerder uitgevoerd)</label>
            <input type="number" name="priority" class="input input-bordered w-32" value="{{ $workflow->priority ?? 0 }}" min="0" max="100">
        </fieldset>

        <!-- Triggers -->
        <div class="border border-base-300 rounded-box p-4">
            <h3 class="font-semibold mb-2">Triggers (ALS...)</h3>
            <p class="text-sm text-base-content/60 mb-3">Alle condities moeten matchen (AND logica)</p>
            <div id="triggers-container">
                <!-- Dynamisch gevuld -->
            </div>
            <button type="button" class="btn btn--ghost btn--sm mt-2" onclick="addTrigger()">+ Trigger toevoegen</button>
            <textarea name="triggers" id="triggers-json" class="hidden"></textarea>
        </div>

        <!-- Actions -->
        <div class="border border-base-300 rounded-box p-4">
            <h3 class="font-semibold mb-2">Acties (DAN...)</h3>
            <p class="text-sm text-base-content/60 mb-3">Worden sequentieel uitgevoerd</p>
            <div id="actions-container">
                <!-- Dynamisch gevuld -->
            </div>
            <button type="button" class="btn btn--ghost btn--sm mt-2" onclick="addAction()">+ Actie toevoegen</button>
            <textarea name="actions" id="actions-json" class="hidden"></textarea>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="submit" class="btn btn--primary" onclick="serializeForm()">Save</button>
        <a href="/admin/workflows" class="btn btn--ghost">Cancel</a>
    </div>
</form>

@endsection

@section('scripts')
<script>
const existingTriggers = @json($workflow->triggers ?? []);
const existingActions = @json($workflow->actions ?? []);

const triggerFields = [
    {value: 'from', label: 'Van (afzender)'},
    {value: 'to', label: 'Aan (ontvanger)'},
    {value: 'subject', label: 'Onderwerp'},
    {value: 'body', label: 'Inhoud'},
    {value: 'has_attachment', label: 'Heeft bijlage'},
    {value: 'spf', label: 'SPF resultaat'},
    {value: 'dkim', label: 'DKIM resultaat'},
];

const operators = [
    {value: 'contains', label: 'bevat'},
    {value: 'equals', label: 'is gelijk aan'},
    {value: 'starts_with', label: 'begint met'},
    {value: 'ends_with', label: 'eindigt met'},
    {value: 'regex', label: 'regex match'},
    {value: 'is_true', label: 'is waar'},
    {value: 'is_false', label: 'is onwaar'},
];

const actionTypes = [
    {value: 'auto_reply', label: 'Automatisch antwoord', fields: ['template']},
    {value: 'ai_reply', label: 'AI Antwoord', fields: ['instructions', 'auto_send']},
    {value: 'forward', label: 'Doorsturen', fields: ['to']},
    {value: 'label', label: 'Label / Map', fields: ['folder']},
    {value: 'move', label: 'Verplaatsen', fields: ['folder']},
    {value: 'mark_read', label: 'Markeer als gelezen', fields: []},
    {value: 'star', label: 'Ster toevoegen', fields: []},
    {value: 'webhook', label: 'Webhook', fields: ['url']},
];

let triggerCount = 0;
let actionCount = 0;

function addTrigger(data = {}) {
    const i = triggerCount++;
    const container = document.getElementById('triggers-container');
    const div = document.createElement('div');
    div.className = 'flex gap-2 mb-2 items-center';
    div.innerHTML = `
        <select data-trigger="${i}" data-key="field" class="select select-bordered select-sm w-40">
            ${triggerFields.map(f => `<option value="${f.value}" ${data.field === f.value ? 'selected' : ''}>${f.label}</option>`).join('')}
        </select>
        <select data-trigger="${i}" data-key="operator" class="select select-bordered select-sm w-36">
            ${operators.map(o => `<option value="${o.value}" ${data.operator === o.value ? 'selected' : ''}>${o.label}</option>`).join('')}
        </select>
        <input data-trigger="${i}" data-key="value" class="input input-bordered input-sm flex-1" value="${data.value || ''}" placeholder="waarde">
        <button type="button" class="btn btn--ghost btn--sm text-error" onclick="this.parentElement.remove()">X</button>
    `;
    container.appendChild(div);
}

function addAction(data = {}) {
    const i = actionCount++;
    const container = document.getElementById('actions-container');
    const div = document.createElement('div');
    div.className = 'flex gap-2 mb-2 items-start flex-wrap';
    div.id = `action-${i}`;
    div.innerHTML = `
        <select data-action="${i}" data-key="type" class="select select-bordered select-sm w-44" onchange="updateActionFields(${i}, this.value)">
            ${actionTypes.map(a => `<option value="${a.value}" ${data.type === a.value ? 'selected' : ''}>${a.label}</option>`).join('')}
        </select>
        <div id="action-fields-${i}" class="flex-1 flex gap-2 flex-wrap"></div>
        <button type="button" class="btn btn--ghost btn--sm text-error" onclick="this.parentElement.remove()">X</button>
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
            container.innerHTML += `<label class="flex items-center gap-2 text-sm"><input type="checkbox" data-action="${i}" data-key="auto_send" class="checkbox checkbox-sm" ${data.auto_send ? 'checked' : ''}> Auto verzenden</label>`;
        } else {
            container.innerHTML += `<input data-action="${i}" data-key="${field}" class="input input-bordered input-sm flex-1" value="${data[field] || ''}" placeholder="${field}">`;
        }
    });
}

function serializeForm() {
    // Serialize triggers
    const triggers = [];
    document.querySelectorAll('[data-trigger]').forEach(el => {
        const idx = el.dataset.trigger;
        if (!triggers[idx]) triggers[idx] = {};
        triggers[idx][el.dataset.key] = el.value;
    });
    document.getElementById('triggers-json').value = JSON.stringify(triggers.filter(Boolean));

    // Serialize actions
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

// Init existing data
existingTriggers.forEach(t => addTrigger(t));
existingActions.forEach(a => addAction(a));
if (existingTriggers.length === 0) addTrigger();
if (existingActions.length === 0) addAction();
</script>
@endsection
