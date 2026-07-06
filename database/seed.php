<?php

declare(strict_types=1);

/**
 * Cria o usuário administrador inicial, caso ainda não exista.
 * Uso: php database/seed.php [email] [senha]
 */

require dirname(__DIR__) . '/src/autoload.php';

use App\Models\User;

$email = $argv[1] ?? 'admin@example.com';
$password = $argv[2] ?? 'admin123';

if (User::findByEmail($email)) {
    echo "Já existe um usuário com o e-mail {$email}.\n";
    exit(0);
}

User::create([
    'name' => 'Administrador',
    'email' => $email,
    'password' => $password,
    'role' => 'admin',
    'status' => 'active',
]);

echo "Admin criado: {$email} / {$password}\n";
echo "Troque a senha após o primeiro login.\n";
