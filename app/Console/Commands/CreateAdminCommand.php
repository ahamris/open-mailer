<?php

namespace App\Console\Commands;

use App\Models\AdminUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create {name} {email} {password}';
    protected $description = 'Maak een admin gebruiker aan';

    public function handle(): void
    {
        AdminUser::create([
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'password' => Hash::make($this->argument('password')),
        ]);

        $this->info("Admin gebruiker '{$this->argument('name')}' aangemaakt!");
    }
}
