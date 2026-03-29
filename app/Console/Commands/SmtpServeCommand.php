<?php

namespace App\Console\Commands;

use App\Smtp\SmtpServer;
use Illuminate\Console\Command;

class SmtpServeCommand extends Command
{
    protected $signature = 'smtp:serve {--host=0.0.0.0} {--port=25}';
    protected $description = 'Start de CLOM SMTP inbound server';

    public function handle(): void
    {
        $host = $this->option('host');
        $port = (int) $this->option('port');

        $this->info("Starting CLOM SMTP Server on {$host}:{$port}...");

        $server = new SmtpServer($host, $port);

        pcntl_signal(SIGTERM, fn () => $server->stop());
        pcntl_signal(SIGINT, fn () => $server->stop());

        $server->start();
    }
}
