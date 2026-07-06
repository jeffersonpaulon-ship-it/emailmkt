<?php
declare(strict_types=1);

// Inicializa a sessão segura
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure'   => true, // Ativado para o seu domínio HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

require_once __DIR__ . '/../config/database.php';
use Config\Database;

$error = null;

// Processa o formulário de login quando enviado (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if ($email && !empty($password)) {
        try {
            $db = Database::getConnection();
            
            // Busca o usuário e valida se está ativo
            $stmt = $db->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && (int)$user['status'] === 1 && password_verify($password, $user['password'])) {
                // Regenera o ID da sessão para prevenir Session Fixation
                session_regenerate_id(true);

                // Grava os dados essenciais na sessão
                $_SESSION['user_id']   = (int)$user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                // PHP 8.2 Match Expression para Redirecionamento Dinâmico de Rotas
                $redirectUrl = match ($user['role']) {
                    'admin'    => 'https://wnebr.com/rsvp/views/admin/dashboard.php',
                    'promoter' => 'https://wnebr.com/rsvp/views/promoter/dashboard.php',
                    'client'   => 'https://wnebr.com/rsvp/views/client/dashboard.php',
                    default    => null
                };

                if ($redirectUrl) {
                    header("Location: " . $redirectUrl);
                    exit;
                }
                
                $error = "Perfil de acesso inválido.";
            } else {
                $error = "E-mail ou senha incorretos (ou conta inativa).";
            }
        } catch (Exception $e) {
            $error = "Erro no servidor. Tente novamente mais tarde.";
        }
    } else {
        $error = "Por favor, preencha todos os campos corretamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema RSVP</title>
    <link rel="stylesheet" href="https://wnebr.com/rsvp/public/assets/css/style.css">
    <style>
        /* Estilos específicos e isolados para a tela de login centralizada */
        .login-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--background-color);
            padding: 1rem;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: var(--surface-color);
            border-radius: var(--radius-md);
            padding: 2.5rem 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: var(--color-declined);
            border: 1px solid #fca5a5;
            padding: 0.75rem;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        
        <div class="login-header">
            <h1>Plataforma RSVP</h1>
            <p>Digite suas credenciais para acessar o painel</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" autocomplete="off">
            <div class="form-group">
                <label Safe for="email">E-mail Corporativo</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="nome@empresa.com" required autofocus>
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="password">Senha de Acesso</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.95rem;">
                Entrar no Sistema
            </button>
        </form>

    </div>
</div>

</body>
</html>