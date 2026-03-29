<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;

class ApiKeyController extends Controller
{
    public function index()
    {
        return view('admin.api-keys', [
            'apiKeys' => ApiKey::orderByDesc('created_at')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permission' => 'in:full_access,sending_access',
        ]);

        $result = ApiKey::generate($validated['name'], $validated['permission'] ?? 'full_access');

        return redirect('/admin/api-keys')->with('new_key', $result['raw_key']);
    }

    public function destroy(string $id)
    {
        ApiKey::findOrFail($id)->delete();
        return redirect('/admin/api-keys')->with('success', 'API key verwijderd');
    }
}
