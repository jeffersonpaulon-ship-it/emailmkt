<?php

declare(strict_types=1);

namespace App\Mailer;

use App\Url;

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
        string $trackingToken
    ): array {
        $confirmationLink = Url::full('/confirmar/' . $event['slug']) . '?t=' . $trackingToken;

        $replacements = [
            '{{nome}}' => $contact['name'],
            '{{evento}}' => $event['name'],
            '{{link_confirmacao}}' => $confirmationLink,
        ];

        $subject = strtr($template['subject'], $replacements);
        $body = strtr($template['body_html'], $replacements);

        $body = self::rewriteLinksForClickTracking($body, $trackingToken);
        $body .= self::openPixelTag($trackingToken);

        return ['subject' => $subject, 'body' => $body];
    }

    private static function rewriteLinksForClickTracking(string $html, string $token): string
    {
        return preg_replace_callback(
            '/href="(https?:\/\/[^"]+)"/i',
            function (array $matches) use ($token): string {
                $redirect = Url::full('/track/click/' . $token) . '?url=' . urlencode($matches[1]);
                return 'href="' . $redirect . '"';
            },
            $html
        );
    }

    private static function openPixelTag(string $token): string
    {
        return '<img src="' . Url::full('/track/open/' . $token . '.gif') . '" width="1" height="1" alt="" style="display:none">';
    }
}
