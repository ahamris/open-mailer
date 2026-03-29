<?php

namespace App\Smtp\Handlers;

use App\Smtp\Envelope;
use App\Smtp\Auth\DkimVerifier;
use App\Smtp\Auth\SpfChecker;
use App\Models\Email;
use Fiber;

class ConnectionHandler
{
    private string $readBuffer = '';
    private string $state = 'INIT';
    private ?Envelope $envelope = null;
    private string $peerAddress = '';

    private const RESPONSES = [
        'greeting' => "220 %s ESMTP CLOM (CodeLabs Open Mailer)\r\n",
        'ehlo' => "250-%s Hello\r\n250-SIZE 52428800\r\n250-8BITMIME\r\n250-STARTTLS\r\n250-ENHANCEDSTATUSCODES\r\n250-PIPELINING\r\n250-CHUNKING\r\n250 SMTPUTF8\r\n",
        'helo' => "250 %s\r\n",
        'ok' => "250 2.1.0 Ok\r\n",
        'rcpt_ok' => "250 2.1.5 Ok\r\n",
        'data_start' => "354 End data with <CR><LF>.<CR><LF>\r\n",
        'data_ok' => "250 2.0.0 Ok: queued as %s\r\n",
        'bye' => "221 2.0.0 Bye\r\n",
        'error_syntax' => "500 5.5.2 Syntax error\r\n",
        'error_sequence' => "503 5.5.1 Bad sequence of commands\r\n",
    ];

    public function __construct(
        private \Socket $socket,
        private string $hostname,
    ) {
        socket_getpeername($this->socket, $this->peerAddress);
    }

    public function handle(): void
    {
        $this->send(sprintf(self::RESPONSES['greeting'], $this->hostname));
        $this->state = 'GREETING_SENT';

        $timeout = time() + 300; // 5 min timeout

        while (time() < $timeout) {
            $line = $this->readLine();

            if ($line === null) {
                Fiber::suspend();
                continue;
            }

            if (!$this->processCommand($line)) {
                break; // QUIT of fout
            }
        }

        @socket_close($this->socket);
    }

    private function processCommand(string $line): bool
    {
        $command = strtoupper(trim(substr($line, 0, 4)));
        $fullCommand = trim($line);

        return match ($command) {
            'EHLO' => $this->handleEhlo($fullCommand),
            'HELO' => $this->handleHelo($fullCommand),
            'MAIL' => $this->handleMailFrom($fullCommand),
            'RCPT' => $this->handleRcptTo($fullCommand),
            'DATA' => $this->handleData(),
            'QUIT' => $this->handleQuit(),
            'RSET' => $this->handleReset(),
            'NOOP' => $this->handleNoop(),
            default => $this->handleUnknown($fullCommand),
        };
    }

    private function handleEhlo(string $line): bool
    {
        $this->envelope = new Envelope();
        $this->envelope->heloHostname = trim(substr($line, 5));
        $this->envelope->peerAddress = $this->peerAddress;
        $this->send(sprintf(self::RESPONSES['ehlo'], $this->hostname));
        $this->state = 'EHLO_RECEIVED';
        return true;
    }

    private function handleHelo(string $line): bool
    {
        $this->envelope = new Envelope();
        $this->envelope->heloHostname = trim(substr($line, 5));
        $this->envelope->peerAddress = $this->peerAddress;
        $this->send(sprintf(self::RESPONSES['helo'], $this->hostname));
        $this->state = 'HELO_RECEIVED';
        return true;
    }

    private function handleMailFrom(string $line): bool
    {
        if (!in_array($this->state, ['EHLO_RECEIVED', 'HELO_RECEIVED', 'RESET'])) {
            $this->send(self::RESPONSES['error_sequence']);
            return true;
        }

        if (preg_match('/MAIL FROM:\s*<(.*)>/i', $line, $matches)) {
            $this->envelope->fromAddress = trim($matches[1]);
            $this->state = 'MAIL_FROM_RECEIVED';
            $this->send(self::RESPONSES['ok']);
        } else {
            $this->send(self::RESPONSES['error_syntax']);
        }
        return true;
    }

