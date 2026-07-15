<?php

declare(strict_types=1);

interface HtmlMailer
{
    public function sendHtmlMessage(string $toAddress, string $toName, string $subject, string $htmlBody, string $textBody): void;
}
