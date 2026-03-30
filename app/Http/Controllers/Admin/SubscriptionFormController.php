<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionForm;
use App\Models\Audience;
use Illuminate\Http\Request;

class SubscriptionFormController extends Controller
{
    public function index()
    {
        return view('admin.forms.index', [
            'forms' => SubscriptionForm::with('audience')->orderByDesc('created_at')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.forms.form', ['form' => null, 'audiences' => Audience::all()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'audience_id' => 'required|uuid',
            'double_opt_in' => 'nullable|boolean',
            'redirect_url' => 'nullable|url',
            'confirmation_subject' => 'nullable|string',
        ]);
        $data['double_opt_in'] = $request->boolean('double_opt_in');
        SubscriptionForm::create($data);
        return redirect('/admin/forms')->with('success', 'Form created');
    }

    public function destroy(string $id)
    {
        SubscriptionForm::findOrFail($id)->delete();
        return redirect('/admin/forms')->with('success', 'Form deleted');
    }

    public function embed(string $id)
    {
        $form = SubscriptionForm::findOrFail($id);
        return view('admin.forms.embed', ['form' => $form]);
    }
}
