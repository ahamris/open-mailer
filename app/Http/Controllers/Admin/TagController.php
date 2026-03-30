<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:50|unique:tags,name', 'color' => 'nullable|string']);
        Tag::create($data);
        return back()->with('success', 'Tag created');
    }

    public function destroy(string $id)
    {
        Tag::findOrFail($id)->delete();
        return back()->with('success', 'Tag deleted');
    }
}
