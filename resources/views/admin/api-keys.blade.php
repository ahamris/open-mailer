@extends('layouts.admin')
@section('title', 'API Keys')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">API Keys</h2>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('create-key').showModal()">+ Nieuwe key</button>
</div>

@if(session('new_key'))
<div class="alert alert-warning mb-4">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <div>
        <p class="font-bold">Kopieer je API key - wordt niet meer getoond!</p>
        <code class="text-sm bg-base-300 px-2 py-1 rounded select-all">{{ session('new_key') }}</code>
    </div>
</div>
@endif

<div class="bg-base-100 rounded-box shadow">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Key prefix</th>
                    <th>Rechten</th>
                    <th>Laatst gebruikt</th>
                    <th>Aangemaakt</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($apiKeys as $key)
                <tr>
                    <td class="font-medium">{{ $key->name }}</td>
                    <td><code class="text-sm">{{ $key->key_prefix }}...</code></td>
                    <td><span class="badge badge-ghost badge-sm">{{ $key->permission }}</span></td>
                    <td class="text-sm text-base-content/60">{{ $key->last_used_at?->diffForHumans() ?? 'Nooit' }}</td>
                    <td class="text-sm text-base-content/60">{{ $key->created_at->format('d M Y') }}</td>
                    <td>
                        <form method="POST" action="/admin/api-keys/{{ $key->id }}" onsubmit="return confirm('Weet je zeker dat je deze key wilt verwijderen?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-ghost btn-xs text-error">Verwijder</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-base-content/40 py-8">Nog geen API keys</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<dialog id="create-key" class="modal">
    <div class="modal-box">
        <h3 class="text-lg font-bold">Nieuwe API Key</h3>
        <form method="POST" action="/admin/api-keys" class="mt-4">
            @csrf
            <fieldset class="fieldset">
                <label class="fieldset-label">Naam</label>
                <input type="text" name="name" class="input input-bordered w-full" placeholder="bijv. Production" required>
            </fieldset>
            <fieldset class="fieldset mt-3">
                <label class="fieldset-label">Rechten</label>
                <select name="permission" class="select select-bordered w-full">
                    <option value="full_access">Volledige toegang</option>
                    <option value="sending_access">Alleen verzenden</option>
                </select>
            </fieldset>
            <div class="modal-action">
                <button type="button" class="btn" onclick="document.getElementById('create-key').close()">Annuleer</button>
                <button type="submit" class="btn btn-primary">Aanmaken</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
@endsection
