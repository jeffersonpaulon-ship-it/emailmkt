<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

final class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(): array
    {
        return Database::connection()->query('SELECT * FROM users ORDER BY role, name')->fetchAll();
    }

    public static function clientsOfPromoter(int $promoterId): array
    {
        $stmt = Database::connection()->prepare("SELECT * FROM users WHERE role = 'client' AND promoter_id = ? ORDER BY name");
        $stmt->execute([$promoterId]);
        return $stmt->fetchAll();
    }

    public static function promoters(): array
    {
        return Database::connection()->query("SELECT * FROM users WHERE role = 'promoter' ORDER BY name")->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO users (name, email, password_hash, role, promoter_id, messages_per_hour, status)
             VALUES (:name, :email, :password_hash, :role, :promoter_id, :messages_per_hour, :status)'
        );
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'],
            'promoter_id' => $data['promoter_id'] ?? null,
            'messages_per_hour' => $data['messages_per_hour'] ?? 100,
            'status' => $data['status'] ?? 'active',
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $fields = [];
        $params = ['id' => $id];

        foreach (['name', 'email', 'role', 'promoter_id', 'messages_per_hour', 'status'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (!empty($data['password'])) {
            $fields[] = 'password_hash = :password_hash';
            $params['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) {
            return;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        Database::connection()->prepare($sql)->execute($params);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
    }
}
