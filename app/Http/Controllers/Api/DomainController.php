<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Domain::all()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|unique:domains,name']);

        $domain = Domain::create([
            'name' => $validated['name'],
            'status' => 'pending',
            'dns_records' => $this->generateDnsRecords($validated['name']),
        ]);

        return response()->json($domain, 201);
    }

    public function show(string $id)
    {
        return response()->json(Domain::findOrFail($id));
    }

    public function verify(string $id)
    {
        $domain = Domain::findOrFail($id);
        $results = $this->checkDns($domain->name);

        $domain->update([
            'spf_valid' => $results['spf'],
            'dkim_valid' => $results['dkim'],
            'dmarc_valid' => $results['dmarc'],
            'mx_valid' => $results['mx'],
            'status' => ($results['spf'] && $results['dkim'] && $results['mx']) ? 'verified' : 'pending',
            'verified_at' => ($results['spf'] && $results['dkim'] && $results['mx']) ? now() : null,
        ]);

        return response()->json($domain->fresh());
    }

    public function destroy(string $id)
    {
        Domain::findOrFail($id)->delete();
        return response()->json(['id' => $id, 'deleted' => true]);
    }

    private function generateDnsRecords(string $domain): array
    {
        return [
            ['type' => 'TXT', 'host' => $domain, 'value' => 'v=spf1 include:mail.worxone.nl ~all'],
            ['type' => 'TXT', 'host' => "clom._domainkey.{$domain}", 'value' => 'v=DKIM1; k=rsa; p=<GENERATE_KEY>'],
            ['type' => 'TXT', 'host' => "_dmarc.{$domain}", 'value' => 'v=DMARC1; p=quarantine; rua=mailto:dmarc@' . $domain],
            ['type' => 'MX', 'host' => $domain, 'value' => 'mail.worxone.nl', 'priority' => 10],
        ];
    }

    private function checkDns(string $domain): array
    {
        $spf = false;
        $dkim = false;
        $dmarc = false;
        $mx = false;

        $txtRecords = dns_get_record($domain, DNS_TXT) ?: [];
        foreach ($txtRecords as $record) {
            if (str_contains($record['txt'] ?? '', 'v=spf1')) $spf = true;
        }

        $dkimRecords = dns_get_record("clom._domainkey.{$domain}", DNS_TXT) ?: [];
        foreach ($dkimRecords as $record) {
            if (str_contains($record['txt'] ?? '', 'v=DKIM1')) $dkim = true;
        }

        $dmarcRecords = dns_get_record("_dmarc.{$domain}", DNS_TXT) ?: [];
        foreach ($dmarcRecords as $record) {
            if (str_contains($record['txt'] ?? '', 'v=DMARC1')) $dmarc = true;
        }

        $mxRecords = dns_get_record($domain, DNS_MX) ?: [];
        $mx = count($mxRecords) > 0;

        return compact('spf', 'dkim', 'dmarc', 'mx');
    }
}
