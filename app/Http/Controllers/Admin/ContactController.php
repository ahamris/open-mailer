<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Audience;
use App\Models\Tag;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::with(['audiences', 'tags'])->orderByDesc('created_at');

        if ($audienceId = $request->get('audience')) {
            $query->whereHas('audiences', fn ($q) => $q->where('audiences.id', $audienceId));
        }
        if ($tagId = $request->get('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $tagId));
        }
        if ($search = $request->get('q')) {
            $query->where(fn ($q) => $q->where('email', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%"));
        }

        return view('admin.contacts.index', [
            'contacts' => $query->paginate(25),
            'audiences' => Audience::withCount('contacts')->get(),
            'tags' => Tag::withCount('contacts')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.contacts.form', ['contact' => null, 'audiences' => Audience::all(), 'tags' => Tag::all()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|unique:contacts,email',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'audiences' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);
        $contact = Contact::create($data);
        if (!empty($data['audiences'])) $contact->audiences()->sync($data['audiences']);
        if (!empty($data['tags'])) $contact->tags()->sync($data['tags']);
        return redirect('/admin/contacts')->with('success', 'Contact created');
    }

    public function edit(string $id)
    {
        return view('admin.contacts.form', [
            'contact' => Contact::with(['audiences', 'tags'])->findOrFail($id),
            'audiences' => Audience::all(),
            'tags' => Tag::all(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $contact = Contact::findOrFail($id);
        $data = $request->validate([
            'email' => "required|email|unique:contacts,email,{$id}",
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'audiences' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);
        $contact->update($data);
        $contact->audiences()->sync($data['audiences'] ?? []);
        $contact->tags()->sync($data['tags'] ?? []);
        return redirect('/admin/contacts')->with('success', 'Contact updated');
    }

    public function destroy(string $id)
    {
        Contact::findOrFail($id)->delete();
        return redirect('/admin/contacts')->with('success', 'Contact deleted');
    }

    public function storeAudience(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        Audience::create($data);
        return redirect('/admin/contacts')->with('success', 'Audience created');
    }

    public function destroyAudience(string $id)
    {
        Audience::findOrFail($id)->delete();
        return redirect('/admin/contacts')->with('success', 'Audience deleted');
    }
}
