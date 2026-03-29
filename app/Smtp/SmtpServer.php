<?php

namespace App\Smtp;

use App\Smtp\Handlers\ConnectionHandler;
use Fiber;

class SmtpServer
{
    private \Socket $socket;
    private bool $running = false;
    private string $hostname;
    private array $activeFibers = [];

    public function __construct(
        private string $bindAddress = '0.0.0.0',
        private int $port = 25,
    ) {
        $this->hostname = gethostname() ?: 'clom.local';
    }

    public function start(): void
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);

        if (!socket_bind($this->socket, $this->bindAddress, $this->port)) {
            throw new \RuntimeException(
                "Failed to bind to {$this->bindAddress}:{$this->port}: " . socket_strerror(socket_last_error())
            );
        }

        socket_listen($this->socket, 128);
        socket_set_nonblock($this->socket);

        $this->running = true;
        $this->log("CLOM SMTP Server listening on {$this->bindAddress}:{$this->port}");

        $this->loop();
    }

    public function stop(): void
    {
        $this->running = false;
        socket_close($this->socket);
        $this->log("SMTP Server stopped");
    }

    private function loop(): void
    {
        while ($this->running) {
            // Accept new connections (non-blocking)
            $client = @socket_accept($this->socket);

            if ($client !== false) {
                socket_set_nonblock($client);
                socket_getpeername($client, $peerAddress, $peerPort);
                $this->log("New connection from {$peerAddress}:{$peerPort}");

                $handler = new ConnectionHandler($client, $this->hostname);
                $fiber = new Fiber(fn () => $handler->handle());
                $this->activeFibers[] = ['fiber' => $fiber, 'handler' => $handler];
                $fiber->start();
            }

            // Resume suspended fibers
            $this->activeFibers = array_filter($this->activeFibers, function ($entry) {
                $fiber = $entry['fiber'];
                if ($fiber->isTerminated()) {
                    return false;
                }
                if ($fiber->isSuspended()) {
                    $fiber->resume();
                }
                return !$fiber->isTerminated();
            });

            // Prevent CPU spin when idle
            if (empty($this->activeFibers)) {
                usleep(10000); // 10ms
            } else {
                usleep(1000); // 1ms when active
            }
        }
    }

    private function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        echo "[{$timestamp}] {$message}\n";
    }
}
