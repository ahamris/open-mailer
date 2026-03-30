<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DripCampaign;
use App\Models\DripStep;
use App\Models\Audience;
use App\Models\Template;
use App\Services\DripEngine;
use Illuminate\Http\Request;

class DripCampaignController extends Controller
{
    public function index()
    {
        return view('admin.drips.index', [
            'campaigns' => DripCampaign::with(['audience', 'steps'])->withCount('enrollments')->orderByDesc('created_at')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.drips.form', [
            'campaign' => null,
            'audiences' => Audience::withCount('contacts')->get(),
            'templates' => Template::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'audience_id' => 'required|uuid',
            'trigger_type' => 'in:subscription,tag_added,manual',
            'trigger_value' => 'nullable|string',
            'from_address' => 'required|email',
            'from_name' => 'nullable|string',
            'steps' => 'required|json',
        ]);

        $campaign = DripCampaign::create(collect($data)->except('steps')->toArray());
        $this->syncSteps($campaign, json_decode($data['steps'], true));

        return redirect('/admin/drips')->with('success', 'Drip campaign created');
    }

    public function edit(string $id)
    {
        return view('admin.drips.form', [
            'campaign' => DripCampaign::with('steps')->findOrFail($id),
            'audiences' => Audience::withCount('contacts')->get(),
            'templates' => Template::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $campaign = DripCampaign::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'audience_id' => 'required|uuid',
            'trigger_type' => 'in:subscription,tag_added,manual',
            'trigger_value' => 'nullable|string',
            'from_address' => 'required|email',
            'from_name' => 'nullable|string',
            'steps' => 'required|json',
        ]);

        $campaign->update(collect($data)->except('steps')->toArray());
        $this->syncSteps($campaign, json_decode($data['steps'], true));

        return redirect('/admin/drips')->with('success', 'Drip campaign updated');
    }

    public function toggle(string $id)
    {
        $c = DripCampaign::findOrFail($id);
        $c->update(['active' => !$c->active]);
        return back();
    }

    public function destroy(string $id)
    {
        DripCampaign::findOrFail($id)->delete();
        return redirect('/admin/drips')->with('success', 'Drip campaign deleted');
    }

    public function show(string $id)
    {
        $campaign = DripCampaign::with(['steps', 'enrollments.contact', 'audience'])->findOrFail($id);
        return view('admin.drips.show', compact('campaign'));
    }

    private function syncSteps(DripCampaign $campaign, array $steps): void
    {
        $campaign->steps()->delete();
        foreach ($steps as $i => $step) {
            DripStep::create([
                'drip_campaign_id' => $campaign->id,
                'position' => $i,
                'type' => $step['type'] ?? 'email',
                'subject' => $step['subject'] ?? null,
                'html_body' => $step['html_body'] ?? null,
                'template_id' => $step['template_id'] ?? null,
                'delay_days' => $step['delay_days'] ?? 0,
                'delay_hours' => $step['delay_hours'] ?? 0,
                'condition_field' => $step['condition_field'] ?? null,
                'condition_value' => $step['condition_value'] ?? null,
            ]);
        }
    }
}
