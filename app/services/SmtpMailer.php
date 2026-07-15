<?php

declare(strict_types=1);

final class SmtpMailer implements HtmlMailer
{
    /**
     * @throws RuntimeException
     */
    public function sendHtmlMessage(string $toAddress, string $toName, string $subject, string $htmlBody, string $textBody): void
    {
        $host = smtp_host();
        $username = smtp_username();
        $password = smtp_password();
        $fromAddress = mail_from_address();

        if ($host === '' || $username === '' || $password === '' || $fromAddress === '') {
            throw new RuntimeException('SMTP environment variables are incomplete.');
        }

        $socket = $this->connect();

        try {
            $this->assertReply($socket, [220]);
            $this->sendCommand($socket, 'EHLO synapse-motors');
            $this->assertReply($socket, [250]);

            if (smtp_encryption() === 'tls') {
                $this->sendCommand($socket, 'STARTTLS');
                $this->assertReply($socket, [220]);

                $cryptoEnabled = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

                if ($cryptoEnabled !== true) {
                    throw new RuntimeException('Unable to start TLS for SMTP connection.');
                }

                $this->sendCommand($socket, 'EHLO synapse-motors');
                $this->assertReply($socket, [250]);
            }

            $this->sendCommand($socket, 'AUTH LOGIN');
            $this->assertReply($socket, [334]);
            $this->sendCommand($socket, base64_encode($username));
            $this->assertReply($socket, [334]);
            $this->sendCommand($socket, base64_encode($password));
            $this->assertReply($socket, [235]);

            $this->sendCommand($socket, 'MAIL FROM:<' . $fromAddress . '>');
            $this->assertReply($socket, [250]);
            $this->sendCommand($socket, 'RCPT TO:<' . $toAddress . '>');
            $this->assertReply($socket, [250, 251]);
            $this->sendCommand($socket, 'DATA');
            $this->assertReply($socket, [354]);
            $this->sendData($socket, $this->buildMessage($toAddress, $toName, $subject, $htmlBody, $textBody));
            $this->assertReply($socket, [250]);
            $this->sendCommand($socket, 'QUIT');
        } finally {
            fclose($socket);
        }
    }

    /**
     * @return resource
     */
    private function connect()
    {
        $transport = smtp_encryption() === 'ssl' ? 'ssl://' : 'tcp://';
        $socket = stream_socket_client(
            $transport . smtp_host() . ':' . smtp_port(),
            $errorCode,
            $errorMessage,
            15,
            STREAM_CLIENT_CONNECT
        );

        if ($socket === false) {
            throw new RuntimeException('Unable to connect to SMTP server: ' . $errorMessage . ' (' . $errorCode . ')');
        }

        stream_set_timeout($socket, 15);

        return $socket;
    }

    /**
     * @param resource $socket
     */
    private function sendCommand($socket, string $command): void
    {
        fwrite($socket, $command . "\r\n");
    }

    /**
     * @param resource $socket
     */
    private function sendData($socket, string $message): void
    {
        $normalized = str_replace(["\r\n.", "\n."], ["\r\n..", "\n.."], $message);
        fwrite($socket, $normalized . "\r\n.\r\n");
    }

    /**
     * @param resource $socket
     * @param array<int, int> $expectedCodes
     */
    private function assertReply($socket, array $expectedCodes): string
    {
        $reply = $this->readReply($socket);
        $statusCode = (int) substr($reply, 0, 3);

        if (!in_array($statusCode, $expectedCodes, true)) {
            throw new RuntimeException('SMTP error: ' . trim($reply));
        }

        return $reply;
    }

    /**
     * @param resource $socket
     */
    private function readReply($socket): string
    {
        $reply = '';

        while (!feof($socket)) {
            $line = fgets($socket, 515);

            if ($line === false) {
                break;
            }

            $reply .= $line;

            if (strlen($line) < 4 || $line[3] !== '-') {
                break;
            }
        }

        if ($reply === '') {
            throw new RuntimeException('SMTP server closed the connection unexpectedly.');
        }

        return $reply;
    }

    private function buildMessage(string $toAddress, string $toName, string $subject, string $htmlBody, string $textBody): string
    {
        $boundary = 'b1_' . bin2hex(random_bytes(12));
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        $headers = [
            'Date: ' . gmdate('D, d M Y H:i:s') . ' +0000',
            'From: ' . $this->formatMailbox(mail_from_address(), mail_from_name()),
            'To: ' . $this->formatMailbox($toAddress, $toName),
            'Subject: ' . $encodedSubject,
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];

        $parts = [
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $textBody,
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $htmlBody,
            '--' . $boundary . '--',
        ];

        return implode("\r\n", array_merge($headers, [''], $parts));
    }

    private function formatMailbox(string $address, string $name): string
    {
        $sanitizedName = trim(preg_replace('/[\r\n]+/', ' ', $name) ?? '');

        if ($sanitizedName === '') {
            return '<' . $address . '>';
        }

        return sprintf('"%s" <%s>', addcslashes($sanitizedName, '"\\'), $address);
    }
}
