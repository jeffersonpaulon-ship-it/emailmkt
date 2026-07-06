<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class Guest
{
    public function __construct(private PDO $db) {}

    /**
     * Retorna todos os convidados de um evento (com paginação opcional para o painel do Promotor)
     */
    public function getAllByEvent(int $eventId, int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM guests WHERE event_id = :event_id ORDER BY full_name ASC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        
        // BindValue necessário para garantir o tipo inteiro no LIMIT/OFFSET do MySQL
        $stmt->bindValue(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Atualiza o status de confirmação (pending, confirmed, declined)
     * Utilizado tanto pelas respostas automatizadas quanto pelo controle manual do promotor
     */
    public function updateConfirmationStatus(int $guestId, string $status): bool
    {
        // PHP 8.2: Match expression garantindo validação estrita antes do banco
        $validStatus = match ($status) {
            'confirmed', 'confirmado' => 'confirmed',
            'declined', 'recusado'   => 'declined',
            default                   => 'pending',
        };

        $sql = "UPDATE guests SET confirmation_status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        return $this->db->prepare($sql)->execute([
            'status' => $validStatus,
            'id'     => $guestId
        ]);
    }

    /**
     * Atualiza o andamento do contato e a régua de relacionamento (Excel / Ligações / WhatsApp)
     */
    public function updateContactProgress(int $guestId, array $progressData): bool
    {
        $sql = "UPDATE guests SET 
                    contact_status = :contact_status,
                    contact_method = :contact_method,
                    growth_responsible = :growth_responsible,
                    save_the_date = :save_the_date,
                    follow_up = :follow_up,
                    reminder_2w = :reminder_2w,
                    reminder_1w = :reminder_1w,
                    reminder_1d = :reminder_1d,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id'                 => $guestId,
            'contact_status'     => $progressData['contact_status'] ?? null,
            'contact_method'     => $progressData['contact_method'] ?? null,
            'growth_responsible' => $progressData['growth_responsible'] ?? null,
            'save_the_date'      => (int)($progressData['save_the_date'] ?? 0),
            'follow_up'          => (int)($progressData['follow_up'] ?? 0),
            'reminder_2w'        => (int)($progressData['reminder_2w'] ?? 0),
            'reminder_1w'        => (int)($progressData['reminder_1w'] ?? 0),
            'reminder_1d'        => (int)($progressData['reminder_1d'] ?? 0),
        ]);
    }

    /**
     * Busca um único convidado por ID
     */
    public function findById(int $guestId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM guests WHERE id = ?");
        $stmt->execute([$guestId]);
        $guest = $stmt->fetch();

        return $guest ?: null;
    }
}