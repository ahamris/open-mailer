<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Services\AiService;
use Illuminate\Http\Request;

class AiSettingsController extends Controller
{
    public function index()
    {
        return view('admin.ai-settings', [
            'setting' => AiSetting::first(),
            'providers' => AiService::availableProviders(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|in:anthropic,openai,gemini,ollama',
            'model' => 'nullable|string',
            'api_key' => 'nullable|string',
            'base_url' => 'nullable|url',
        ]);

        AiSetting::updateOrCreate(['id' => 1], $validated + ['active' => true]);

        return redirect('/admin/ai-settings')->with('success', 'AI instellingen opgeslagen');
    }

    public function test(Request $request)
    {
        $ai = app(AiService::class);
        $result = $ai->compose('Zeg "CLOM AI werkt!" in één zin.');

        return response()->json([
            'success' => empty($result['error']),
            'response' => $result['content'],
            'provider' => $result['provider'],
            'model' => $result['model'],
        ]);
    }
}
