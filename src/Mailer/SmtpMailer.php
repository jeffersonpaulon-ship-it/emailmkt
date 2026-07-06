<?php

declare(strict_types=1);

namespace App\Mailer;

use RuntimeException;

/**
 * Cliente SMTP mínimo, sem dependências externas (sockets + comandos SMTP puros).
 * Suporta STARTTLS e AUTH LOGIN, que é o que a grande maioria dos provedores exige.
 */
final class SmtpMailer
{
    private $socket;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $encryption, // 'tls' | 'ssl' | ''
        private readonly string $username,
        private readonly string $password,
        private readonly string $fromEmail,
        private readonly string $fromName
    ) {
    }

    public function send(string $toEmail, string $toName, string $subject, string $htmlBody): void
    {
        $transport = $this->encryption === 'ssl' ? 'ssl://' : '';
        $this->socket = @fsockopen($transport . $this->host, $this->port, $errno, $errstr, 15);

        if (!$this->socket) {
            throw new RuntimeException("Não foi possível conectar ao SMTP {$this->host}:{$this->port} ({$errstr})");
        }

        stream_set_timeout($this->socket, 15);

        $this->expect(220);
        $this->command('EHLO ' . gethostname(), 250);

        if ($this->encryption === 'tls') {
            $this->command('STARTTLS', 220);
            if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Falha ao negociar STARTTLS com o servidor SMTP.');
            }
            $this->command('EHLO ' . gethostname(), 250);
        }

        if ($this->username !== '') {
            $this->command('AUTH LOGIN', 334);
            $this->command(base64_encode($this->username), 334);
            $this->command(base64_encode($this->password), 235);
        }

        $this->command('MAIL FROM:<' . $this->fromEmail . '>', 250);
        $this->command('RCPT TO:<' . $toEmail . '>', 250);
        $this->command('DATA', 354);

        $headers = $this->buildHeaders($toEmail, $toName, $subject);
        $body = str_replace("\n.", "\n..", $htmlBody); // escapa linhas que começam com "." (fim de DATA)

        fwrite($this->socket, $headers . "\r\n" . $body . "\r\n.\r\n");
        $this->expect(250);

        $this->command('QUIT', 221);
        fclose($this->socket);
    }

    private function buildHeaders(string $toEmail, string $toName, string $subject): string
    {
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedFromName = '=?UTF-8?B?' . base64_encode($this->fromName) . '?=';
        $encodedToName = '=?UTF-8?B?' . base64_encode($toName) . '?=';

        return implode("\r\n", [
            'From: ' . $encodedFromName . ' <' . $this->fromEmail . '>',
            'To: ' . $encodedToName . ' <' . $toEmail . '>',
            'Subject: ' . $encodedSubject,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'Date: ' . date('r'),
            'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . gethostname() . '>',
        ]) . "\r\n";
    }

    private function command(string $command, int $expectedCode): string
    {
        fwrite($this->socket, $command . "\r\n");
        return $this->expect($expectedCode);
    }

    private function expect(int $expectedCode): string
    {
        $response = '';
        while ($line = fgets($this->socket, 515)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new RuntimeException("Resposta SMTP inesperada: esperado {$expectedCode}, recebido '{$response}'");
        }

        return $response;
    }
}
