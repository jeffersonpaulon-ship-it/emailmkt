<?php

declare(strict_types=1);

namespace App\Mailer;

final class EmailRenderer
{
    /**
     * Substitui variáveis do template, reescreve todos os links para passarem pelo
     * redirecionador de cliques e acrescenta o pixel de abertura no final do corpo.
     *
     * @return array{subject: string, body: string}
     */
    public static function renderForRecipient(
        array $template,
        array $event,
        array $contact,
        string $trackingToken,
        string $appUrl
    ): array {
        $confirmationLink = $appUrl . '/rsvp/' . $event['slug'] . '?t=' . $trackingToken;

        $replacements = [
            '{{nome}}' => $contact['name'],
            '{{evento}}' => $event['name'],
            '{{link_confirmacao}}' => $confirmationLink,
        ];

        $subject = strtr($template['subject'], $replacements);
        $body = strtr($template['body_html'], $replacements);

        $body = self::rewriteLinksForClickTracking($body, $trackingToken, $appUrl);
        $body .= self::openPixelTag($trackingToken, $appUrl);

        return ['subject' => $subject, 'body' => $body];
    }

    private static function rewriteLinksForClickTracking(string $html, string $token, string $appUrl): string
    {
        return preg_replace_callback(
            '/href="(https?:\/\/[^"]+)"/i',
            function (array $matches) use ($token, $appUrl): string {
                $redirect = $appUrl . '/track/click/' . $token . '?url=' . urlencode($matches[1]);
                return 'href="' . $redirect . '"';
            },
            $html
        );
    }

    private static function openPixelTag(string $token, string $appUrl): string
    {
        return '<img src="' . $appUrl . '/track/open/' . $token . '.gif" width="1" height="1" alt="" style="display:none">';
    }
}
