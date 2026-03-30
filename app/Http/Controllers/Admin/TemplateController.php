<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    public function index()
    {
        return view('admin.templates.index', ['templates' => Template::orderByDesc('updated_at')->get()]);
    }

    public function create()
    {
        return view('admin.templates.form', ['template' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'html_body' => 'nullable|string',
            'text_body' => 'nullable|string',
        ]);
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);
        Template::create($data);
        return redirect('/admin/templates')->with('success', 'Template created');
    }

    public function edit(string $id)
    {
        return view('admin.templates.form', ['template' => Template::findOrFail($id)]);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'html_body' => 'nullable|string',
            'text_body' => 'nullable|string',
        ]);
        Template::findOrFail($id)->update($data);
        return redirect('/admin/templates')->with('success', 'Template updated');
    }

    public function destroy(string $id)
    {
        Template::findOrFail($id)->delete();
        return redirect('/admin/templates')->with('success', 'Template deleted');
    }

    // GrapesJS drag-and-drop builder
    public function builder(string $id)
    {
        return view('admin.templates.builder', ['template' => Template::findOrFail($id)]);
    }

    public function saveBuilder(Request $request, string $id)
    {
        $template = Template::findOrFail($id);
        $template->update([
            'html_body' => $request->input('html'),
            'grapes_json' => $request->input('grapes_json'),
        ]);
        return response()->json(['success' => true]);
    }
}
