<?php

declare(strict_types=1);

/**
 * Worker de envio de e-mails. Deve ser chamado periodicamente via cron (ex: a cada minuto):
 *   php bin/send_queue.php
 *
 * Respeita o limite de mensagens/hora configurado pelo admin para cada promotor
 * (users.messages_per_hour), somando o que já foi enviado na última hora com o que
 * está sendo enviado nesta execução.
 */

require dirname(__DIR__) . '/src/autoload.php';

use App\Config;
use App\Mailer\EmailRenderer;
use App\Mailer\SmtpMailer;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Event;
use App\Models\EmailTemplate;
use App\Models\User;

$smtpConfig = Config::get('smtp');
$appUrl = Config::get('app.url');

$batch = CampaignRecipient::nextPendingBatch(500);

if (empty($batch)) {
    echo "Nenhum e-mail pendente.\n";
    exit(0);
}

$sentThisRunByPromoter = [];
$sentCount = 0;
$skippedByLimit = 0;
$failedCount = 0;

$mailer = new SmtpMailer(
    $smtpConfig['host'],
    $smtpConfig['port'],
    $smtpConfig['encryption'],
    $smtpConfig['username'],
    $smtpConfig['password'],
    $smtpConfig['from_email'],
    $smtpConfig['from_name']
);

$touchedCampaigns = [];

foreach ($batch as $recipient) {
    $promoterId = (int) $recipient['promoter_id'];

    if (!isset($sentThisRunByPromoter[$promoterId])) {
        $promoter = User::find($promoterId);
        $limit = (int) ($promoter['messages_per_hour'] ?? 100);
        $alreadySent = CampaignRecipient::sentLastHourByPromoter($promoterId);
        $sentThisRunByPromoter[$promoterId] = ['limit' => $limit, 'used' => $alreadySent];
    }

    $state = &$sentThisRunByPromoter[$promoterId];

    if ($state['used'] >= $state['limit']) {
        $skippedByLimit++;
        continue;
    }

    $touchedCampaigns[(int) $recipient['campaign_id']] = true;

    try {
        $template = EmailTemplate::find((int) $recipient['template_id']);
        $event = Event::find((int) $recipient['event_id']);

        $rendered = EmailRenderer::renderForRecipient(
            $template,
            $event,
            ['name' => $recipient['contact_name'], 'email' => $recipient['contact_email']],
            $recipient['tracking_token'],
            $appUrl
        );

        $mailer->send($recipient['contact_email'], $recipient['contact_name'], $rendered['subject'], $rendered['body']);
        CampaignRecipient::markSent((int) $recipient['id']);
        $state['used']++;
        $sentCount++;
    } catch (\Throwable $e) {
        CampaignRecipient::markFailed((int) $recipient['id'], $e->getMessage());
        $failedCount++;
    }

    unset($state);
}

foreach (array_keys($touchedCampaigns) as $campaignId) {
    $stats = Campaign::stats($campaignId);
    if ((int) $stats['pending'] === 0) {
        Campaign::updateStatus($campaignId, 'completed');
    } else {
        Campaign::updateStatus($campaignId, 'sending');
    }
}

echo "Enviados: {$sentCount} | Falhas: {$failedCount} | Adiados por limite/hora: {$skippedByLimit}\n";
