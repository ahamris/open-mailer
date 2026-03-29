<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use Illuminate\Console\Command;

class CreateApiKeyCommand extends Command
{
    protected $signature = 'apikey:create {name}';
    protected $description = 'Maak een nieuwe API key aan';

    public function handle(): void
    {
        $result = ApiKey::generate($this->argument('name'));

        $this->info("API Key aangemaakt!");
        $this->line("Name: {$result['api_key']->name}");
        $this->line("Key:  {$result['raw_key']}");
        $this->warn("Bewaar deze key veilig - hij wordt niet meer getoond.");
    }
}
