<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Suppression;
use Illuminate\Http\Request;

class SuppressionController extends Controller
{
    public function index()
    {
        return view('admin.suppressions.index', [
            'suppressions' => Suppression::orderByDesc('created_at')->paginate(25),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'reason' => 'in:manual,bounce,complaint,unsubscribe',
            'note' => 'nullable|string',
        ]);
        $validated['email'] = strtolower($validated['email']);
        Suppression::firstOrCreate(['email' => $validated['email']], $validated);
        return redirect('/admin/suppressions')->with('success', "Added {$validated['email']} to suppression list");
    }

    public function destroy(string $id)
    {
        Suppression::findOrFail($id)->delete();
        return redirect('/admin/suppressions')->with('success', 'Removed from suppression list');
    }
}
