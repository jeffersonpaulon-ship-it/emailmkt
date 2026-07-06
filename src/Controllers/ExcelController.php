<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;
use Exception;

class ExcelController 
{
    // PHP 8.2: Constructor Property Promotion
    public function __construct(private PDO $db) {}

    public function import(int $eventId, array $fileInfo): string 
    {
        header('Content-Type: application/json');

        if ($fileInfo['error'] !== UPLOAD_ERR_OK) {
            return json_encode(['success' => false, 'message' => 'Erro no upload do arquivo.']);
        }

        // Valida se é um arquivo CSV/Texto
        $mimeType = mime_content_type($fileInfo['tmp_name']);
        if (!in_array($mimeType, ['text/csv', 'text/plain', 'application/vnd.ms-excel'])) {
            return json_encode(['success' => false, 'message' => 'Por favor, envie um arquivo CSV válido.']);
        }

        try {
            $handle = fopen($fileInfo['tmp_name'], 'r');
            if (!$handle) {
                throw new Exception('Não foi possível abrir o arquivo.');
            }

            // Identifica o delimitador (, ou ;)
            $firstLine = fgets($handle);
            $delimiter = str_contains($firstLine, ';') ? ';' : ',';
            rewind($handle);

            // Lendo o cabeçalho para garantir mapeamento correto
            $headers = fgetcsv($handle, 0, $delimiter);
            
            // Prepara a Query de Inserção em Massa
            $sql = "INSERT INTO guests (
                event_id, category, tickets_count, email, full_name, gender, company, job_title, 
                whatsapp, secretary_contact, contact_method, growth_responsible, contact_status, 
                confirmation_status, save_the_date, follow_up, reminder_2w, reminder_1w, reminder_1d
            ) VALUES (
                :event_id, :category, :tickets_count, :email, :full_name, :gender, :company, :job_title, 
                :whatsapp, :secretary_contact, :contact_method, :growth_responsible, :contact_status, 
                :confirmation_status, :save_the_date, :follow_up, :reminder_2w, :reminder_1w, :reminder_1d
            )";

            $stmt = $this->db->prepare($sql);

            // Inicia uma Transação no MySQL para ganho massivo de performance
            $this->db->beginTransaction();

            $insertedCount = 0;
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                // Evita linhas vazias
                if (empty($row[3])) continue; 

                // Mapeamento exato baseado nas colunas fornecidas
                $stmt->execute([
                    'event_id'             => $eventId,
                    'category'             => $row[0] ?? null,
                    'tickets_count'        => is_numeric($row[1]) ? (int)$row[1] : 1,
                    'email'                => filter_var($row[2] ?? '', FILTER_VALIDATE_EMAIL) ? $row[2] : null,
                    'full_name'            => trim($row[3] ?? 'Convidado Sem Nome'),
                    'gender'               => $row[4] ?? null,
                    'company'              => $row[5] ?? null,
                    'job_title'            => $row[6] ?? null,
                    'whatsapp'             => $row[7] ?? null,
                    'secretary_contact'    => $row[8] ?? null,
                    'contact_method'       => $row[9] ?? null,
                    'growth_responsible'   => $row[10] ?? null,
                    'contact_status'       => $row[11] ?? null,
                    // Converte strings de status para o ENUM do banco
                    'confirmation_status'  => $this->mapConfirmationStatus($row[12] ?? ''),
                    'save_the_date'        => $this->parseBoolean($row[13] ?? ''),
                    'follow_up'            => $this->parseBoolean($row[14] ?? ''),
                    'reminder_2w'          => $this->parseBoolean($row[15] ?? ''),
                    'reminder_1w'          => $this->parseBoolean($row[16] ?? ''),
                    'reminder_1d'          => $this->parseBoolean($row[17] ?? '')
                ]);
                $insertedCount++;
            }

            fclose($handle);
            $this->db->commit(); // Salva permanentemente se tudo correu bem

            return json_encode([
                'success' => true, 
                'message' => "Lista importada com sucesso! {$insertedCount} convidados adicionados."
            ]);

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack(); // Cancela tudo em caso de falha no meio do arquivo
            }
            return json_encode(['success' => false, 'message' => 'Erro ao processar arquivo: ' . $e->getMessage()]);
        }
    }

    private function mapConfirmationStatus(string $status): string 
    {
        return match (strtolower(trim($status))) {
            'confirmado', 'sim', 'confirmed' => 'confirmed',
            'recusado', 'não', 'nao', 'declined' => 'declined',
            default => 'pending',
        };
    }

    private function parseBoolean(string $value): int 
    {
        $value = strtolower(trim($value));
        return in_array($value, ['sim', 'yes', '1', 'true', 'ok']) ? 1 : 0;
    }
}