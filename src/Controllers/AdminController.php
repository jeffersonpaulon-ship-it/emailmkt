<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Models\User;
use App\Url;
use App\View;

final class AdminController
{
    public function __construct()
    {
        Auth::requireRole('admin');
    }

    public function index(): void
    {
        $users = User::all();
        $promoters = User::promoters();
        View::render('admin/users', [
            'pageTitle' => 'Usuários',
            'users' => $users,
            'promoters' => $promoters,
        ]);
    }

    public function create(): void
    {
        Csrf::requireValid();

        $role = $_POST['role'] ?? 'client';
        $promoterId = $role === 'client' ? (int) ($_POST['promoter_id'] ?? 0) : null;

        if (User::findByEmail(trim($_POST['email'] ?? ''))) {
            $_SESSION['flash_error'] = 'Já existe um usuário com este e-mail.';
            header('Location: ' . Url::to('/admin/users'));
            return;
        }

        User::create([
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? bin2hex(random_bytes(6)),
            'role' => $role,
            'promoter_id' => $promoterId ?: null,
            'messages_per_hour' => $role === 'promoter' ? (int) ($_POST['messages_per_hour'] ?? 100) : null,
        ]);

        $_SESSION['flash_success'] = 'Usuário criado com sucesso.';
        header('Location: ' . Url::to('/admin/users'));
    }

    public function updateRateLimit(string $id): void
    {
        Csrf::requireValid();

        User::update((int) $id, [
            'messages_per_hour' => max(1, (int) ($_POST['messages_per_hour'] ?? 100)),
        ]);

        $_SESSION['flash_success'] = 'Limite de mensagens por hora atualizado.';
        header('Location: ' . Url::to('/admin/users'));
    }

    public function updateStatus(string $id): void
    {
        Csrf::requireValid();

        $status = ($_POST['status'] ?? 'active') === 'active' ? 'active' : 'inactive';
        User::update((int) $id, ['status' => $status]);

        $_SESSION['flash_success'] = 'Status do usuário atualizado.';
        header('Location: ' . Url::to('/admin/users'));
    }

    public function delete(string $id): void
    {
        Csrf::requireValid();

        if ((int) $id === Auth::id()) {
            $_SESSION['flash_error'] = 'Você não pode remover o próprio usuário.';
            header('Location: ' . Url::to('/admin/users'));
            return;
        }

        User::delete((int) $id);
        $_SESSION['flash_success'] = 'Usuário removido.';
        header('Location: ' . Url::to('/admin/users'));
    }
}
