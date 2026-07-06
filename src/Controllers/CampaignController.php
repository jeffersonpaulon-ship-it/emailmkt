<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;
use Exception;

class CampaignController 
{
    public function __construct(private PDO $db) {}

    // Cria ou salva rascunho de uma campanha
    public function create(int $eventId, string $subject, string $bodyHtml, ?string $scheduledAt = null): string 
    {
        header('Content-Type: application/json');

        try {
            $status = $scheduledAt ? 'scheduled' : 'draft';

            $stmt = $this->db->prepare("
                INSERT INTO email_campaigns (event_id, subject, body_html, scheduled_at, status) 
                VALUES (:event_id, :subject, :body_html, :scheduled_at, :status)
            ");

            $stmt->execute([
                'event_id'     => $eventId,
                'subject'      => $subject,
                'body_html'    => $bodyHtml,
                'scheduled_at' => $scheduledAt,
                'status'       => $status
            ]);

            return json_encode(['success' => true, 'campaign_id' => $this->db->lastInsertId(), 'message' => 'Campanha salva com sucesso!']);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Envia um email de teste unitário antes de disparar para a lista inteira
    public function sendTest(int $campaignId, string $targetEmail): string 
    {
        header('Content-Type: application/json');

        try {
            $stmt = $this->db->prepare("SELECT subject, body_html FROM email_campaigns WHERE id = :id");
            $stmt->execute(['id' => $campaignId]);
            $campaign = $stmt->fetch();

            if (!$campaign) {
                return json_encode(['success' => false, 'message' => 'Campanha não encontrada.']);
            }

            // Injeta dados fictícios para a pré-visualização do teste
            $bodyPrepared = str_replace(['{{nome}}', '{{empresa}}'], ['[Teste] Nome do Convidado', '[Teste] Empresa S/A'], $campaign['body_html']);
            
            // Aqui entraria a chamada da sua biblioteca de envio (ex: PHPMailer, SES, Sendgrid)
            // $this->mailService->send($targetEmail, $campaign['subject'], $bodyPrepared);

            return json_encode(['success' => true, 'message' => "E-mail de teste enviado para {$targetEmail}!"]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Dispara a campanha gerando os links individuais e pixels de rastreio
    public function processQueue(int $campaignId): void 
    {
        // Altera status para enviando para evitar dupla execução simultânea
        $this->db->prepare("UPDATE email_campaigns SET status = 'sending' WHERE id = ?")->execute([$campaignId]);

        $stmt = $this->db->prepare("
            SELECT c.subject, c.body_html, g.id as guest_id, g.full_name, g.email, g.company 
            FROM email_campaigns c
            JOIN guests g ON g.event_id = c.event_id
            WHERE c.id = :campaign_id AND g.email IS NOT NULL
        ");
        $stmt->execute(['campaign_id' => $campaignId]);
        $queue = $stmt->fetchAll();

        foreach ($queue as $recipient) {
            $html = $recipient['body_html'];

            // 1. Substituição de variáveis dinâmicas do template
            $html = str_replace('{{nome}}', $recipient['full_name'], $html);
            $html = str_replace('{{empresa}}', $recipient['company'] ?? '', $html);

            // 2. Injeção do Pixel Oculto de Abertura
            $trackingPixel = '<img src="https://wnebr.com/rsvp/t.php?c='.$campaignId.'&g='.$recipient['guest_id'].'" width="1" height="1" style="display:none;" />';
            $html .= $trackingPixel;

            // 3. (Opcional) Rewrite de Links para Tracking de Cliques
            // Aqui você pode rodar um regex capturando as tags <a href=""> e apontando para o seu `r.php`

            // Envio real do e-mail
            // $this->mailService->send($recipient['email'], $recipient['subject'], $html);
        }

        // Finaliza o status da campanha
        $this->db->prepare("UPDATE email_campaigns SET status = 'sent' WHERE id = ?")->execute([$campaignId]);
    }
}