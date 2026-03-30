<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Audience;
use Illuminate\Http\Request;

class ContactImportController extends Controller
{
    public function showImport()
    {
        return view('admin.contacts.import', ['audiences' => Audience::all()]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240',
            'audience_id' => 'nullable|uuid',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);

        // Normalize header names
        $header = array_map(fn ($h) => strtolower(trim($h)), $header);
        $emailCol = array_search('email', $header);
        $firstCol = array_search('first_name', $header) !== false ? array_search('first_name', $header) : array_search('first name', $header);
        $lastCol = array_search('last_name', $header) !== false ? array_search('last_name', $header) : array_search('last name', $header);

        if ($emailCol === false) {
            return back()->with('error', 'CSV must have an "email" column');
        }

        $imported = 0; $skipped = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $email = strtolower(trim($row[$emailCol] ?? ''));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $skipped++; continue; }

            $contact = Contact::firstOrCreate(
                ['email' => $email],
                [
                    'first_name' => $firstCol !== false ? trim($row[$firstCol] ?? '') : null,
                    'last_name' => $lastCol !== false ? trim($row[$lastCol] ?? '') : null,
                ]
            );

            if ($request->audience_id) {
                $contact->audiences()->syncWithoutDetaching([$request->audience_id]);
            }
            $imported++;
        }
        fclose($handle);

        return redirect('/admin/contacts')->with('success', "Imported {$imported} contacts" . ($skipped ? ", {$skipped} skipped" : ''));
    }
}
