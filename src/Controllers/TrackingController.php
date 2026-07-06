<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CampaignRecipient;

final class TrackingController
{
    private const PIXEL = "\x47\x49\x46\x38\x39\x61\x01\x00\x01\x00\x80\x00\x00\x00\x00\x00\xff\xff\xff\x21\xf9\x04\x01\x00\x00\x00\x00\x2c\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02\x44\x01\x00\x3b";

    public function pixel(string $token): void
    {
        CampaignRecipient::registerOpen($token, $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null);

        header('Content-Type: image/gif');
        header('Content-Length: ' . strlen(self::PIXEL));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo self::PIXEL;
    }

    public function click(string $token): void
    {
        $url = $_GET['url'] ?? '/';
        $url = filter_var($url, FILTER_VALIDATE_URL) ? $url : '/';

        CampaignRecipient::registerClick($token, $url, $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null);

        header('Location: ' . $url);
    }
}
