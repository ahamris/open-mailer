@extends('layouts.admin')
@section('title', 'Domeinen')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Domeinen</h2>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('add-domain').showModal()">+ Domein toevoegen</button>
</div>

<div class="grid gap-4">
    @forelse($domains as $domain)
    <div class="bg-base-100 rounded-box shadow p-5">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-lg font-semibold">{{ $domain->name }}</h3>
                <span class="badge badge-{{ $domain->status === 'verified' ? 'success' : 'warning' }} badge-sm">{{ $domain->status }}</span>
            </div>
            <div class="flex gap-2">
                <form method="POST" action="/admin/domains/{{ $domain->id }}/verify">
                    @csrf
                    <button class="btn btn-outline btn-sm">Verifieer DNS</button>
                </form>
                <form method="POST" action="/admin/domains/{{ $domain->id }}" onsubmit="return confirm('Domein verwijderen?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-ghost btn-sm text-error">Verwijder</button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-2">
            <div class="flex items-center gap-2">
                <span class="badge badge-{{ $domain->spf_valid ? 'success' : 'error' }} badge-sm">SPF</span>
                {{ $domain->spf_valid ? 'Geldig' : 'Niet gevonden' }}
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-{{ $domain->dkim_valid ? 'success' : 'error' }} badge-sm">DKIM</span>
                {{ $domain->dkim_valid ? 'Geldig' : 'Niet gevonden' }}
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-{{ $domain->dmarc_valid ? 'success' : 'error' }} badge-sm">DMARC</span>
                {{ $domain->dmarc_valid ? 'Geldig' : 'Niet gevonden' }}
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-{{ $domain->mx_valid ? 'success' : 'error' }} badge-sm">MX</span>
                {{ $domain->mx_valid ? 'Geldig' : 'Niet gevonden' }}
            </div>
        </div>

        @if($domain->dns_records)
        <div class="collapse collapse-arrow mt-3 bg-base-200">
            <input type="checkbox" />
            <div class="collapse-title font-medium text-sm">DNS Records (voor configuratie)</div>
            <div class="collapse-content">
                <div class="overflow-x-auto">
                    <table class="table table-xs">
                        <thead><tr><th>Type</th><th>Host</th><th>Value</th></tr></thead>
                        <tbody>
                            @foreach($domain->dns_records as $record)
                            <tr>
                                <td><code>{{ $record['type'] }}</code></td>
                                <td><code class="text-xs">{{ $record['host'] }}</code></td>
                                <td><code class="text-xs break-all">{{ $record['value'] }}</code></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
    @empty
    <div class="text-center py-12 text-base-content/40">Nog geen domeinen geconfigureerd</div>
    @endforelse
</div>

<dialog id="add-domain" class="modal">
    <div class="modal-box">
        <h3 class="text-lg font-bold">Domein toevoegen</h3>
        <form method="POST" action="/admin/domains" class="mt-4">
            @csrf
            <fieldset class="fieldset">
                <label class="fieldset-label">Domeinnaam</label>
                <input type="text" name="name" class="input input-bordered w-full" placeholder="bijv. code-labs.nl" required>
            </fieldset>
            <div class="modal-action">
                <button type="button" class="btn" onclick="document.getElementById('add-domain').close()">Annuleer</button>
                <button type="submit" class="btn btn-primary">Toevoegen</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
@endsection
