<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function index()
    {
        return view('admin.domains', [
            'domains' => Domain::orderByDesc('created_at')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|unique:domains,name']);

        Domain::create([
            'name' => $validated['name'],
            'status' => 'pending',
            'dns_records' => [
                ['type' => 'TXT', 'host' => $validated['name'], 'value' => 'v=spf1 include:mail.worxone.nl ~all'],
                ['type' => 'TXT', 'host' => "clom._domainkey.{$validated['name']}", 'value' => 'v=DKIM1; k=rsa; p=<GENERATE>'],
                ['type' => 'TXT', 'host' => "_dmarc.{$validated['name']}", 'value' => "v=DMARC1; p=quarantine; rua=mailto:dmarc@{$validated['name']}"],
                ['type' => 'MX', 'host' => $validated['name'], 'value' => 'mail.worxone.nl', 'priority' => 10],
            ],
        ]);

        return redirect('/admin/domains')->with('success', "Domein {$validated['name']} toegevoegd");
    }

    public function verify(string $id)
    {
        $domain = Domain::findOrFail($id);

        $spf = $dkim = $dmarc = $mx = false;

        $txtRecords = dns_get_record($domain->name, DNS_TXT) ?: [];
        foreach ($txtRecords as $r) {
            if (str_contains($r['txt'] ?? '', 'v=spf1')) $spf = true;
        }

        $dkimRecords = dns_get_record("clom._domainkey.{$domain->name}", DNS_TXT) ?: [];
        foreach ($dkimRecords as $r) {
            if (str_contains($r['txt'] ?? '', 'v=DKIM1')) $dkim = true;
        }

        $dmarcRecords = dns_get_record("_dmarc.{$domain->name}", DNS_TXT) ?: [];
        foreach ($dmarcRecords as $r) {
            if (str_contains($r['txt'] ?? '', 'v=DMARC1')) $dmarc = true;
        }

        $mx = count(dns_get_record($domain->name, DNS_MX) ?: []) > 0;

        $domain->update([
            'spf_valid' => $spf,
            'dkim_valid' => $dkim,
            'dmarc_valid' => $dmarc,
            'mx_valid' => $mx,
            'status' => ($spf && $mx) ? 'verified' : 'pending',
            'verified_at' => ($spf && $mx) ? now() : null,
        ]);

        return redirect('/admin/domains')->with('success', 'DNS verificatie uitgevoerd');
    }

    public function destroy(string $id)
    {
        Domain::findOrFail($id)->delete();
        return redirect('/admin/domains')->with('success', 'Domein verwijderd');
    }
}