    private function handleRcptTo(string $line): bool
    {
        if (!in_array($this->state, ['MAIL_FROM_RECEIVED', 'RCPT_TO_RECEIVED'])) {
            $this->send(self::RESPONSES['error_sequence']);
            return true;
        }

        if (preg_match('/RCPT TO:\s*<(.+)>/i', $line, $matches)) {
            $this->envelope->recipients[] = trim($matches[1]);
            $this->state = 'RCPT_TO_RECEIVED';
            $this->send(self::RESPONSES['rcpt_ok']);
        } else {
            $this->send(self::RESPONSES['error_syntax']);
        }
        return true;
    }

    private function handleData(): bool
    {
        if ($this->state !== 'RCPT_TO_RECEIVED') {
            $this->send(self::RESPONSES['error_sequence']);
            return true;
        }

        $this->send(self::RESPONSES['data_start']);
        $this->state = 'DATA';

        // Lees data tot we ".\r\n" tegenkomen
        $dataBuffer = '';
        $readingHeaders = true;

        while (true) {
            $line = $this->readLine();
            if ($line === null) {
                Fiber::suspend();
                continue;
            }

            if ($line === ".\r\n") {
                break;
            }

            // Dot-stuffing: regel die begint met ".." wordt "."
            if (str_starts_with($line, '..')) {
                $line = substr($line, 1);
            }

            if ($readingHeaders) {
                if ($line === "\r\n") {
                    $readingHeaders = false;
                } else {
                    $this->envelope->rawHeaders .= $line;
                }
            } else {
                $this->envelope->rawBody .= $line;
            }
        }

        // Verwerk de envelope
        $emailId = $this->processEnvelope();
        $this->send(sprintf(self::RESPONSES['data_ok'], $emailId));
        $this->state = 'EHLO_RECEIVED'; // Klaar voor volgende mail
        return true;
    }

    private function handleQuit(): bool
    {
        $this->send(self::RESPONSES['bye']);
        return false;
    }

    private function handleReset(): bool
    {
        $this->envelope = new Envelope();
        $this->envelope->peerAddress = $this->peerAddress;
        $this->state = 'EHLO_RECEIVED';
        $this->send(self::RESPONSES['ok']);
        return true;
    }

    private function handleNoop(): bool
    {
        $this->send(self::RESPONSES['ok']);
        return true;
    }

    private function handleUnknown(string $line): bool
    {
        $this->send(self::RESPONSES['error_syntax']);
        return true;
    }

    private function processEnvelope(): string
    {
        $this->envelope->parseHeaders();

        // DKIM verificatie
        $dkimResult = DkimVerifier::verify($this->envelope);

        // SPF check
        $spfResult = SpfChecker::check(
            $this->envelope->peerAddress,
            $this->envelope->fromAddress
        );

        // Opslaan in database
        $email = Email::create([
            'direction' => 'inbound',
            'status' => 'received',
            'from_address' => $this->envelope->fromAddress,
            'from_name' => $this->envelope->parsedHeaders['from']['name'] ?? null,
            'to_addresses' => $this->envelope->recipients,
            'subject' => $this->envelope->parsedHeaders['subject'] ?? '(no subject)',
            'html_body' => $this->envelope->getHtmlBody(),
            'text_body' => $this->envelope->getTextBody(),
            'message_id' => $this->envelope->parsedHeaders['message-id'] ?? null,
            'sender_ip' => $this->envelope->peerAddress,
            'spf_result' => $spfResult,
            'dkim_result' => $dkimResult['status'] ?? 'none',
            'sent_at' => now(),
        ]);

        // Trigger workflows
        try {
            app(\App\Services\WorkflowEngine::class)->processIncoming($email);
        } catch (\Exception $e) {
            // Log but dont crash SMTP
        }

        return $email->id;
    }

    private function send(string $data): void
    {
        @socket_write($this->socket, $data, strlen($data));
    }

    private function readLine(): ?string
    {
        $data = @socket_read($this->socket, 8192);

        if ($data === false || $data === '') {
            return null;
        }

        $this->readBuffer .= $data;

        $pos = strpos($this->readBuffer, "\r\n");
        if ($pos !== false) {
            $line = substr($this->readBuffer, 0, $pos + 2);
            $this->readBuffer = substr($this->readBuffer, $pos + 2);
            return $line;
        }

        return null;
    }
}
