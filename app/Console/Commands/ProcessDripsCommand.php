<?php
namespace App\Console\Commands;

use App\Services\DripEngine;
use Illuminate\Console\Command;

class ProcessDripsCommand extends Command
{
    protected $signature = 'drip:process';
    protected $description = 'Process pending drip campaign enrollments';

    public function handle(DripEngine $engine): void
    {
        $processed = $engine->processEnrollments();
        $this->info("Processed {$processed} drip enrollments");
    }
}
