<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\AiSetting;
use App\Models\Email;
use Laravel\Ai\Enums\Lab;

class AiService
{
    private const PROVIDER_MAP = [
        'anthropic' => Lab::Anthropic,
        'openai' => Lab::OpenAI,
        'gemini' => Lab::Gemini,
        'ollama' => Lab::Ollama,
    ];

    private const DEFAULT_MODELS = [
        'anthropic' => 'claude-sonnet-4-20250514',
        'openai' => 'gpt-4o-mini',
        'gemini' => 'gemini-2.0-flash',
        'ollama' => 'llama3.1',
    ];

    public function getActiveProvider(): ?AiSetting
    {
        return AiSetting::where('active', true)->first();
    }

    public function isConfigured(): bool
    {
        $setting = $this->getActiveProvider();
        if ($setting && $setting->api_key) return true;

        // Fallback to .env
        return !empty(config('ai.providers.anthropic.key'))
            || !empty(config('ai.providers.openai.key'))
            || !empty(config('ai.providers.gemini.key'));
    }

    public function compose(string $prompt, ?Email $replyTo = null): array
    {
        $this->applyProviderConfig();

        $systemPrompt = "Je bent een professionele e-mailassistent voor CodeLabs B.V., een Nederlands bedrijf gespecialiseerd in open source oplossingen voor de overheid. Schrijf e-mails in het Nederlands tenzij anders gevraagd. Wees professioneel maar toegankelijk. Geef alleen de e-mail body terug, geen onderwerp of metadata.";

        $userPrompt = $prompt;
        if ($replyTo) {
            $body = strip_tags($replyTo->html_body ?? $replyTo->text_body ?? '');
            $userPrompt = "Originele e-mail:\nVan: {$replyTo->from_address}\nOnderwerp: {$replyTo->subject}\n\n{$body}\n\n---\nOpdracht: {$prompt}";
        }

        $setting = $this->getActiveProvider();
        $provider = self::PROVIDER_MAP[$setting?->provider ?? 'anthropic'] ?? Lab::Anthropic;
        $model = $setting?->model ?? self::DEFAULT_MODELS[$setting?->provider ?? 'anthropic'] ?? null;

        try {
            $response = \Laravel\Ai\agent(
                instructions: $systemPrompt,
            )->prompt(
                $userPrompt,
                provider: $provider,
                model: $model,
            );

            $text = (string) $response;
            $usage = $response->usage ?? null;

            return [
                'content' => $text,
                'model' => $model ?? 'unknown',
                'provider' => $setting?->provider ?? 'anthropic',
                'input_tokens' => $usage?->inputTokens ?? 0,
                'output_tokens' => $usage?->outputTokens ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'content' => "Fout: {$e->getMessage()}",
                'model' => $model ?? 'unknown',
                'provider' => $setting?->provider ?? 'anthropic',
                'input_tokens' => 0,
                'output_tokens' => 0,
                'error' => true,
            ];
        }
    }

    public function summarize(Email $email): array
    {
        $body = strip_tags($email->html_body ?? $email->text_body ?? '');
        return $this->compose(
            "Vat deze e-mail samen in 2-3 korte bullet points:\n\nVan: {$email->from_address}\nOnderwerp: {$email->subject}\n\n{$body}"
        );
    }

    public function suggestReply(Email $email): array
    {
        return $this->compose(
            'Stel een professioneel antwoord voor op deze e-mail. Houd het kort en actiegericht.',
            $email,
        );
    }

    public function saveConversation(Email $email, string $action, string $prompt, array $result): AiConversation
    {
        return AiConversation::create([
            'email_id' => $email->id,
            'action' => $action,
            'prompt' => $prompt,
            'response' => $result['content'],
            'model' => $result['model'],
            'input_tokens' => $result['input_tokens'],
            'output_tokens' => $result['output_tokens'],
        ]);
    }

    private function applyProviderConfig(): void
    {
        $setting = $this->getActiveProvider();
        if (!$setting) return;

        $provider = $setting->provider;
        if ($setting->api_key) {
            config(["ai.providers.{$provider}.key" => $setting->api_key]);
        }
        if ($setting->base_url) {
            config(["ai.providers.{$provider}.url" => $setting->base_url]);
        }
    }

    public static function availableProviders(): array
    {
        return [
            'anthropic' => ['name' => 'Anthropic (Claude)', 'models' => ['claude-sonnet-4-20250514', 'claude-haiku-4-5-20251001', 'claude-opus-4-20250514']],
            'openai' => ['name' => 'OpenAI (GPT)', 'models' => ['gpt-4o', 'gpt-4o-mini', 'o3-mini']],
            'gemini' => ['name' => 'Google Gemini', 'models' => ['gemini-2.5-pro', 'gemini-2.0-flash', 'gemini-2.5-flash']],
            'ollama' => ['name' => 'Ollama (Local)', 'models' => ['llama3.1', 'mistral', 'codellama', 'mixtral']],
        ];
    }
}
