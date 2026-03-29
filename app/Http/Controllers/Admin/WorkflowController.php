<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function index()
    {
        return view('admin.workflows.index', [
            'workflows' => Workflow::orderBy('priority', 'desc')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.workflows.form', ['workflow' => null]);
    }

    public function store(Request $request)
    {
        Workflow::create($this->validateWorkflow($request));
        return redirect('/admin/workflows')->with('success', 'Workflow aangemaakt');
    }

    public function edit(string $id)
    {
        return view('admin.workflows.form', ['workflow' => Workflow::findOrFail($id)]);
    }

    public function update(Request $request, string $id)
    {
        Workflow::findOrFail($id)->update($this->validateWorkflow($request));
        return redirect('/admin/workflows')->with('success', 'Workflow bijgewerkt');
    }

    public function destroy(string $id)
    {
        Workflow::findOrFail($id)->delete();
        return redirect('/admin/workflows')->with('success', 'Workflow verwijderd');
    }

    public function toggle(string $id)
    {
        $w = Workflow::findOrFail($id);
        $w->update(['active' => !$w->active]);
        return back();
    }

    public function logs(string $id)
    {
        $workflow = Workflow::findOrFail($id);
        return view('admin.workflows.logs', [
            'workflow' => $workflow,
            'logs' => $workflow->logs()->with('email')->orderByDesc('created_at')->paginate(25),
        ]);
    }

    private function validateWorkflow(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'integer|min:0|max:100',
            'triggers' => 'required|json',
            'actions' => 'required|json',
        ]);

        $data['triggers'] = json_decode($data['triggers'], true);
        $data['actions'] = json_decode($data['actions'], true);

        return $data;
    }
}
