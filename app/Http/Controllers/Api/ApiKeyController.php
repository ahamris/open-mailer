<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;

class ApiKeyController extends Controller
{
    public function index()
    {
        $keys = ApiKey::select('id', 'name', 'key_prefix', 'permission', 'created_at')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $keys]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permission' => 'in:full_access,sending_access',
        ]);

        $result = ApiKey::generate(
            $validated['name'],
            $validated['permission'] ?? 'full_access'
        );

        return response()->json([
            'id' => $result['api_key']->id,
            'token' => $result['raw_key'],
        ], 201);
    }

    public function destroy(string $id)
    {
        ApiKey::findOrFail($id)->delete();
        return response()->json(['id' => $id, 'deleted' => true]);
    }
}
